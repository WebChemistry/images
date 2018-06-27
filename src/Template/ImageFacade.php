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

	/**
	 * @param string|null $id
	 * @param array $aliases
	 * @return string
	 */
	public function create(?string $id, array $aliases = []) {
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
	 * @return string
	 */
	public function link(IFileResource $resource) {
		return $this->storage->link($resource);
	}

}
