<?php

namespace WebChemistry\Images\Helpers;

use WebChemistry\Images\Bridges\Nette\Image;

interface IHelper {

	/**
	 * @param Image  $image
	 * @param string $parameter
	 * @return mixed
	 */
	public function invoke(Image &$image, $parameter);
}