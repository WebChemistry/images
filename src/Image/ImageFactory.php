<?php declare(strict_types = 1);

namespace WebChemistry\Images\Image;

use Nette\Utils\UnknownImageFileException;
use Nette\Utils\ImageException;

class ImageFactory implements IImageFactory {

	/**
	 * @param string $file
	 * @param int|null $format
	 * @return Image
	 * @throws UnknownImageFileException
	 */
	public function createFromFile(string $file, int &$format = null): Image {
		return Image::fromFile($file, $format);
	}

	/**
	 * @param string $string
	 * @param int|null $format
	 * @return Image
	 * @throws ImageException
	 */
	public function createFromString(string $string, int &$format = null): Image {
		return Image::fromString($string, $format);
	}

	/**
	 * @param resource $resource
	 * @return Image
	 */
	public function createFromResource($resource): Image {
		return new Image($resource);
	}

}
