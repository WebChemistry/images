<?php declare(strict_types = 1);

namespace WebChemistry\Images\Modifiers;

final class ImageParameters {

	/** @var array */
	private $parameters = [];

	/** @var string|null */
	private $defaultImage;

	public function setDefaultImage(?string $defaultImage): void {
		$this->defaultImage = $defaultImage;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function addParameter(string $name, $value): void {
		$this->parameters[$name] = $value;
	}

	public function getDefaultImage(): ?string {
		return $this->defaultImage;
	}

	public function getParameters(): array {
		return $this->parameters;
	}

	/**
	 * @param string $name
	 * @return mixed|null
	 */
	public function getParameter(string $name) {
		return $this->parameters[$name] ?? null;
	}

}
