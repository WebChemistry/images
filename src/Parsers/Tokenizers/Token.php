<?php declare(strict_types = 1);

namespace WebChemistry\Images\Parsers\Tokenizers;

class Token {

	const VALUE = 0, PIPE = 1, COLON = 2, COMMA = 3, BRACKET_LEFT = 4, BRACKET_RIGHT = 5, VARIABLE = 6;

	/** @var string */
	public $token;

	/** @var int */
	public $type;

	public function __construct(string $token, int $type) {
		$this->token = $token;
		$this->type = $type;
	}

}
