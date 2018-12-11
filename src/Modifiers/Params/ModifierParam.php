<?php declare(strict_types = 1);

namespace WebChemistry\Images\Modifiers\Params;

use Nette\SmartObject;
use Nette\Utils\Image;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Meta\IResourceMeta;

final class ModifierParam {

	use SmartObject;

	/** @var Image */
	private $image;

	/** @var IResourceMeta */
	private $meta;

	public function __construct(Image $image, IResourceMeta $meta) {
		$this->image = $image;
		$this->meta = $meta;
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
