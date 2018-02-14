<?php

namespace WebChemistry\Images\Image;


class ImageFactory implements IImageFactory {

	/**
	 * @param string $file
	 * @param mixed $format
	 * @return Image
	 */
	public function createFromFile($file, &$format = null) {
		return Image::fromFile($file, $format);
	}


	/**
	 * @param string $string
	 * @param mixed $format
	 * @return Image
	 */
	public function createFromString($string, &$format = null) {
		return Image::fromString($string, $format);
	}

}
