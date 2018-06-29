<?php declare(strict_types = 1);

namespace WebChemistry\Images\Parsers;

use WebChemistry\Images\Parsers\Tokenizers\Token;

class ParserException extends \Exception {

	public static function convertType(?int $type): string {
		if ($type === null) {
			return 'NULL';
		}
		switch ($type) {
			case Token::VALUE:
				return 'value';
			case Token::COLON:
				return 'colon';
			case Token::BRACKET_LEFT:
				return 'bracket left';
			case Token::BRACKET_RIGHT:
				return 'bracket right';
			case Token::COMMA:
				return 'comma';
			case Token::PIPE:
				return 'pipe';
		}

		throw new ParserException();
	}

	public static function typeError($expected, $given) {
		$expected = self::convertType($expected);
		$given = self::convertType($given);
		throw new ParserException("Expected $expected, given $given.");
	}

}
