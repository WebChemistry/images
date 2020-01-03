<?php declare(strict_types = 1);

namespace WebChemistry\Images\Transactions;

use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;

interface ITransaction extends ITransactional {

	public function isClosed(): bool;

	public function isPersisted(): bool;

	public function save(ITransferResource $resource): IFileResource;

	public function copy(IFileResource $src, IFileResource $dest): IFileResource;

	public function move(IFileResource $src, IFileResource $dest): IFileResource;

}
