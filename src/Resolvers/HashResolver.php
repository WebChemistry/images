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

	/**
	 * Returns alias hash or 'original'
	 *
	 * @param IResourceMeta $resourceServed
	 * @return string|null
	 */
	public function resolve(IResourceMeta $resourceServed): ?string {
		$aliases = $resourceServed->getSignature();
		if (!$aliases) {
			return $this->original;
		}

		return Helpers::getNameByAliases($aliases);
	}

}
