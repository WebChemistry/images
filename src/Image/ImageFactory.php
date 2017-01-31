<?php

namespace WebChemistry\Images\Image;


class ImageFactory implements IImageFactory {

	/**
	 * @param string $file
	 * @param mixed $format
	 * @return Image
	 */
	public function createFromFile($file, &$format = NULL) {
		return Image::fromFile($file, $format);
	}


	/**
	 * @param string $string
	 * @param mixed $format
	 * @return Image
	 */
	public function createFromString($string, &$format = NULL) {
		return Image::fromString($string, $format);
	}

}