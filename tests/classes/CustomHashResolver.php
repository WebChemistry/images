<?php declare(strict_types = 1);

namespace Test;

use WebChemistry\Images\Resolvers\HashResolver;
use WebChemistry\Images\Resources\Meta\IResourceMeta;

class CustomHashResolver extends HashResolver {

	/** @var bool */
	public $useCustom = false;

	public function getOriginal(IResourceMeta $meta): ?string {
		if ($this->useCustom) {
			return null;
		}

		return parent::getOriginal($meta);
	}

	public function resolve(IResourceMeta $meta): ?string {
		if ($this->useCustom) {
			$aliases = $meta->getSignature();
			if (!$aliases) {
				return null;
			}
		}

		return parent::resolve($meta);
	}

}
