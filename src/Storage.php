<?php

namespace WebChemistry\Images;


use Nette\Http\FileUpload;
use WebChemistry\Images\Resources\FileResource;
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

}
