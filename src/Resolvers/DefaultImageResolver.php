<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resolvers;

use WebChemistry\Images\Resources\FileResource;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;

final class DefaultImageResolver implements IDefaultImageResolver {

	/** @var array */
	private $defaults = [];

	/** @var string|null */
	private $global;

	public function __construct(array $defaults = []) {
		$this->defaults = $defaults;
		$this->global = $this->defaults['*'] ?? null;
	}

	public function resolve(?IResource $resource, ?string $default = null): ?IFileResource {
		if ($default && strpos($default, '.') === false) {
			$default = $this->defaults[$default] ?? null;
		}

		$id = $default ?: $this->global;
		if ($resource) {
			$namespace = $resource->getNamespace();

			$id = $this->defaults[$namespace] ?? $id;
		}

		if (!$id) {
			return null;
		}

		$default = new FileResource($id);
		$default->setFilters($resource->getFilters());

		return $default;
	}

}
