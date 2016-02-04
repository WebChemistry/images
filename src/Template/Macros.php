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
		return $writer->write('echo $imageStorage->get(%node.args)->getLink();');
	}

	/**
	 * @param Latte\MacroNode $node
	 * @param Latte\PhpWriter $writer
	 * @return string
	 */
	public function attrImg(Latte\MacroNode $node, Latte\PhpWriter $writer) {
		if ($node->htmlNode->name === 'a') {
			$attr = 'href=';
		} else {
			$attr = 'src=';
		}

		return $writer->write('echo \' ' . $attr . '"\' . $imageStorage->get(%node.args)->getLink() . \'"\'');
	}

}

