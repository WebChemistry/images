<?php

namespace WebChemistry\Images;


use Nette\Http\FileUpload;
use WebChemistry\Images\Image\ImageSize;
use WebChemistry\Images\Resources\FileResource;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Transfer\LocalResource;
use WebChemistry\Images\Resources\Transfer\UploadResource;

abstract class Storage implements IImageStorage {

	/**
	 * @param FileUpload $fileUpload
	 * @return UploadResource
	 */
	public function createUploadResource(FileUpload $fileUpload) {
		return new UploadResource($fileUpload);
	}

	/**
	 * @param $location
	 * @return LocalResource
	 */
	public function createLocalResource($location) {
		return new LocalResource($location, basename($location));
	}

	/**
	 * @param string $id
	 * @return FileResource
	 */
	public function createResource($id) {
		return new FileResource($id);
	}

	/**
	 * @param IFileResource $resource
	 * @return ImageSize
	 */
	abstract function getImageSize(IFileResource $resource);

	/**
	 * @param IFileResource $resource
	 * @return null|string
	 */
	abstract public function link(IFileResource $resource);

	/**
	 * @param IResource $resource
	 * @return IFileResource
	 */
	abstract public function save(IResource $resource);

	/**
	 * @param IFileResource $src
	 * @param IFileResource $dest
	 * @return void
	 */
	abstract public function copy(IFileResource $src, IFileResource $dest);

	/**
	 * @param IFileResource $src
	 * @param IFileResource $dest
	 * @return void
	 */
	abstract public function move(IFileResource $src, IFileResource $dest);

	/**
	 * @param IFileResource $resource
	 * @return void
	 */
	abstract public function delete(IFileResource $resource);

}
