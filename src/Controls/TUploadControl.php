<?php

namespace WebChemistry\Images\Controls;

trait TUploadControl {

	/**
	 * @param string $name
	 * @param string|null $label
	 * @param string|null $namespace
	 * @return UploadControl
	 */
	public function addImageUpload($name, $label = null, $namespace = null) {
		return $this[$name] = new UploadControl($label, $namespace);
	}

}
