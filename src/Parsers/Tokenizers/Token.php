<?php

namespace WebChemistry\Images\Parsers\Tokenizers;


class Token {

	const VALUE = 0, PIPE = 1, COLON = 2, COMMA = 3, BRACKET_LEFT = 4, BRACKET_RIGHT = 5;

	/** @var string */
	public $token;

	/** @var int */
	public $type;

	/**
	 * @param string $token
	 * @param int $type
	 */
	public function __construct($token, $type) {
		$this->token = $token;
		$this->type = $type;
	}

}
