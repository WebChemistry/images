<?php declare(strict_types = 1);

namespace WebChemistry\Images\Modifiers\Params;

use Nette\SmartObject;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Meta\IResourceMeta;

final class ResourceModifierParam {

	use SmartObject;

	/** @var IResourceMeta */
	private $meta;

	public function __construct(IResourceMeta $meta) {
		$this->meta = $meta;
	}

	public function getMeta(): IResourceMeta {
		return $this->meta;
	}

	public function getResource(): IResource {
		return $this->meta->getResource();
	}

}
