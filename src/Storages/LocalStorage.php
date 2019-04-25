<?php declare(strict_types = 1);

namespace WebChemistry\Images\Storages;

use Nette\Http\IRequest;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\ImageException;
use Nette\Utils\UnknownImageFileException;
use Tracy\ILogger;
use WebChemistry\Images\Helpers;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Image\ImageSize;
use WebChemistry\Images\ImageStorageException;
use WebChemistry\Images\Resources\EmptyResource;
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
	protected $directory;

	/** @var string|null */
	protected $defaultImage;

	/** @var string */
	protected $basePath;

	/** @var string */
	protected $baseUrl;

	/** @var IResourceMetaFactory */
	protected $metaFactory;

	/** @var IImageFactory */
	protected $imageFactory;

	/** @var ILogger|null */
	protected $logger;

	/** @var bool */
	protected $safeLink;

	public function __construct(string $wwwDir, string $assetsDir, ?ILogger $logger, IResourceMetaFactory $metaFactory, IRequest $request,
								IImageFactory $imageFactory, bool $safeLink = false, ?string $defaultImage = null) {
		$this->metaFactory = $metaFactory;
		$this->defaultImage = $defaultImage;

		// paths
		$assetsDir = Helpers::normalizePath($assetsDir);

		$this->directory = $wwwDir . '/' . $assetsDir;
		$this->basePath = $request->getUrl()->getBasePath() . $assetsDir;
		$this->baseUrl = $request->getUrl()->getBaseUrl() . $assetsDir;
		$this->imageFactory = $imageFactory;
		$this->logger = $logger;
		$this->safeLink = $safeLink;
	}

	protected function getDefaultImage(?IFileResource $resource): ?string {
		$defaultImage = $this->defaultImage;
		if ($resource) {
			// "fill method" getDefaultImage()
			$this->metaFactory->create($resource);

			if ($resource->getDefaultImage()) {
				$defaultImage = $resource->getDefaultImage();
			}
		}

		if (!$defaultImage) {
			return null;
		}

		$default = $this->createResource($defaultImage);
		$default->setAliases($resource->getAliases());

		return $this->getLink($default);
	}

	public function link(?IFileResource $resource): ?string {
		try {
			$location = null;
			if ($resource && !$resource->isEmpty()) {
				$location = $this->getLink($resource);
			}

			if ($location === null) {
				$location = $this->getDefaultImage($resource);
			}
		} catch (ImageException $e) {
			if (!$this->safeLink) {
				throw $e;
			}

			$this->logger->log($e);

			// try default image
			try {
				$location = $this->getDefaultImage($resource);
			} catch (\Throwable $e) {
				$location = null;
			}
		}

		return $location === null ? null : ($resource->isBaseUrl() ? $this->baseUrl : $this->basePath). $location;
	}

	/**
	 * @param IResource $resource
	 * @return IFileResource
	 * @throws ImageStorageException
	 */
	public function save(IResource $resource): IFileResource {
		$this->getLink($resource);
		if ($resource instanceof IFileResource) {
			return $resource;
		}

		return $this->createResource($resource->getId());
	}

	/**
	 * @param IResource $resource
	 * @throws ImageStorageException
	 */
	protected function getLink(IResource $resource): ?string {
		$meta = $this->metaFactory->create($resource);
		$originalPath = null;

		if ($resource instanceof ITransferResource) {
			$resource->setSaved();

			$location = $this->generateUniqueLocation($meta);
			$this->makeDir($this->directory . $location);

			// gif animation
			if ($resource instanceof UploadResource && !$meta->toModify()) {
				$resource->getUpload()->move($this->directory . $location);

				return $location;
			}

			$image = $resource->getProvider()->toImage($this->imageFactory);
			$path = $this->directory . $location;
		} else if ($resource instanceof IFileResource) {
			$location = $this->getResourceLocation($meta);
			$path = $this->directory . $location;
			if (is_file($path)) {
				return $location;
			}
			if (!$meta->toModify()) {
				return null;
			}

			// resize image
			$originalPath = $this->getResourcePath($this->metaFactory->create($resource->getOriginal()));
			if (!is_file($originalPath)) {
				return null;
			}

			try {
				$image = $this->imageFactory->createFromFile($originalPath);
			} catch (UnknownImageFileException $e) {
				return null;
			}
		} else {
			throw new ImageStorageException('Resource must be instance of ITransferResource or IFileResource.');
		}

		$meta->modify($image, $originalPath);
		$this->makeDir($path);
		$image->save($path);

		// clean
		imagedestroy($image->getImageResource());

		return $location;
	}

	/**
	 * @param IFileResource $src
	 * @param IFileResource $dest
	 * @return IFileResource
	 * @throws ImageStorageException
	 */
	public function copy(IFileResource $src, IFileResource $dest): IFileResource {
		if ($src->getId() === $dest->getId()) {
			throw new ImageStorageException('Cannot copy to same destination.');
		}
		$resource = new LocalResource(
			$this->directory . $this->getResourceLocation($this->metaFactory->create($src->getOriginal())), $dest->getId()
		);

		$resource->setAliases($dest->getAliases());
		$this->save($resource);

		return $dest;
	}

	/**
	 * @param IFileResource $src
	 * @param IFileResource $dest
	 * @return IFileResource
	 * @throws ImageStorageException
	 */
	public function move(IFileResource $src, IFileResource $dest): IFileResource {
		$this->copy($src, $dest);
		$this->delete($src);

		return $dest;
	}

	public function delete(IFileResource $resource): IFileResource {
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

		return new EmptyResource();
	}

	public function getImageSize(IFileResource $resource): ImageSize {
		$meta = $this->metaFactory->create($resource);

		[$width, $height] = getimagesize($this->directory . $this->getResourceLocation($meta));

		return new ImageSize($width, $height);
	}

	/////////////////////////////////////////////////////////////////

	protected function folder(?string $name): string {
		return ($name ? $name . '/' : '');
	}

	/**
	 * Location of resource
	 *
	 * @param IResourceMeta $resource
	 * @return string
	 */
	protected function getResourceLocation(IResourceMeta $resource): string {
		$namespace = $resource->getNamespaceFolder();
		$hash = $resource->getResource() instanceof ITransferResource ? $resource->getOriginalHashFolder() : $resource->getHashFolder();

		return $this->folder($namespace) . $this->folder($hash) . $resource->getResource()->getName();
	}

	/**
	 * Absolute path of resource
	 *
	 * @param IResourceMeta $resource
	 * @return string
	 */
	protected function getResourcePath(IResourceMeta $resource): string {
		return $this->directory . '/' . $this->getResourceLocation($resource);
	}

	protected function makeDir(string $dir): void {
		$dir = dirname($dir);

		FileSystem::createDir($dir);
	}

	protected function generateUniqueLocation(IResourceMeta $meta): string {
		$resource = $meta->getResource();
		$location = $this->getResourceLocation($meta);

		while (file_exists($this->directory . $location)) {
			$resource->generatePrefix();
			$location = $this->getResourceLocation($meta);
		}

		return $location;
	}

}
