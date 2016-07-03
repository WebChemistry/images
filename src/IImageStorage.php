<?php

namespace WebChemistry\Images;

use Nette\Http\FileUpload;
use Nette\Utils\Image;
use WebChemistry\Images\Image\PropertyAccess;

interface IImageStorage {

	/**
	 * @return PropertyAccess
	 */
	public function createImage();

	/**
	 * @param string $absoluteName
	 * @param string $size
	 * @param string|int $flag
	 * @param string $defaultImage
	 * @param callable $callback
	 * @return PropertyAccess
	 */
	public function get($absoluteName, $size = NULL, $flag = NULL, $defaultImage = NULL, $callback = NULL);

	/**
	 * @param FileUpload $fileUpload
	 * @param string $namespace
	 * @param callable $callback
	 * @return string Absolute name
	 */
	public function saveUpload(FileUpload $fileUpload, $namespace = NULL, $callback = NULL);

	/**
	 * @param Image $image
	 * @param string $fileName
	 * @param string $namespace
	 * @param callable $callback
	 * @return string AbsoluteName
	 */
	public function saveImage(Image $image, $fileName, $namespace = NULL, $callback = NULL);

	/**
	 * @param string $absoluteName
	 * @return bool
	 */
	public function delete($absoluteName);

}
