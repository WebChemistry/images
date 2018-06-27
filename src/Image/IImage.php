<?php declare(strict_types = 1);

namespace WebChemistry\Images\Image;


interface IImage {

	/**
	 * @param int $quality
	 * @return static
	 */
	public function setQuality(int $quality = 80);

}
