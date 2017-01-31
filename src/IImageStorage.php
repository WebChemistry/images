<?php

namespace WebChemistry\Images;


use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;

interface IImageStorage {

	/**
	 * @param IFileResource $resource
	 * @return string
	 */
	public function link(IFileResource $resource);

	/**
	 * @param IResource $resource
	 * @return mixed
	 */
	public function save(IResource $resource);

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
	public function createResource($id);

}
