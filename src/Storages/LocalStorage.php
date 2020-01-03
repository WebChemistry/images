<?php declare(strict_types = 1);

namespace WebChemistry\Images\Storages;

use Nette\Http\IRequest;
use Nette\Utils\FileSystem;
use Nette\Utils\ImageException;
use Nette\Utils\UnknownImageFileException;
use Tracy\ILogger;
use WebChemistry\Images\Facades\LocationFacade;
use WebChemistry\Images\Facades\StorageFacade;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Exceptions\ImageStorageException;
use WebChemistry\Images\Resolvers\IImageSuffixResolver;
use WebChemistry\Images\Resources\EmptyResource;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Transfer\LocalResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;
use WebChemistry\Images\Resources\Transfer\UploadResource;
use WebChemistry\Images\Storage;
use WebChemistry\Images\Utils\FixOrientation;
use WebChemistry\Images\Utils\ISafeLink;
use WebChemistry\Images\Utils\ISafeLinkFactory;

class LocalStorage extends Storage {

	/** @var string */
	protected $basePath;

	/** @var string */
	protected $baseUrl;

	/** @var ILogger|null */
	protected $logger;

	/** @var StorageFacade */
	private $storageFacade;

	/** @var IImageSuffixResolver */
	private $imageSuffixResolver;

	/** @var IImageFactory */
	private $imageFactory;

	/** @var LocationFacade */
	private $locationFacade;

	/** @var ISafeLink */
	private $safeLink;

	/** @var FixOrientation */
	private $fixOrientation;

	public function __construct(?ILogger $logger, IRequest $request, StorageFacade $storageFacade, LocationFacade $locationFacade,
								IImageFactory $imageFactory, IImageSuffixResolver $imageSuffixResolver, ISafeLinkFactory $safeLinkFactory,
								FixOrientation $fixOrientation) {
		$this->logger = $logger;
		$this->storageFacade = $storageFacade;
		$this->imageSuffixResolver = $imageSuffixResolver;
		$this->imageFactory = $imageFactory;
		$this->locationFacade = $locationFacade;
		$this->safeLink = $safeLinkFactory->create(function (IFileResource $resource): ?string {
			return $this->getLink($resource);
		});

		$assetsDir = $locationFacade->getAssetsDir() ? '/' . $locationFacade->getAssetsDir() : '';
		$this->basePath = rtrim($request->getUrl()->getBasePath(), '/') . $assetsDir;
		$this->baseUrl = rtrim($request->getUrl()->getBaseUrl(), '/') . $assetsDir;
		$this->fixOrientation = $fixOrientation;
	}

	/**
	 * @param mixed[] $options
	 */
	public function link(?IFileResource $resource, array $options = []): ?string {
		$baseUrl = $options['baseUrl'] ?? false;

		$location = $this->safeLink->call($resource, $options);
		if (!$location) {
			return null;
		}

		return ($baseUrl ? $this->baseUrl : $this->basePath) . '/' . $location;
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
	 * @throws ImageStorageException
	 * @throws ImageException
	 */
	protected function getLink(IResource $resource): ?string {
		$originalPath = null;

		if ($resource instanceof ITransferResource) {
			$resource->setSaved();

			// fix suffix
			$this->imageSuffixResolver->resolve($resource);

			$relativePath = $this->locationFacade->generateUniqueLocation($resource);
			$this->makeDir($absolutePath = $this->locationFacade->absolutizeRelativeLocation($relativePath));

			// gif animation fix
			if ($resource instanceof UploadResource && !$resource->getFilters()) {
				$resource->getUpload()->move($absolutePath);

				return $relativePath;
			}

			$image = $this->storageFacade->transferResourceToImage($resource);
			$this->fixOrientation->fix($resource, $image);

		} else if ($resource instanceof IFileResource) {
			$relativePath = $this->locationFacade->getResourceLocation($resource);
			$absolutePath = $this->locationFacade->absolutizeRelativeLocation($relativePath);
			if (is_file($absolutePath)) {
				return $relativePath;
			}
			if (!$resource->getFilters()) {
				return null;
			}

			// resize image
			$originalPath = $this->locationFacade->getAbsoluteLocation($resource->getOriginal());
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

		$this->storageFacade->filter($resource, $image);

		$this->makeDir($absolutePath);
		$image->save($absolutePath);

		// clean
		imagedestroy($image->getImageResource());

		return $relativePath;
	}

	/**
	 * @param IFileResource $src
	 * @param IFileResource $dest
	 * @return IFileResource
	 * @throws ImageStorageException
	 */
	public function copy(IFileResource $src, IFileResource $dest): IFileResource {
		if ($src->getId() === $dest->getId()) {
			return $dest;
		}

		$resource = new LocalResource(
			$this->locationFacade->getAbsoluteLocation($src->getOriginal()), $dest->getId()
		);
		$resource->setFilters($dest->getFilters());

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
		$basePath = (string) $resource->getNamespace();
		if ($basePath) {
			$basePath .= '/';
		}
		$location = $this->locationFacade->absolutizeRelativeLocation($basePath);
		if (!is_dir($location)) {
			return new EmptyResource();
		}

		$this->storageFacade->cleanResourceDirectory($resource, $location);

		return new EmptyResource();
	}

	/////////////////////////////////////////////////////////////////

	protected function makeDir(string $dir): void {
		$dir = dirname($dir);

		FileSystem::createDir($dir, 0777);
	}

}
