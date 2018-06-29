<?php declare(strict_types = 1);

namespace WebChemistry\Images\Parsers;

use WebChemistry\Images\Parsers\Tokenizers\ModifierTokenizer;
use WebChemistry\Images\Parsers\Tokenizers\Token;

class ModifierParser {

	/** @var array */
	private static $convert = ['null' => null, 'NULL' => null];

	/** @var ModifierTokenizer */
	private static $tokenizer;

	/** @var ValueBuilder */
	private static $valueBuilder;

	protected static function convertValue(Token $token) {
		if ($token->type === Token::VARIABLE) {
			return new Variable($token->token);
		}

		return array_key_exists($token->token, self::$convert) ? self::$convert[$token->token] : $token->token;
	}

	public static function parse(string $input): Values {
		self::$tokenizer = new ModifierTokenizer($input);

		self::$valueBuilder = new ValueBuilder();
		self::modifier();

		return self::$valueBuilder->getResult();
	}

	/**
	 * @param Token $token
	 * @param int $expected
	 * @throws ParserException
	 */
	protected static function checkToken(Token $token, int $expected): void {
		if ($token->type !== $expected) {
			ParserException::typeError($expected, $token->type);
		}
	}

	/**
	 * <id>: <expr>
	 * €
	 * @throws ParserException
	 */
	protected static function modifier(): void {
		while ($token = self::$tokenizer->nextToken()) {
			if ($token->type !== $token::VALUE) {
				ParserException::typeError($token::VALUE, $token->type);
			}

			self::$valueBuilder->addKey($token->token)
				->setActive($token->token);
			$token = self::$tokenizer->nextToken();
			if ($token === null) {
				break;
			}
			if ($token->type === Token::PIPE) { // next modifier
				self::$valueBuilder->pop();
				continue;
			}
			self::checkToken($token, Token::COLON);

			self::expression();

			self::$valueBuilder->pop();
		}
	}

	/**
	 * <param>[, <param>...]
	 * [<id>: <expr>]
	 * €
	 */
	protected static function expression(): void {
		$isFirst = true;
		while ($token = self::$tokenizer->nextToken()) {
			if (!$isFirst) {
				if ($token->type === Token::PIPE) {
					return;
				}
				self::checkToken($token, Token::COMMA);
				$token = self::$tokenizer->nextToken();

				if ($token === null) {
					throw new ParserException('Expected left bracket or value, null given.');
				}
			}

			if ($token->type === Token::BRACKET_LEFT) {
				$key = self::$valueBuilder->addDefaultKey();
				self::$valueBuilder->setActive($key);

				self::arr();

				self::$valueBuilder->pop();

				$isFirst = false;

				continue;
			}

			if ($token->type !== Token::VALUE && $token->type !== Token::VARIABLE) {
				throw new ParserException('Expected left bracket or value, ' . ParserException::convertType($token->type) . ' given.');
			}

			$key = self::$valueBuilder->addDefaultKey();
			self::$valueBuilder->setValue($key, self::convertValue($token));

			$isFirst = false;
		}
	}

	/**
	 * [<id>: <paramOrArray>]
	 */
	protected static function arr(): void {
		$isFirst = true;
		while (($token = self::$tokenizer->nextToken()) && $token->type !== Token::BRACKET_RIGHT) {
			if (!$isFirst) {
				self::checkToken($token, Token::COMMA);
				$token = self::$tokenizer->nextToken();

				if ($token === null) {
					new ParserException('Expected left bracket or value, null given.');
				}
			}
			self::checkToken($token, Token::VALUE);
			$key = $token->token;
			self::$valueBuilder->addKey($key);

			self::checkToken(self::$tokenizer->nextToken(), Token::COLON);

			$token = self::$tokenizer->nextToken();
			if ($token === null) {
				throw new ParserException('Unexpected end.');
			}

			if ($token->type === Token::BRACKET_LEFT) {
				self::$valueBuilder->setActive($key);

				self::arr();

				self::$valueBuilder->pop();

				$isFirst = false;

				continue;
			}
			if ($token->type !== Token::VALUE && $token->type !== Token::VARIABLE) {
				throw new ParserException('Expected left bracket or value, ' . ParserException::convertType($token->type) . ' given.');
			}

			self::$valueBuilder->setValue($key, self::convertValue($token));
			$isFirst = false;
		}
	}

}
