<?php declare(strict_types = 1);

namespace WebChemistry\Images\Modifiers;

interface ILoader {

	public function load(IModifiers $modifiers): void;

}
