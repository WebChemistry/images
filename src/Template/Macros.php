<?php

namespace WebChemistry\Images\Template;

use Latte;

class Macros extends Latte\Macros\MacroSet {

	/**
	 * @param Latte\Compiler $compiler
	 */
	public static function install(Latte\Compiler $compiler) {
		$me = new static($compiler);

		$me->addMacro('img', [$me, 'beginImg'], null, [$me, 'attrImg']);
	}

	protected function skipWhitespaces(Latte\MacroTokens $tokenizer) {
		while (($tokens = $tokenizer->currentToken()) && $tokens[2] === $tokenizer::T_WHITESPACE) {
			$tokenizer->nextToken();
		}
	}

	/**
	 * @param Latte\MacroTokens $tokenizer
	 * @return array
	 */
	protected function parseArguments(Latte\MacroTokens $tokenizer) {
		$args = [];
		$tokenizer->nextToken();

		while (($tokens = $tokenizer->currentToken()) && $tokens[0] !== ')') {
			$this->skipWhitespaces($tokenizer);
			list($value,,$type) = $tokenizer->currentToken();
			if ($value === ',') {
				throw new \LogicException("Expected value, given '$value'.");
			}

			$args[] = trim($value, "'\"");
			$tokenizer->nextToken();
			$this->skipWhitespaces($tokenizer);
			list($value,,$type) = $tokenizer->currentToken();
			if ($value === ',') {
				$tokenizer->nextToken();
			}
		}

		return $args;
	}

	/**
	 * @param Latte\MacroTokens $tokenizer
	 * @return array
	 */
	protected function parseAliases(Latte\MacroTokens $tokenizer) {
		$aliases = [];

		while ($token = $tokenizer->nextToken()) {
			$this->skipWhitespaces($tokenizer);

			list($key,,$type) = $tokenizer->currentToken();

			$aliases[$key] = [];
			if ($type !== $tokenizer::T_SYMBOL) {
				throw new \LogicException("Alias must starts with identifier, given '$key'");
			}

			$this->skipWhitespaces($tokenizer);

			if (!$tokenizer->isNext()) {
				break;
			}
			$this->skipWhitespaces($tokenizer);
			list($value,,$type) = $tokenizer->nextToken();
			if ($value === '(') {
				$aliases[$key] = $this->parseArguments($tokenizer);
				if (!$tokenizer->isNext()) {
					break;
				}
				list($value,,$type) = $tokenizer->nextToken();
			}
			if ($value !== ',') {
				throw new \LogicException("Next alias must continue with dash, given '$value'.");
			}
		}

		return $aliases;
	}


	/**
	 * @param Latte\MacroNode $node
	 * @param Latte\PhpWriter $writer
	 * @return string
	 */
	public function beginImg(Latte\MacroNode $node, Latte\PhpWriter $writer) {
		$tokenizer = $node->tokenizer;
		$imageName = $tokenizer->fetchWord();
		$aliases = $this->parseAliases($tokenizer);

		return $writer->write('
			$_res = $this->global->imageStorageFacade->create(%word, %var);' .
			'echo $this->global->imageStorageFacade->link($_res);',
		$imageName,
		$aliases
		);
	}

	/**
	 * @param Latte\MacroNode $node
	 * @param Latte\PhpWriter $writer
	 * @return string
	 */
	public function attrImg(Latte\MacroNode $node, Latte\PhpWriter $writer) {
		return $writer->write(
			'echo " " . %word . "\""; %raw echo "\"";',
			$node->htmlNode->name === 'a' ? 'href=' : 'src=',
			$this->beginImg($node, $writer)
		);
	}

}
