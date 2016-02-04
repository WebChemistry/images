<?php

namespace WebChemistry\Images\Helpers;

use Nette\Utils\Image;

class Crop implements IHelper {

	/**
	 * @param Image $image
	 * @param array $parameters
	 */
	public function invoke(Image $image, array $parameters) {
		call_user_func_array([$image, 'crop'], $parameters);
	}

}
