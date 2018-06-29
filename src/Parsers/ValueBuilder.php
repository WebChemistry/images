<?php declare(strict_types = 1);

namespace WebChemistry\Images\Parsers;

/**
 * @internal
 */
class ValueBuilder {

	/** @var array */
	protected $values = [];

	/** @var array */
	protected $variables = [];

	/** @var int */
	protected $current;

	/** @var array */
	protected $referenceStack = [];

	/** @var array */
	protected $currentPath = [];

	public function __construct() {
		$this->current = 0;
		$this->referenceStack = [&$this->values];
	}

	/**
	 * @param string|int $key
	 * @param mixed $value
	 */
	public function setValue($key, $value): void {
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

	/**
	 * @param int|string $key
	 * @return static
	 */
	public function addKey($key) {
		$this->referenceStack[$this->current][$key] = [];

		return $this;
	}

	public function addDefaultKey(): int {
		$this->referenceStack[$this->current][] = [];
		end($this->referenceStack[$this->current]);

		return (int) key($this->referenceStack[$this->current]);
	}

	public function pop(): void {
		if (!$this->referenceStack) {
			throw new \LogicException('Cannot pop.');
		}

		$this->current--;
		array_pop($this->referenceStack);
		array_pop($this->currentPath);
	}

	/**
	 * @param string|int $key
	 * @return static
	 */
	public function setActive($key) {
		if (!isset($this->referenceStack[$this->current][$key])) {
			throw new \LogicException("Key '$key' not exists in array.");
		}

		$this->referenceStack[] = &$this->referenceStack[$this->current][$key];
		$this->currentPath[] = $key;
		$this->current++;

		return $this;
	}

	public function getResult(): Values {
		return new Values($this->values, $this->variables);
	}

}
