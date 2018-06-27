<?php declare(strict_types = 1);

namespace WebChemistry\Images\Modifiers;


interface ILoader {

	/**
	 * @param ModifierContainer $modifierContainer
	 */
	public function load(ModifierContainer $modifierContainer);

}
