<?php declare(strict_types = 1);

namespace WebChemistry\Images\Modifiers\Params;

use Nette\SmartObject;
use Nette\Utils\Image;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Meta\IResourceMeta;
use WebChemistry\Images\Resources\Transfer\ITransferResource;

final class ModifierParam {

	use SmartObject;

	/** @var Image */
	private $image;

	/** @var IResourceMeta */
	private $meta;

	/** @var string|null */
	private $location;

	public function __construct(Image $image, ?string $location, IResourceMeta $meta) {
		$this->image = $image;
		$this->meta = $meta;
		$this->location = $location;

		$resource = $this->getResource();
		if (!$this->location && $resource instanceof ITransferResource) {
			$this->location = $resource->getLocation();
		}

		if (!$this->location || !file_exists($this->location)) {
			$this->location = null;
		}
	}

	public function getLocation(): ?string {
		return $this->location;
	}

	public function getImage(): Image {
		return $this->image;
	}

	public function getMeta(): IResourceMeta {
		return $this->meta;
	}

	public function getResource(): IResource {
		return $this->meta->getResource();
	}

}
