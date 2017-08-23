<?php

namespace WebChemistry\Images\Modifiers;


use Nette\Utils\Image;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\TypeException;

class ModifierContainer {

	/** @var callable[] */
	private $modifiers = [];

	/** @var callable[] */
	private $parameterModifiers = [];

	/** @var ILoader[] */
	private $loaders = [];

	/** @var array */
	private $aliases = [];

	/**
	 * @param string $name
	 * @param callable|NULL $callback
	 * @throws TypeException
	 */
	public function addModifier($name, callable $callback) {
		if (!$name || !is_string($name)) {
			throw new TypeException('string', $name);
		}
		$this->modifiers[$name] = $callback;
	}

	/**
	 * @param string $name
	 * @param callable|NULL $callback
	 * @throws TypeException
	 */
	public function addParameterModifier($name, callable $callback)  {
		if (!$name || !is_string($name)) {
			throw new TypeException('string', $name);
		}
		$this->parameterModifiers[$name] = $callback;
	}

	/**
	 * @param ILoader $modifier
	 */
	public function addLoader(ILoader $modifier) {
		$this->loaders[] = $modifier;
	}

	/**
	 * @param string $alias
	 * @param array $modifiers
	 * @throws ModifierException
	 * @throws TypeException
	 */
	public function addAlias($alias, array $modifiers) {
		if (!$alias || !is_string($alias)) {
			throw new TypeException('string', $alias);
		}
		if (isset($this->aliases[$alias])) {
			throw new ModifierException('Alias already exists.');
		}
		$this->aliases[$alias] = $modifiers;
	}

	private function load() {
		foreach ($this->loaders as $loader) {
			$loader->load($this);
		}
		$this->loaders = [];
	}

	public function modifiersFromResource(IResource $resource) {
		$modifiers = [];
		foreach ($resource->getAliases() as $alias) {
			if (!isset($this->aliases[$alias])) {
				throw new ModifierException("Configuration for alias '$alias' not exists.");
			}

			$modifiers = array_merge($this->aliases[$alias], $modifiers);
		}

		return $modifiers;
	}

	public function getImageParameters(IResource $resource) {
		$this->load();

		$parameters = new ImageParameters();
		foreach ($resource->getAliases() as $alias) {
			if (!isset($this->aliases[$alias])) {
				throw new ModifierException("Configuration for alias '$alias' not exists.");
			}

			foreach ($this->aliases[$alias] as $modifier => $values) {
				if (!isset($this->parameterModifiers[$modifier])) {
					continue;
				}

				$callback = $this->parameterModifiers[$modifier];

				array_unshift($values, $parameters);
				call_user_func_array($callback, $values);
			}
		}

		return $parameters;
	}

	public function modifyImage(IResource $resource, Image $image) {
		$this->load();

		foreach ($resource->getAliases() as $alias) {
			if (!isset($this->aliases[$alias])) {
				throw new ModifierException("Configuration for alias '$alias' not exists.");
			}
			foreach ($this->aliases[$alias] as $modifier => $values) {
				if (!isset($this->modifiers[$modifier])) {
					continue;
				}

				$callback = $this->modifiers[$modifier];
				if ($callback === NULL) {
					continue;
				}

				array_unshift($values, $image);
				call_user_func_array($callback, $values);
			}
		}
	}

}
