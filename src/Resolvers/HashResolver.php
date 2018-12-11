<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resolvers;

use WebChemistry\Images\Helpers;
use WebChemistry\Images\Resources\Meta\IResourceMeta;

class HashResolver implements IHashResolver {

	private const ORIGINAL = 'original';

	/** @var string */
	protected $original;

	public function __construct(string $original = self::ORIGINAL) {
		$this->original = $original;
	}

	public function getOriginal(IResourceMeta $meta): ?string {
		return $this->original;
	}

	/**
	 * Returns alias hash or 'original'
	 *
	 * @param IResourceMeta $meta
	 * @return string|null
	 */
	public function resolve(IResourceMeta $meta): ?string {
		$aliases = $meta->getSignature();
		if (!$aliases) {
			return $this->getOriginal($meta);
		}

		return Helpers::getNameByAliases($aliases);
	}

}
