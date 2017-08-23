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
			$_res = $_imageFacade->create(%node.args);' .
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

}

