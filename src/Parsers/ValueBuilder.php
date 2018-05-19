<?php

namespace WebChemistry\Images\Parsers;

/**
 * @internal
 */
class ValueBuilder {

	/** @var array */
	protected $values = [];

	/** @var array */
	protected $variables = [];

	/** @var array reference */
	protected $current;

	/** @var array */
	protected $referenceStack = [];

	/** @var array */
	protected $currentPath = [];

	public function __construct() {
		$this->current = 0;
		$this->referenceStack = [&$this->values];
	}

	public function setValue($key, $value) {
		if ($value instanceof Variable) {
			$position = $value->getPosition() - 1;
			$path = implode('.', $this->currentPath);
			$path = $path ? $path . '.' . $key : $key;
			if (isset($this->variables[$position])) {
				$this->variables[$position][] = $path;
			} else {
				$this->variables[$position] = [$path];
			}

			$this->referenceStack[$this->current][$key] = null;
			return;
		}

		$this->referenceStack[$this->current][$key] = $value;
	}

	public function addKey($key) {
		$this->referenceStack[$this->current][$key] = [];

		return $this;
	}

	public function addDefaultKey() {
		$this->referenceStack[$this->current][] = [];
		end($this->referenceStack[$this->current]);

		return key($this->referenceStack[$this->current]);
	}

	public function pop() {
		if (!$this->referenceStack) {
			throw new \LogicException('Cannot pop.');
		}

		$this->current--;
		array_pop($this->referenceStack);
		array_pop($this->currentPath);
	}

	public function setActive($key) {
		if (!isset($this->referenceStack[$this->current][$key])) {
			throw new \LogicException("Key '$key' not exists in array.");
		}

		$this->referenceStack[] = &$this->referenceStack[$this->current][$key];
		$this->currentPath[] = $key;
		$this->current++;

		return $this;
	}

	public function getResult() {
		return new Values($this->values, $this->variables);
	}

}
