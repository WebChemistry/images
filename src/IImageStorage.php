<?php declare(strict_types = 1);

namespace WebChemistry\Images;

use WebChemistry\Images\Batches\IBatch;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;

interface IImageStorage extends IUnitOfWork {

	/**
	 * @param IFileResource|null $resource
	 * @return string|null
	 */
	public function link(?IFileResource $resource): ?string;

	/**
	 * @param string $id
	 * @return IFileResource
	 */
	public function createResource(string $id): IFileResource;

	public function createBatch(): IBatch;

}
