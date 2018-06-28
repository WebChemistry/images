<?php declare(strict_types = 1);

namespace WebChemistry\Images;

use Nette\Http\FileUpload;
use WebChemistry\Images\Image\ImageSize;
use WebChemistry\Images\Resources\FileResource;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;
use WebChemistry\Images\Resources\Transfer\LocalResource;
use WebChemistry\Images\Resources\Transfer\UploadResource;

abstract class Storage implements IImageStorage {

	/**
	 * @param FileUpload $fileUpload
	 * @return UploadResource
	 * @throws Resources\ResourceException
	 */
	public function createUploadResource(FileUpload $fileUpload): ITransferResource {
		return new UploadResource($fileUpload);
	}

	/**
	 * @param string $location
	 * @return LocalResource
	 */
	public function createLocalResource(string $location) {
		return new LocalResource($location, basename($location));
	}

	/**
	 * @param string $id
	 * @return FileResource
	 * @throws Resources\ResourceException
	 */
	public function createResource(string $id): IFileResource {
		return new FileResource($id);
	}

	/**
	 * @param IFileResource $resource
	 * @return ImageSize
	 */
	abstract function getImageSize(IFileResource $resource): ImageSize;

	/**
	 * @param IFileResource $resource
	 * @return string|null
	 */
	abstract public function link(IFileResource $resource): ?string;

	/**
	 * {@inheritdoc}
	 */
	abstract public function save(IResource $resource): IFileResource;

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
