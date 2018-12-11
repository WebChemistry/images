<?php declare(strict_types = 1);

namespace WebChemistry\Images\Modifiers;

use WebChemistry\Images\Parsers\Values;
use WebChemistry\Images\Resources\IResource;

class ModifierContainer implements IModifiers {

	/** @var array */
	private $modifiers = [];

	/** @var callable[] */
	private $resourceModifiers = [];

	/** @var ILoader[] */
	private $loaders = [];

	/** @var Values[] */
	private $aliases = [];

	public function addResourceModifier(string $name, ?callable $callback): void {
		if ($callback === null) {
			unset($this->resourceModifiers[$name]);
		} else {
			$this->resourceModifiers[$name] = $callback;
		}
	}

	public function addModifier(string $name, ?callable $callback, bool $changeSignature = true): void {
		if ($callback === null) {
			unset($this->modifiers[$name]);
		} else {
			$this->modifiers[$name] = [$callback, $changeSignature];
		}
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

	protected function checkModifierName(string $name) {
		if (!isset($this->modifiers[$name]) && !isset($this->resourceModifiers[$name])) {
			throw new ModifierException("Modifier or resource modifier '$name' not exists.");
		}
	}

	/**
	 * @param IResource $resource
	 * @return array [callback, values, changeSignature, alias]
	 */
	public function getModifiersByResource(IResource $resource): array {
		$this->load();

		$array = [];
		foreach ($resource->getAliases() as $alias => $args) {
			if (!isset($this->aliases[$alias])) {
				throw new ModifierException("Configuration for alias '$alias' not exists.");
			}

			foreach ($this->aliases[$alias]->call($args) as $modifier => $values) {
				if (!isset($this->modifiers[$modifier])) {
					$this->checkModifierName($modifier);

					continue;
				}

				/** @var callable $callback */
				[$callback, $changeSignature] = $this->modifiers[$modifier];

				$array[] = [$callback, $values, $changeSignature, $alias];
			}
		}

		return $array;
	}

	/**
	 * @param IResource $resource
	 * @return array [callback, values]
	 */
	public function getResourceModifiersByResource(IResource $resource): array {
		$this->load();

		$array = [];
		foreach ($resource->getAliases() as $alias => $args) {
			if (!isset($this->aliases[$alias])) {
				throw new ModifierException("Configuration for alias '$alias' not exists.");
			}

			foreach ($this->aliases[$alias]->call($args) as $modifier => $values) {
				if (!isset($this->resourceModifiers[$modifier])) {
					$this->checkModifierName($modifier);

					continue;
				}

				/** @var callable $callback */
				$callback = $this->resourceModifiers[$modifier];

				$array[] = [$callback, $values];
			}
		}

		return $array;
	}

}
