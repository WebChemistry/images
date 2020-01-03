<?php declare(strict_types = 1);

namespace WebChemistry\Images\Facades;

use Nette\Utils\Finder;
use Nette\Utils\Image;
use WebChemistry\Images\Filters\IImageFilter;
use WebChemistry\Images\Image\IImage;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Resolvers\IHashResolver;
use WebChemistry\Images\Resolvers\INamespaceResolver;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;

final class StorageFacade {

	/** @var IHashResolver */
	private $hashResolver;

	/** @var INamespaceResolver */
	private $namespaceResolver;

	/** @var IImageFilter */
	private $imageFilter;

	/** @var IImageFactory */
	private $imageFactory;

	public function __construct(IHashResolver $hashResolver, INamespaceResolver $namespaceResolver, IImageFactory $imageFactory,
								IImageFilter $imageFilter) {
		$this->hashResolver = $hashResolver;
		$this->namespaceResolver = $namespaceResolver;
		$this->imageFilter = $imageFilter;
		$this->imageFactory = $imageFactory;
	}

	public function resolveHash(IResource $resource): ?string {
		return $this->hashResolver->resolve($resource);
	}

	public function resolveOriginalHash(IResource $resource): ?string {
		return $this->hashResolver->getOriginal($resource);
	}

	public function resolveNamespace(IResource $resource): ?string {
		return $this->namespaceResolver->resolve($resource);
	}

	public function getNamespaceFolder(IResource $resource): string {
		$folder = $this->resolveNamespace($resource);

		return $folder ? $folder . '/' : '';
	}

	public function getHashFolder(IResource $resource): string {
		$folder = $resource instanceof ITransferResource ? $this->resolveOriginalHash($resource) : $this->resolveHash($resource);

		return $folder ? $folder . '/' : '';
	}

	public function transferResourceToImage(ITransferResource $resource): Image {
		return $resource->getProvider()->toImage($this->imageFactory);
	}

	public function cleanResourceDirectory(IResource $resource, string $directory) {
		foreach (Finder::findFiles($resource->getName())->from($directory)->limitDepth(1) as $file) {
			unlink((string) $file);
		}
		foreach (Finder::findDirectories('*')->in($directory) as $dir) {
			@rmdir((string) $dir);
		}
	}

	/**
	 * @return mixed
	 */
	public function filter(IResource $resource, ?IImage $image = null) {
		return $this->imageFilter->filter($resource, $image);
	}

}
