<?php

namespace WebChemistry\Images\Template;


use WebChemistry\Images\Resources\IFileResource;

interface IImageModifiers {

	/**
	 * @param IFileResource $resource
	 * @return string
	 */
	public function baseUri(IFileResource $resource);

	/**
	 * @param IFileResource $resource
	 * @param string|null $imageId
	 * @return string
	 */
	public function noImage(IFileResource $resource, $imageId);

}