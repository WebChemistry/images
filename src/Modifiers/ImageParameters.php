<?php

namespace WebChemistry\Images\Modifiers;

final class ImageParameters {

	/** @var array */
	private $parameters = [];

	/** @var string|null */
	private $defaultImage;

	/**
	 * @param null|string $defaultImage
	 */
	public function setDefaultImage($defaultImage) {
		$this->defaultImage = $defaultImage;
	}

	public function addParameter($name, $values) {
		$this->parameters[$name] = $values;
	}

	/**
	 * @return null|string
	 */
	public function getDefaultImage() {
		return $this->defaultImage;
	}

	/**
	 * @return array
	 */
	public function getParameters() {
		return $this->parameters;
	}

	public function getParameter($name) {
		return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
	}

}
