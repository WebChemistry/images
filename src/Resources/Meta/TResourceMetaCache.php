<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Meta;

trait TResourceMetaCache {

	/**
	 * @var IResourceMeta|null
	 * @internal do not change manually, only for caching
	 */
	public $__meta;

	public function __clone() {
		$this->__meta = null;
	}

}
