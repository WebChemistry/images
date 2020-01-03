<?php declare(strict_types = 1);

namespace WebChemistry\Images;

use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;

interface IUnitOfWork {

	public function save(IResource $resource): IFileResource;

	public function copy(IFileResource $src, IFileResource $dest): IFileResource;

	public function move(IFileResource $src, IFileResource $dest): IFileResource;

	public function delete(IFileResource $resource): IFileResource;

}
