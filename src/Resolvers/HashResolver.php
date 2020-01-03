<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resolvers;

use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Meta\IResourceMeta;

class HashResolver implements IHashResolver {

	private const FOLDER_SIZE = 247;
	private const FOLDER_WITH_MD5_SIZE = 215; // 247 - 32

	protected const ORIGINAL = 'original';
	protected const ALIAS_DEL = '.';
	protected const PARAM_DEL = '_';

	/** @var string */
	protected $original;

	public function __construct(string $original = self::ORIGINAL) {
		$this->original = $original;
	}

	public function getOriginal(IResource $resource): ?string {
		return $this->original;
	}

	/**
	 * Returns alias hash or 'original'
	 *
	 * @param IResourceMeta $meta
	 * @return string|null
	 */
	public function resolve(IResource $resource): ?string {
		$hash = $this->getFilterHash($resource);
		if ($hash === null) {
			return $this->getOriginal($resource);
		}

		return $hash;
	}

	protected function getFilterHash(IResource $resource): ?string {
		$filters = $resource->getFilters();
		if (!$filters) {
			return null;
		}

		uksort($filters, function ($a, $b) {
			return strcmp($a, $b);
		});
		$name = implode(self::ALIAS_DEL, array_keys($filters));
		if (strlen($name) > self::FOLDER_WITH_MD5_SIZE) {
			throw new \LogicException('Maximum length of aliases is ' . self::FOLDER_WITH_MD5_SIZE);
		}

		$params = [];
		foreach ($filters as $filter) {
			foreach ($filter->getArguments() as $argument) {
				if (is_bool($argument)) {
					$argument = $argument ? '1' : '0';
				} else {
					$argument = (string)$argument;
				}
				$this->validateArgument($argument);
				$params[] = $argument;
			}
		}
		if (!$params) {
			return $name;
		}

		$params = '_' . implode(self::PARAM_DEL, $params);
		$hash = $name . $params;
		if (strlen($hash) > self::FOLDER_SIZE) {
			$hash = $name . '_' . md5(substr($params, 1));
		}

		return $hash;
	}

	private static function validateArgument(string $value): void {
		if (!preg_match('#^[0-9a-zA-Z.]+$#', $value)) {
			throw new \LogicException("Parameter '$value' has disallowed characters");
		}
	}

}
