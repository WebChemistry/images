<?php declare(strict_types = 1);

namespace Test;

class StateRow {

	/** @var array */
	protected $params = [];

	/** @var array */
	protected $expected = [];

	/** @var string */
	protected $description;

	public function __construct(string $description) {
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function __getDescription(): string {
		return $this->description;
	}

	public function params(...$params) {
		$this->params = $params;

		return $this;
	}

	public function expect(...$params) {
		$this->expected = $params;

		return $this;
	}

	public function __invoke(): array {
		return [$this->params, $this->expected];
	}

}
