<?php declare(strict_types = 1);

namespace WebChemistry\Images\Image;

use Nette\Utils\Image;

interface IImageFactory {

	/**
	 * @param string $file
	 * @param mixed $format
	 * @return IImage|Image
	 */
	public function createFromFile(string $file, &$format = null);

	/**
	 * @param string $string
	 * @param mixed $format
	 * @return IImage|Image
	 */
	public function createFromString(string $string, &$format = null);

}
