<?php declare(strict_types = 1);

namespace WebChemistry\Images\Image;

interface IImageFactory {

	/**
	 * @param string $file
	 * @param mixed $format
	 * @return Image
	 */
	public function createFromFile(string $file, &$format = null);

	/**
	 * @param string $string
	 * @param mixed $format
	 * @return Image
	 */
	public function createFromString(string $string, &$format = null);

}
