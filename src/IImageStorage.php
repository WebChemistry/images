<?php declare(strict_types = 1);

namespace WebChemistry\Images;

use WebChemistry\Images\Transactions\ITransaction;
use WebChemistry\Images\Resources\IFileResource;

interface IImageStorage extends IUnitOfWork {

	public function link(?IFileResource $resource, array $options = []): ?string;

	public function createResource(string $id): IFileResource;

	public function createTransaction(): ITransaction;

}
