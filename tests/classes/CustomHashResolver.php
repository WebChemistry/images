<?php declare(strict_types = 1);

namespace Test;

use WebChemistry\Images\Resolvers\HashResolver;
use WebChemistry\Images\Resources\IResource;

class CustomHashResolver extends HashResolver {

	/** @var bool */
	public $useCustom = false;

	public function getOriginal(IResource $resource): ?string {
		if ($this->useCustom) {
			return null;
		}

		return parent::getOriginal($resource);
	}

	public function resolve(IResource $resource): ?string {
		if ($this->useCustom) {
			$filters = $resource->getFilters();
			if (!$filters) {
				return null;
			}
		}

		return parent::resolve($resource);
	}

}
