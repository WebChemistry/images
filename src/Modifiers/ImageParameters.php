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

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function addParameter($name, $value) {
		$this->parameters[$name] = $value;
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

	/**
	 * @param string $name
	 * @return mixed|null
	 */
	public function getParameter($name) {
		return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
	}

}
