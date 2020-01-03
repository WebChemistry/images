<?php declare(strict_types = 1);

namespace WebChemistry\Images\Image;

interface IImage {

	/**
	 * @return static
	 */
	public function setQuality(?int $quality);

}
