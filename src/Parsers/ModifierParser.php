<?php

namespace WebChemistry\Images\Parsers;


use WebChemistry\Images\Parsers\Tokenizers\ModifierTokenizer;
use WebChemistry\Images\Parsers\Tokenizers\Token;

class ModifierParser {

	/** @var ModifierTokenizer */
	private static $tokenizer;

	/** @var array */
	private static $values;

	/** @var mixed reference to values */
	private static $active;

	public static function parse($input) {
		self::$tokenizer = new ModifierTokenizer($input);

		self::$values = [];
		self::$active = &self::$values;
		self::modifier();

		return self::$values;
	}

	protected static function checkToken($token, $expected) {
		if (($isNull = $token === NULL) || $token->type !== $expected) {
			ParserException::typeError($expected, $isNull ? NULL : $token->type);
		}
	}

	/**
	 * <id>: <expr>
	 * €
	 */
	protected static function modifier() {
		while ($token = self::$tokenizer->nextToken()) {
			if ($token->type !== $token::VALUE) {
				ParserException::typeError($token::VALUE, $token->type);
			}

			self::$values[$token->token] = [];
			self::$active = &self::$values[$token->token];
			$token = self::$tokenizer->nextToken();
			if ($token === NULL) {
				break;
			}
			if ($token->type === Token::PIPE) { // next modifier
				continue;
			}
			self::checkToken($token, Token::COLON);

			self::expression();
		}
	}

	/**
	 * <param>[, <param>...]
	 * [<id>: <expr>]
	 * €
	 */
	protected static function expression() {
		$isFirst = TRUE;
		while ($token = self::$tokenizer->nextToken()) {
			if (!$isFirst) {
				if ($token->type === Token::PIPE) {
					return;
				}
				self::checkToken($token, Token::COMMA);
				$token = self::$tokenizer->nextToken();

				if ($token === NULL) {
					new ParserException('Expected left bracket or value, NULL given.');
				}
			}

			if ($token->type === Token::BRACKET_LEFT) {
				$store = &self::$active;
				self::arr();
				self::$active = &$store;
				$isFirst = FALSE;

				continue;
			}

			if ($token->type !== Token::VALUE) {
				throw new ParserException('Expected left bracket or value, ' . ParserException::convertType($token->type) . ' given.');
			}

			self::$active[] = $token->token;
			$isFirst = FALSE;
		}
	}

	/**
	 * [<id>: <paramOrArray>]
	 */
	protected static function arr() {
		$isFirst = TRUE;
		while (($token = self::$tokenizer->nextToken()) && $token->type !== Token::BRACKET_RIGHT) {
			if (!$isFirst) {
				self::checkToken($token, Token::COMMA);
				$token = self::$tokenizer->nextToken();

				if ($token === NULL) {
					new ParserException('Expected left bracket or value, NULL given.');
				}
			}
			self::checkToken($token, Token::VALUE);
			$key = $token->token;

			self::checkToken(self::$tokenizer->nextToken(), Token::COLON);

			$token = self::$tokenizer->nextToken();
			if ($token === NULL) {
				throw new ParserException('Unexpected end.');
			}

			if ($token->type === Token::BRACKET_LEFT) {
				$store = &self::$active;
				self::$active[$key] = [];
				self::$active = &self::$active[$key];
				self::arr();
				self::$active = &$store;
				$isFirst = FALSE;

				continue;
			}
			if ($token->type !== Token::VALUE) {
				throw new ParserException('Expected left bracket or value, ' . ParserException::convertType($token->type) . ' given.');
			}

			self::$active[$key] = $token->token;
			$isFirst = FALSE;
		}
	}

}
