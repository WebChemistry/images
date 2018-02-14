<?php

namespace WebChemistry\Images\Image;


use Nette\Utils\Image;

interface IImageFactory {

	/**
	 * @param string $file
	 * @param mixed $format
	 * @return IImage|Image
	 */
	public function createFromFile($file, &$format = null);

	/**
	 * @param string $string
	 * @param mixed $format
	 * @return IImage|Image
	 */
	public function createFromString($string, &$format = null);

}
