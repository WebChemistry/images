<?php

namespace WebChemistry\Images\Image;


interface IImage {

	/**
	 * @param int $quality
	 * @return static
	 */
	public function setQuality($quality = 80);

}