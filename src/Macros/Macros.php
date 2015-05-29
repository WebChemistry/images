<?php

namespace WebChemistry\Images\Macros;

use Latte;

class Macros extends Latte\Macros\MacroSet {

	/**
	 * @param Latte\Compiler $compiler
	 */
	public static function install(Latte\Compiler $compiler) {
		$me = new static($compiler);

		$me->addMacro('img', [$me, 'beginImg'], NULL, [$me, 'attrImg']);
		$me->addMacro('imgLink', [$me, 'beginLink'], NULL, [$me, 'attrLink']);
	}

	/**
	 * @param Latte\MacroNode $node
	 * @param Latte\PhpWriter $writer
	 * @return string
	 */
	public function beginLink(Latte\MacroNode $node, Latte\PhpWriter $writer) {
		return $writer->write('echo %escape($_control->link(":ImageStorage:Generate:", %node.array));');
	}

	/**
	 * @param Latte\MacroNode $node
	 * @param Latte\PhpWriter $writer
	 * @return string
	 */
	public function attrLink(Latte\MacroNode $node, Latte\PhpWriter $writer) {
		if ($node->htmlNode->name === 'a') {
			$attr = 'href=';
		} else {
			$attr = 'src=';
		}

		return $writer->write('echo \' ' . $attr . '"\' . %escape($_control->link(":ImageStorage:Generate:", %node.array)) . \'"\'');
	}

	/**
	 * @param Latte\MacroNode $node
	 * @param Latte\PhpWriter $writer
	 * @return string
	 */
	public function beginImg(Latte\MacroNode $node, Latte\PhpWriter $writer) {
		return $writer->write('$__image = $imageStorage->get(%node.args); echo ($__image->isBaseUri() ? $baseUri : $basePath) . "/" . $__image->getLink();');
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

		return $writer->write('$__image = $imageStorage->get(%node.args);echo \' ' . $attr . '"\' . ($__image->isBaseUri() ? $baseUri : $basePath) . "/" . $__image->getLink() . \'"\'');
	}
}
