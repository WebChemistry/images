<?php declare(strict_types = 1);

namespace WebChemistry\Images\Parsers;

/**
 * @internal 
 */
class Variable {

	/** @var int */
	private $position;

	public function __construct(string $position) {
		$position = substr($position, 1);
		if (!filter_var($position, FILTER_VALIDATE_INT, ['min_range' => 1])) {
			throw new ParserException("Given position '$position' is not valid.");
		}

		$this->position = (int) $position;
	}

	public function getPosition(): int {
		return $this->position;
	}

}
