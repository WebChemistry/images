<?php

namespace WebChemistry\Images\Helpers;

use WebChemistry\Images\Bridges\Nette\Image;

class Quality implements IHelper {

	/**
	 * @param Image  $image
	 * @param string $parameter
	 */
	public function invoke(Image &$image, $parameter) {
		$image->setQuality($parameter);
	}
}