<?php declare(strict_types = 1);

namespace WebChemistry\Images\Image;

interface IImage {

	/**
	 * @param int|null $quality
	 * @return static
	 */
	public function setQuality(?int $quality);

}
