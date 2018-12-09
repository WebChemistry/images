<?php declare(strict_types = 1);

namespace WebChemistry\Images\Controls;

trait TUploadControl {

	public function addImageUpload(string $name, ?string $label = null, ?string $namespace = null): UploadControl {
		return $this[$name] = new UploadControl($label, $namespace);
	}

	public function addImagePreviewUpload(string $name, ?string $label = null, ?string $namespace = null): AdvancedUploadControl {
		return $this[$name] = new AdvancedUploadControl($label, $namespace);
	}

}
