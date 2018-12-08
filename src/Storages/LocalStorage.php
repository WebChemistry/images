<?php declare(strict_types = 1);

namespace WebChemistry\Images\Storages;

use Nette\Http\IRequest;
use Nette\Utils\Finder;
use WebChemistry\Images\Helpers;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Image\ImageSize;
use WebChemistry\Images\ImageStorageException;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Transfer\LocalResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;
use WebChemistry\Images\Resources\Transfer\UploadResource;
use WebChemistry\Images\Resources\Meta\IResourceMeta;
use WebChemistry\Images\Resources\Meta\IResourceMetaFactory;
use WebChemistry\Images\Storage;

class LocalStorage extends Storage {

	/** @var string */
	private $directory;

	/** @var string|null */
	private $defaultImage;

	/** @var string */
	private $basePath;

	/** @var string */
	private $baseUrl;

	/** @var IResourceMetaFactory */
	private $metaFactory;

	/** @var IImageFactory */
	private $imageFactory;

	public function __construct(string $wwwDir, string $assetsDir, IResourceMetaFactory $metaFactory, IRequest $request,
								IImageFactory $imageFactory, ?string $defaultImage = null) {
		$this->metaFactory = $metaFactory;
		$this->defaultImage = $defaultImage;

		// paths
		$assetsDir = Helpers::normalizePath($assetsDir);

		$this->directory = $wwwDir . '/' . $assetsDir;
		$this->basePath = $request->getUrl()->getBasePath() . $assetsDir;
		$this->baseUrl = $request->getUrl()->getBaseUrl() . $assetsDir;
		$this->imageFactory = $imageFactory;
	}

	public function link(?IFileResource $resource): ?string {
		$served = $this->metaFactory->create($resource);
		$location = $this->getLink($served);

		// Image not exists
		$defaultImage = $resource->getDefaultImage() ?: $this->defaultImage;
		if ($location === null && $defaultImage) {
			$default = $this->createResource($defaultImage);
			$default->setAliases($resource->getAliases());

			$location = $this->getLink($this->metaFactory->create($default));
		}

		return $location === null ? null : ($resource->isBaseUrl() ? $this->baseUrl : $this->basePath). $location;
	}

	/**
	 * @param IResourceMeta $resource
	 * @return string|null - null not exists
	 */
	protected function getLink(IResourceMeta $resource): ?string {
		$location = $this->getResourceLocation($resource);
		$path = $this->directory . $location;
		if (is_file($path)) {
			return $location;
		}
		if (!$resource->hasModifiers()) {
			return null;
		}

		// resize image
		$originalPath = $this->getResourcePath($this->metaFactory->create($resource->getResource()->getOriginal()));
		if (!is_file($originalPath)) {
			return null;
		}
		$image = $this->imageFactory->createFromFile($originalPath);
		$resource->modify($image);
		$this->makeDir($path);
		$image->save($path);

		return $location;
	}

	/**
	 * @param IResource $resource
	 * @return IFileResource
	 * @throws ImageStorageException
	 */
	public function save(IResource $resource): IFileResource {
		if ($resource instanceof UploadResource && !$resource->toModify()) {
			$served = $this->metaFactory->create($resource);

			$resource->setSaved();
			$location = $this->directory . $this->generateUniqueLocation($served);
			$this->makeDir($location);
			$resource->getUpload()->move($location);

			return $this->createResource($resource->getId());
		}
		if ($resource instanceof ITransferResource) {
			$resource->setSaved();
		} else if (!$resource->hasAliases()) {
			throw new ImageStorageException('Nothing to modify.');
		}

		$this->saveResource($resource);
		/*if ($resource instanceof ITransferResource) {
			return $this->createResource($resource->getId());
		}*/

		return $this->createResource($resource->getId());
	}

	/**
	 * @param IFileResource $src
	 * @param IFileResource $dest
	 * @throws ImageStorageException
	 */
	public function copy(IFileResource $src, IFileResource $dest) {
		if ($src->getId() === $dest->getId()) {
			throw new ImageStorageException('Cannot copy to same destination.');
		}
		$resource = new LocalResource(
			$this->directory . $this->getResourceLocation($this->metaFactory->create($src->getOriginal())), $dest->getId()
		);

		$resource->setAliases($dest->getAliases());
		$this->save($resource);
	}

	/**
	 * @param IFileResource $src
	 * @param IFileResource $dest
	 * @throws ImageStorageException
	 */
	public function move(IFileResource $src, IFileResource $dest) {
		$this->copy($src, $dest);
		$this->delete($src);
	}

	public function delete(IFileResource $resource) {
		$basePath = $resource->getNamespace();
		if ($basePath) {
			$basePath .= '/';
		}
		$location = $this->directory . $basePath;
		foreach (Finder::findFiles($resource->getName())->from($location)->limitDepth(1) as $file) {
			unlink((string) $file);
		}
		foreach (Finder::findDirectories('*')->in($location) as $dir) {
			@rmdir((string) $dir);
		}
	}

	public function getImageSize(IFileResource $resource): ImageSize {
		$served = $this->metaFactory->create($resource);

		[$width, $height] = getimagesize($this->directory . $this->getResourceLocation($served));

		return new ImageSize($width, $height);
	}

	/////////////////////////////////////////////////////////////////

	private function folder(?string $name): string {
		return ($name ? $name . '/' : '');
	}

	/**
	 * Location of resource
	 *
	 * @param IResourceMeta $resource
	 * @return string
	 */
	private function getResourceLocation(IResourceMeta $resource): string {
		$namespace = $resource->getNamespaceFolder();
		$hash = $resource->getHashFolder();

		return $this->folder($namespace) . $this->folder($hash) . $resource->getResource()->getName();
	}

	/**
	 * Absolute path of resource
	 *
	 * @param IResourceMeta $resource
	 * @return string
	 */
	private function getResourcePath(IResourceMeta $resource): string {
		return $this->directory . '/' . $this->getResourceLocation($resource);
	}

	private function makeDir(string $dir): void {
		$dir = dirname($dir);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
	}

	/**
	 * @param IResource $resource
	 * @throws ImageStorageException
	 */
	private function saveResource(IResource $resource) {
		$served = $this->metaFactory->create($resource);

		if ($resource instanceof ITransferResource) {
			$image = $this->imageFactory->createFromFile($resource->getLocation());
		} else if ($resource instanceof IFileResource) {
			$originalServed = $this->metaFactory->create($resource->getOriginal());
			$image = $this->imageFactory->createFromFile($this->directory . $this->getResourceLocation($originalServed));
		} else {
			throw new ImageStorageException('Resource must be instance of ITransferResource or IFileResource.');
		}

		$served->modify($image);
		$location = $this->generateUniqueLocation($served);

		$this->makeDir($this->directory . $location);
		$image->save($this->directory . $location);
	}

	private function generateUniqueLocation(IResourceMeta $served): string {
		$resource = $served->getResource();
		$location = $this->getResourceLocation($served);

		while (file_exists($this->directory . $location)) {
			$resource->generatePrefix();
			$location = $this->getResourceLocation($served);
		}

		return $location;
	}

}
