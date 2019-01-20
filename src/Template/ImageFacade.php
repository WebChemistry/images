<?php declare(strict_types = 1);

namespace WebChemistry\Images\Template;

use InvalidArgumentException;
use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Resources\EmptyResource;
use WebChemistry\Images\Resources\IFileResource;

class ImageFacade {

	/** @var IImageStorage */
	private $storage;

	public function __construct(IImageStorage $storage) {
		$this->storage = $storage;
	}

	public function create($id, array $aliases = []): ?IFileResource {
		if (!$id) {
			$resource = new EmptyResource();
		} else if (is_string($id)) {
			$resource = $this->storage->createResource($id);
		} else if ($id instanceof IFileResource) {
			$resource = clone $id;
		} else {
			throw new InvalidArgumentException('ID must be null, string or instance of ' . IFileResource::class . '.');
		}
		$resource->setAliases($aliases);

		return $resource;
	}

	/**
	 * @param IFileResource $resource
	 * @return string|null
	 */
	public function link(IFileResource $resource): ?string {
		return $this->storage->link($resource);
	}

}
