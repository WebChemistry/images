<?php declare(strict_types = 1);

namespace WebChemistry\Images\Image;

use Nette\Utils\UnknownImageFileException;
use Nette\Utils\ImageException;

class ImageFactory implements IImageFactory {

	/**
	 * @param string $file
	 * @param mixed $format
	 * @return Image
	 * @throws UnknownImageFileException
	 */
	public function createFromFile(string $file, &$format = null) {
		return Image::fromFile($file, $format);
	}

	/**
	 * @param string $string
	 * @param mixed $format
	 * @return Image
	 * @throws ImageException
	 */
	public function createFromString(string $string, &$format = null) {
		return Image::fromString($string, $format);
	}

}
