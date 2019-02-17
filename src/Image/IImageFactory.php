<?php declare(strict_types = 1);

namespace WebChemistry\Images\Image;

interface IImageFactory {

	/**
	 * @param string $file
	 * @param int|null $format
	 * @return Image
	 */
	public function createFromFile(string $file, int &$format = null);

	/**
	 * @param string $string
	 * @param int|null $format
	 * @return Image
	 */
	public function createFromString(string $string, int &$format = null);

	/**
	 * @param resource $resource
	 * @return Image
	 */
	public function createFromResource($resource);

}
