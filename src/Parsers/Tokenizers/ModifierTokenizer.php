<?php declare(strict_types = 1);

namespace WebChemistry\Images\Parsers\Tokenizers;

class ModifierTokenizer {

	/** @var array */
	private static $singles = [
		',' => Token::COMMA, '|' => Token::PIPE, ':' => Token::COLON,'[' => Token::BRACKET_LEFT,
		']' => Token::BRACKET_RIGHT,
	];

	/** @var string */
	private $input;

	/** @var int */
	private $length;

	/** @var int */
	private $index = 0;

	public function __construct(string $input) {
		$this->input = $input;
		$this->length = strlen($input);
	}

	public function nextToken(): ?Token {
		if ($this->index >= $this->length) {
			return null;
		}

		// spaces
		while (ctype_space($this->input[$this->index])) {
			$this->index++;
		}

		foreach (self::$singles as $item => $type) {
			if ($this->input[$this->index] == $item) {
				$this->index++;

				return new Token($item, $type);
			}
		}

		$token = new Token('', Token::VALUE);
		while ($this->index < $this->length) {
			if (isset(self::$singles[$this->input[$this->index]])) {
				break;
			}
			$token->token .= $this->input[$this->index];
			$this->index++;
		}
		if (substr($token->token, 0, 1) === '$') {
			$token->type = Token::VARIABLE;
		}

		return $token;
	}

}
