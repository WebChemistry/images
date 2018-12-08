<?php declare(strict_types = 1);

namespace WebChemistry\Images\Modifiers;

use WebChemistry\Images\Parsers\Values;
use WebChemistry\Images\Resources\IResource;

class ModifierContainer implements IModifiers {

	/** @var array */
	private $modifiers = [];

	/** @var ILoader[] */
	private $loaders = [];

	/** @var Values[] */
	private $aliases = [];

	public function addModifier(string $name, ?callable $callback, bool $changeSignature = true): void {
		$this->modifiers[$name] = [$callback, $changeSignature];
	}

	public function addLoader(ILoader $modifier): void {
		$this->loaders[] = $modifier;
	}

	/**
	 * @param string $alias
	 * @param Values $modifiers
	 * @throws ModifierException
	 */
	public function addAlias(string $alias, Values $modifiers): void {
		if (isset($this->aliases[$alias])) {
			throw new ModifierException('Alias already exists.');
		}
		$this->aliases[$alias] = $modifiers;
	}

	private function load(): void {
		foreach ($this->loaders as $loader) {
			$loader->load($this);
		}
		$this->loaders = [];
	}

	/**
	 * @param IResource $resource
	 * @return iterable [callback, values, changeSignature, alias]
	 */
	public function getModifiersByResource(IResource $resource): iterable {
		$this->load();

		$array = [];
		foreach ($resource->getAliases() as $alias => $args) {
			if (!isset($this->aliases[$alias])) {
				throw new ModifierException("Configuration for alias '$alias' not exists.");
			}

			foreach ($this->aliases[$alias]->call($args) as $modifier => $values) {
				if (!isset($this->modifiers[$modifier])) {
					throw new ModifierException("Modifier '$modifier' not exists.");
				}

				/** @var callable|null $callback */
				[$callback, $changeSignature] = $this->modifiers[$modifier];
				if ($callback === null) {
					continue;
				}

				$array[] = [$callback, $values, $changeSignature, $alias];
			}
		}

		return $array;
	}

}
