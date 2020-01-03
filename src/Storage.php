<?php declare(strict_types = 1);

namespace WebChemistry\Images;

use Nette\Http\FileUpload;
use WebChemistry\Images\Transactions\ITransaction;
use WebChemistry\Images\Transactions\Transaction;
use WebChemistry\Images\Resources\FileResource;
use WebChemistry\Images\Resources\IFileResource;
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

	public function createTransaction(): ITransaction {
		return new Transaction($this);
	}

}
