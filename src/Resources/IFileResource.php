<?php

namespace WebChemistry\Images\Resources;

interface IFileResource extends IResource {

	/**
	 * @return IFileResource
	 */
	public function getOriginal();

	/**
	 * @param string $defaultImage
	 */
	public function setDefaultImage($defaultImage);

	/**
	 * @return string
	 */
	public function getDefaultImage();

	/**
	 * @param bool $baseUri
	 */
	public function setBaseUri($baseUri = TRUE);

	/**
	 * @return bool
	 */
	public function isBaseUri();

}
