<?php

namespace WebChemistry\Images\Storages;


use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;
use WebChemistry\Images\Storage;
use WebChemistry\Images\Storages\Cloudinary\CloudinaryFacade;

class CloudinaryStorage extends Storage {

	/** @var CloudinaryFacade */
	private $facade;

	public function __construct(ModifierContainer $modifierContainer, array $config) {
		$this->facade = new CloudinaryFacade($config, $modifierContainer);
	}

	public function link(IFileResource $resource) {
		return $this->facade->link($resource);
	}

	public function save(IResource $resource) {
		if (!$resource instanceof ITransferResource) {
			return $resource; // or throw exception?
		}
		$resource->setSaved();
		return $this->facade->save($resource);
	}

	public function copy(IFileResource $src, IFileResource $dest) {
		$this->facade->copy($src, $dest);
	}

	public function move(IFileResource $src, IFileResource $dest) {
		$this->facade->move($src, $dest);
	}

	public function delete(IFileResource $resource) {
		$this->facade->delete($resource);
	}

}
