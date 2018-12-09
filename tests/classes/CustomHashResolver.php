<?php declare(strict_types = 1);

namespace Test;

use WebChemistry\Images\Resolvers\HashResolver;
use WebChemistry\Images\Resources\Meta\IResourceMeta;

class CustomHashResolver extends HashResolver {

	/** @var bool */
	public $useCustom = false;

	public function resolve(IResourceMeta $resourceServed): ?string {
		if ($this->useCustom) {
			$aliases = $resourceServed->getSignature();
			if (!$aliases) {
				return null;
			}
		}

		return parent::resolve($resourceServed);
	}

}
