<?php

namespace WebChemistry\Images\Template;


use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Resources\IFileResource;

class ImageFacade {

	/** @var IImageStorage */
	private $storage;

	/** @var IImageModifiers @internal */
	public $imageModifiers;

	public function __construct(IImageStorage $storage, IImageModifiers $imageModifiers) {
		$this->storage = $storage;
		$this->imageModifiers = $imageModifiers;
	}

	/**
	 * @param string $id
	 * @param array ...$aliases
	 * @return string
	 */
	public function create($id, ...$aliases) {
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
