<?php
namespace WebChemistry\Images\Storages;


use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;
use WebChemistry\Images\Storage;
use WebChemistry\Images\Storages\S3\S3Facade;

class S3Storage extends Storage{

	/** @var S3Facade */
	private $facade;

	/**
	 * S3Storage constructor.
	 */
	public function __construct(array $config, ModifierContainer $modifierContainer){
		$this->facade = new S3Facade($config, $modifierContainer);
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