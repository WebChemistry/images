<?php

namespace WebChemistry\Images\Template;


use Latte;

class Macros extends Latte\Macros\MacroSet {

	/**
	 * @param Latte\Compiler $compiler
	 */
	public static function install(Latte\Compiler $compiler) {
		$me = new static($compiler);

		$me->addMacro('img', [$me, 'beginImg'], NULL, [$me, 'attrImg']);
	}

	/**
	 * @param Latte\MacroNode $node
	 * @param Latte\PhpWriter $writer
	 * @return string
	 */
	public function beginImg(Latte\MacroNode $node, Latte\PhpWriter $writer) {
		return $writer->write('
			$_res = ' . $this->modifiersFilter(new Latte\MacroTokens(ltrim($node->modifiers, '|')), '$_imageFacade->create(%node.args)')->joinAll() . ';' .
			'echo $_imageFacade->link($_res);'
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

	// From PhpWriter

    public function modifiersFilter(Latte\MacroTokens $tokens, $var)
    {
        $inside = FALSE;
        $res = new Latte\MacroTokens($var);
        while ($tokens->nextToken()) {
            if ($tokens->isCurrent(Latte\MacroTokens::T_WHITESPACE)) {
                $res->append(' ');

            } elseif ($inside) {
                if ($tokens->isCurrent(':', ',')) {
                    $res->append(', ');
                    $tokens->nextAll(Latte\MacroTokens::T_WHITESPACE);

                } elseif ($tokens->isCurrent('|')) {
                    $res->append(')');
                    $inside = FALSE;

                } else {
                    $res->append($tokens->currentToken());
                }
            } else {
                if ($tokens->isCurrent(Latte\MacroTokens::T_SYMBOL)) {
                    $res->prepend('$_imageFacade->imageModifiers->' . $tokens->currentValue() . '(');
                    $inside = TRUE;
                } else {
                    throw new Latte\CompileException("Modifier name must be alphanumeric string, '{$tokens->currentValue()}' given.");
                }
            }
        }
        if ($inside) {
            $res->append(')');
        }
        return $res;
    }

}

