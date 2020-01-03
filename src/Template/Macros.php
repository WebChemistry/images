<?php

namespace WebChemistry\Images\Template;

use Latte;
use Latte\CompileException;
use Latte\MacroTokens;

class Macros extends Latte\Macros\MacroSet {

	/**
	 * @param Latte\Compiler $compiler
	 */
	public static function install(Latte\Compiler $compiler) {
		$me = new static($compiler);

		$me->addMacro('img', [$me, 'beginImg'], null, [$me, 'attrImg']);
	}

	protected function parseId(Latte\MacroTokens $tokens): string {
		$id = '';
		$counter = 0;
		while ($tokens->nextToken()) {
			if ($tokens->isCurrent(',') && $counter === 0) {
				break;
			}
			if ($tokens->isCurrent('(')) {
				$counter++;
			} else if ($tokens->isCurrent(')')) {
				$counter--;
			}

			$id .= $tokens->currentValue();
		}

		return $id;
	}

	protected function parseFilter(Latte\MacroTokens $tokens, Latte\PhpWriter $writer) {
		$string = '[';
		$stack = [];
		while ($tokens->nextToken()) {
			if ($tokens->isCurrent($tokens::T_WHITESPACE)) {
				continue;
			}

			if ($tokens->isCurrent(',') || $tokens->isCurrent('(')) {
				$name = $writer->formatArgs(new Latte\MacroTokens($stack));
				if ($tokens->isCurrent('(')) {
					$args = '[' . $writer->formatArgs(new Latte\MacroTokens($tokens->joinUntil(')'))) . ']';
					$tokens->nextToken();
				} else {
					$args = '[]';
				}

				$string .= $name . ' => ' . $args . ', ';
				$stack = [];
			} else {
				$stack[] = $tokens->currentToken();
			}
		}
		if ($stack) {
			$string .= $writer->formatArgs(new Latte\MacroTokens($stack)) . ' => []';
		}

		return $string . ']';
	}

	protected function parseModifier(Latte\MacroTokens $tokens, Latte\PhpWriter $writer) {
		$inside = false;
		$string = '[';
		$args = [];

		while ($tokens->nextToken()) {
			if ($tokens->isCurrent($tokens::T_WHITESPACE)) {
				continue;

			} elseif ($inside) {
				if ($tokens->isCurrent(':')) {
					continue;
				} else if ($tokens->isCurrent(',')) {
					$args[] = $tokens->currentToken();
					$tokens->nextAll($tokens::T_WHITESPACE);

				} elseif ($tokens->isCurrent('|')) {
					$string .= $writer->quotingPass(new MacroTokens($args))->joinAll() . '],';
					$inside = false;

				} else {
					$args[] = $tokens->currentToken();
				}
			} else {
				if ($tokens->isCurrent($tokens::T_SYMBOL)) {
					$name = strtolower($tokens->currentValue());
					$string .= "'$name' => [";
					$args = [];

					$inside = true;
				} else {
					throw new CompileException("Modifier name must be alphanumeric string, '{$tokens->currentValue()}' given.");
				}
			}
		}
		if ($inside) {
			$string .= $writer->quotingPass(new MacroTokens($args))->joinAll() . ']';
		}

		return $string . ']';
	}

	/**
	 * @param Latte\MacroNode $node
	 * @param Latte\PhpWriter $writer
	 * @return string
	 */
	public function beginImg(Latte\MacroNode $node, Latte\PhpWriter $writer) {
		$tokenizer = $node->tokenizer;
		$tokens = new Latte\MacroTokens($tokenizer->nextUntil('|'));
		$id = $this->parseId($tokens);
		$filters = $this->parseFilter($tokens, $writer);

		if ($tokenizer->isNext('|')) {
			$tokenizer->nextToken();
		}

		$modifiers = $this->parseModifier(new MacroTokens($tokenizer->nextUntil()), $writer);

		return $writer->write(
			'echo ' .
			'$this->global->imageStorageFacade->link(' .
			'$this->global->imageStorageFacade->create(%raw, %raw)' .
			', %raw);',
			$id, $filters, $modifiers
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
