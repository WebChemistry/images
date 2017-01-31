<?php

namespace WebChemistry\Images\Modifiers;


interface ILoader {

	/**
	 * @param ModifierContainer $modifierContainer
	 */
	public function load(ModifierContainer $modifierContainer);

}
