<?php declare(strict_types = 1);

namespace WebChemistry\Images;

use Nette\Http\FileUpload;
use WebChemistry\Images\Batches\IBatch;
use WebChemistry\Images\Batches\ImageBatch;
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

	public function createBatch(): IBatch {
		return new ImageBatch($this);
	}

	// abstracts

	/**
	 * @param IFileResource $resource
	 * @return ImageSize
	 */
	abstract function getImageSize(IFileResource $resource): ImageSize;

	/**
	 * {@inheritdoc}
	 */
	abstract public function link(?IFileResource $resource): ?string;

	/**
	 * {@inheritdoc}
	 */
	abstract public function save(IResource $resource): IFileResource;

	/**
	 * @param IFileResource $src
	 * @param IFileResource $dest
	 * @return IFileResource
	 */
	abstract public function copy(IFileResource $src, IFileResource $dest): IFileResource;

	/**
	 * @param IFileResource $src
	 * @param IFileResource $dest
	 * @return IFileResource
	 */
	abstract public function move(IFileResource $src, IFileResource $dest): IFileResource;

	/**
	 * @param IFileResource $resource
	 * @return IFileResource
	 */
	abstract public function delete(IFileResource $resource): IFileResource;

}
