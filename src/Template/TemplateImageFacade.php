<?php declare(strict_types = 1);

namespace WebChemistry\Images\Template;

use InvalidArgumentException;
use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Resources\EmptyResource;
use WebChemistry\Images\Resources\IFileResource;

class TemplateImageFacade {

	/** @var IImageStorage */
	private $storage;

	public function __construct(IImageStorage $storage) {
		$this->storage = $storage;
	}

	public function create($id, array $filters): ?IFileResource {
		if (!$id) {
			$resource = new EmptyResource();
		} else if (is_string($id)) {
			$resource = $this->storage->createResource($id);
		} else if ($id instanceof IFileResource) {
			$resource = clone $id;
		} else {
			throw new InvalidArgumentException('ID must be null, string or instance of ' . IFileResource::class . '.');
		}
		foreach ($filters as $name => $arguments) {
			$resource->setFilter($name, $arguments);
		}

		return $resource;
	}

	protected function parseModifiers(array $modifiers): array {
		$options = [];

		if (isset($modifiers['default'])) {
			if (count($modifiers['default']) !== 1) {
				throw new InvalidArgumentException('Modifier default accept exactly 1 argument');
			}
			$options['default'] = $modifiers['default'][0];

			unset($modifiers['default']);
		}
		if (isset($modifiers['baseUrl'])) {
			if (count($modifiers['baseUrl']) !== 0) {
				throw new InvalidArgumentException('Modifier baseUrl accept exactly 1 argument');
			}
			$options['baseUrl'] = true;

			unset($modifiers['baseUrl']);
		}

		if ($modifiers) {
			throw new InvalidArgumentException(sprintf('Modifiers %s are not allowed', implode(', ', array_keys($modifiers))));
		}

		return $options;
	}

	/**
	 * @param IFileResource $resource
	 * @return string|null
	 */
	public function link(IFileResource $resource, array $modifiers): ?string {
		return $this->storage->link($resource, $this->parseModifiers($modifiers));
	}

}
