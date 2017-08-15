<?php

declare(strict_types=1);

namespace WebChemistry\Images\Controls;

trait TUploadControl {

	/**
	 * @param string $name
	 * @param string|null $label
	 * @param string|null $namespace
	 * @return UploadControl
	 */
	public function addImageUpload($name, $label = NULL, $namespace = NULL) {
		return $this[$name] = new UploadControl($label, $namespace);
	}

}
