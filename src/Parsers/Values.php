<?php

namespace WebChemistry\Images\Parsers;

class Values {

	/** @var array */
	private $values = [];

	/** @var array */
	private $variables = [];

	public function __construct(array $values, array $variables) {
		$this->values = $values;
		$this->variables = $variables;
	}

	/**
	 * @return array
	 */
	public function getValues() {
		return $this->values;
	}

	/**
	 * @return array
	 */
	public function getVariables() {
		return $this->variables;
	}

	/**
	 * @param array $args
	 * @return array
	 */
	public function call(array $args) {
		if (count($args) !== count($this->variables)) {
			throw new \LogicException('Argument count is not equal.');
		}
		if (!$args) {
			return $this->values;
		}

		$values = $this->values;
		foreach ($args as $pos => $value) {
			if (!isset($this->variables[$pos])) {
				throw new \LogicException("Too many arguments.");
			}

			foreach ($this->variables[$pos] as $index) {
				$index = explode('.', $index);
				$end = end($index);
				array_pop($index);
				$current = &$values;
				foreach ($index as $row) {
					$current = &$values[$row];
				}
				$current[$end] = $args[$pos];
			}
		}

		return $values;
	}

}
