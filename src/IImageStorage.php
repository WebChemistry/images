<?php declare(strict_types = 1);

namespace WebChemistry\Images;

use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;

interface IImageStorage {

	/**
	 * @param IFileResource $resource
	 * @return string|null
	 */
	public function link(IFileResource $resource): ?string;

	/**
	 * @param IResource $resource
	 * @return IFileResource
	 */
	public function save(IResource $resource): IFileResource;

	/**
	 * @param IFileResource $src
	 * @param IFileResource $dest
	 */
	public function copy(IFileResource $src, IFileResource $dest);

	/**
	 * @param IFileResource $src
	 * @param IFileResource $dest
	 */
	public function move(IFileResource $src, IFileResource $dest);

	/**
	 * @param IFileResource $resource
	 */
	public function delete(IFileResource $resource);

	/**
	 * @param string $id
	 * @return IFileResource
	 */
	public function createResource(string $id): IFileResource;

}
