<?php

namespace WebChemistry\Images\Helpers;

use Nette\Utils\Image;

class Sharpen implements IHelper{

	/**
	 * @param Image  $image
	 * @param array $parameters
	 */
	public function invoke(Image $image, array $parameters) {
		$image->sharpen();
	}

}
