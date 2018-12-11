<?php declare(strict_types = 1);

namespace WebChemistry\Images\Modifiers;

use WebChemistry\Images\Parsers\Values;
use WebChemistry\Images\Resources\IResource;

interface IModifiers {

	public function addResourceModifier(string $name, ?callable $callback);

	public function addModifier(string $name, ?callable $callback, bool $changeSignature = true);

	public function addLoader(ILoader $modifier);

	public function addAlias(string $alias, Values $modifiers);

	public function getModifiersByResource(IResource $resource): array;

	public function getResourceModifiersByResource(IResource $resource): array;

}
