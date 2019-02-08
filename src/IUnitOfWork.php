<?php declare(strict_types = 1);

namespace WebChemistry\Images;

use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;

interface IUnitOfWork {

	/**
	 * @param IResource $resource
	 * @return IFileResource
	 */
	public function save(IResource $resource): IFileResource;

	/**
	 * @param IFileResource $src
	 * @param IFileResource $dest
	 * @return IFileResource
	 */
	public function copy(IFileResource $src, IFileResource $dest): IFileResource;

	/**
	 * @param IFileResource $src
	 * @param IFileResource $dest
	 * @return IFileResource
	 */
	public function move(IFileResource $src, IFileResource $dest): IFileResource;

	/**
	 * @param IFileResource $resource
	 * @return IFileResource
	 */
	public function delete(IFileResource $resource): IFileResource;

}
