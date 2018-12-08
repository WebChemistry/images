<?php declare(strict_types = 1);

namespace Test;

use Latte\Engine;

class TemplateMock {

	/** @var Engine */
	private $engine;

	/** @var array */
	private $params = [];

	public function __construct(Engine $engine) {
		$this->engine = $engine;
	}

	public function compile($name) {
		return $this->engine->compile($name);
	}

	public function renderToString($name, array $params = []) {
		return $this->engine->renderToString($name, $this->params + $params);
	}

	public function __set($name, $value) {
		$this->params[$name] = $value;
	}

	/**
	 * @return Engine
	 */
	public function getEngine() {
		return $this->engine;
	}

}
