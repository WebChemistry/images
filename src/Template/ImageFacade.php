<?php

namespace WebChemistry\Images\Template;

use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Resources\IFileResource;

class ImageFacade {

	/** @var IImageStorage */
	private $storage;

	public function __construct(IImageStorage $storage) {
		$this->storage = $storage;
	}

	public function create($id, array $aliases = []): ?IFileResource {
		if (!$id instanceof IFileResource) {
			$resource = $this->storage->createResource($id);
		} else {
			$resource = $id;
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
