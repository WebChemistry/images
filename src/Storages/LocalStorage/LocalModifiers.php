<?php declare(strict_types = 1);

namespace WebChemistry\Images\Storages\LocalStorage;

use WebChemistry\Images\Modifiers\ILoader;
use WebChemistry\Images\Modifiers\ImageParameters;
use WebChemistry\Images\Modifiers\ModifierContainer;

class LocalModifiers implements ILoader {

	/**
	 * {@inheritdoc}
	 */
	public function load(ModifierContainer $modifierContainer) {
		$modifierContainer->addParameterModifier('baseUri', function (ImageParameters $imageParameters) {
			$imageParameters->addParameter('baseUri', true);
		});
	}

}
