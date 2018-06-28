<?php declare(strict_types = 1);

namespace WebChemistry\Images\Storages;

use Nette\Http\IRequest;
use Nette\Utils\Finder;
use WebChemistry\Images\Helpers;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Image\ImageSize;
use WebChemistry\Images\ImageStorageException;
use WebChemistry\Images\Modifiers\BaseModifiers;
use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Transfer\LocalResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;
use WebChemistry\Images\Resources\Transfer\UploadResource;
use WebChemistry\Images\Storage;
use WebChemistry\Images\Storages\LocalStorage\LocalModifiers;

class LocalStorage extends Storage {

	/** @var string */
	private $directory;

	/** @var ModifierContainer */
	private $modifierContainer;

	/** @var string|null */
	private $defaultImage;

	/** @var string */
	private $basePath;

	/** @var string */
	private $baseUri;

	/** @var IImageFactory */
	private $imageFactory;

	public function __construct(string $wwwDir, string $assetsDir, ModifierContainer $modifierContainer, IRequest $request,
								IImageFactory $imageFactory, ?string $defaultImage = null) {
		$assetsDir = trim($assetsDir, '/\\');
		$assetsDir = ($assetsDir ? $assetsDir . '/' : '');
		$modifierContainer->addLoader(new BaseModifiers());
		$modifierContainer->addLoader(new LocalModifiers());

		$this->modifierContainer = $modifierContainer;
		$this->directory = $wwwDir . '/' . $assetsDir;
		$this->defaultImage = $defaultImage;
		$this->basePath = $request->getUrl()->getBasePath() . $assetsDir;
		$this->baseUri = $request->getUrl()->getBaseUrl() . $assetsDir;
		$this->imageFactory = $imageFactory;
	}

	public function link(IFileResource $resource): ?string {
		$location = $this->getLink($resource);
		$parameters = $this->modifierContainer->getImageParameters($resource);

		// Image not exists
		$defaultImage = $parameters->getDefaultImage() ? : $this->defaultImage;
		if ($location === false && $defaultImage) {
			if ($defaultImage && $location === false) {
				$default = $this->createResource($defaultImage);
				$default->setAliases($resource->getAliases());
				$location = $this->getLink($default);
			}
		}

		return $location === false ? null : ($parameters->getParameter('baseUri') ? $this->baseUri : $this->basePath). $location;
	}

	/**
	 * @param IFileResource $resource
	 * @return bool|string false - not exists
	 * @throws \WebChemistry\Images\Modifiers\ModifierException
	 */
	protected function getLink(IFileResource $resource) {
		$location = $this->getResourceLocation($resource);
		$path = $this->directory . $location;
		if (file_exists($path)) {
			return $location;
		}
		if (!$resource->toModify()) {
			return false;
		}

		// resize image
		$originalPath = $this->directory . $this->getResourceLocation($resource->getOriginal());
		if (!file_exists($originalPath)) {
			return false;
		}
		$image = $this->imageFactory->createFromFile($originalPath);
		$this->modifierContainer->modifyImage($resource, $image);
		$this->makeDir($path);
		$image->save($path);

		return $location;
	}

	public function save(IResource $resource): IFileResource {
		if ($resource instanceof UploadResource && !$resource->toModify()) {
			$resource->setSaved();
			$location = $this->directory . $this->generateUniqueLocation($resource);
			$this->makeDir($location);
			$resource->getUpload()->move($location);

			return $this->createResource($resource->getId());
		}
		if ($resource instanceof ITransferResource) {
			$resource->setSaved();
		} else if (!$resource->toModify()) {
			throw new ImageStorageException('Nothing to modify.');
		}

		$this->saveResource($resource);
		/*if ($resource instanceof ITransferResource) {
			return $this->createResource($resource->getId());
		}*/

		return $this->createResource($resource->getId());
	}

	public function copy(IFileResource $src, IFileResource $dest) {
		if ($src->getId() === $dest->getId()) {
			throw new ImageStorageException('Cannot copy to same destination.');
		}
		$resource = new LocalResource(
			$this->directory . $this->getResourceLocation($src->getOriginal()), $dest->getId()
		);

		$resource->setAliases($dest->getAliases());
		$this->save($resource);
	}

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
		list($width, $height) = getimagesize($this->directory . $this->getResourceLocation($resource));

		return new ImageSize($width, $height);
	}

	/////////////////////////////////////////////////////////////////

	private function getResourceLocation(IResource $resource) {
		return Helpers::getResourceHash($resource, $this->modifierContainer->extractActiveAliases($resource));
	}

	private function makeDir(string $dir): void {
		$dir = dirname($dir);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
	}

	private function saveResource(IResource $resource) {
		if ($resource instanceof ITransferResource) {
			$image = $resource->toImage($this->imageFactory);
		} else if ($resource instanceof IFileResource) {
			$image = $this->imageFactory->createFromFile($this->directory . $this->getResourceLocation($resource->getOriginal()));
		} else {
			throw new ImageStorageException('Resource must be instance of ITransferResource or IFileResource.');
		}

		$this->modifierContainer->modifyImage($resource, $image);
		$location = $this->generateUniqueLocation($resource);

		$dir = dirname($this->directory . $location);
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}

		$image->save($this->directory . $location);
	}

	private function generateUniqueLocation(IResource $resource): string {
		$location = $this->getResourceLocation($resource);

		while (file_exists($this->directory . $location)) {
			$resource->generatePrefix();
			$location = $this->getResourceLocation($resource);
		}

		return $location;
	}

}
