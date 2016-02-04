<?php

namespace WebChemistry\Images\Helpers;

use Nette\Utils\Image;

interface IHelper {

	/**
	 * @param Image  $image
	 * @param array $parameters
	 */
	public function invoke(Image $image, array $parameters);

}
