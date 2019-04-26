<?php declare(strict_types = 1);

namespace WebChemistry\Images\Batches;

use WebChemistry\Images\IUnitOfWork;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;

interface IBatch extends IUnitOfWork {

	public function flush(): void;

	public function save(IResource $resource): IFileResource;

	public function copy(IFileResource $src, IFileResource $dest): IFileResource;

	public function move(IFileResource $src, IFileResource $dest): IFileResource;

	public function delete(IFileResource $resource): IFileResource;

	public function addBatch(IBatch $batch): IBatch;

}
