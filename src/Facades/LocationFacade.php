<?php declare(strict_types = 1);

namespace WebChemistry\Images\Facades;

use Nette\SmartObject;
use WebChemistry\Images\Resources\IResource;

final class LocationFacade {

	use SmartObject;

	/** @var StorageFacade */
	private $storageFacade;

	/** @var string */
	private $wwwDir;

	/** @var string|null */
	private $assetsDir;

	/** @var string */
	private $absoluteDir;

	public function __construct(string $wwwDir, ?string $assetsDir, StorageFacade $storageFacade) {
		$this->wwwDir = rtrim($wwwDir);
		$this->assetsDir = $assetsDir ? ltrim($assetsDir) : null;
		$this->storageFacade = $storageFacade;

		$this->absoluteDir = $wwwDir . ($assetsDir ? '/' . $assetsDir : '');
	}

	public function getAssetsDir(): ?string {
		return $this->assetsDir;
	}

	/**
	 * Makes relative location absolute
	 */
	public function absolutizeRelativeLocation(string $location): string {
		return $this->absoluteDir . '/' . $location;
	}

	/**
	 * Relative of resource (<namespace>/<hash>/<resource>)
	 */
	public function getResourceLocation(IResource $resource): string {
		$namespace = $this->storageFacade->getNamespaceFolder($resource);
		$hash = $this->storageFacade->getHashFolder($resource);

		return $namespace . $hash . $resource->getName();
	}

	/**
	 * Absolute path of resource (%wwwDir%/%assetsDir%/<namespace>/<hash>/<resource>)
	 */
	public function getAbsoluteLocation(IResource $resource): string {
		return $this->absolutizeRelativeLocation($this->getResourceLocation($resource));
	}

	public function generateUniqueLocation(IResource $resource): string {
		$location = $this->getResourceLocation($resource);

		while (file_exists($this->absolutizeRelativeLocation($location))) {
			$resource->generatePrefix();
			$location = $this->getResourceLocation($resource);
		}

		return $location;
	}

}
