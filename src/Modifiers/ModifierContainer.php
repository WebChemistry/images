<?php

namespace WebChemistry\Images\Modifiers;


use Nette\Utils\Image;
use WebChemistry\Images\Parsers\Values;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\TypeException;

class ModifierContainer {

	/** @var callable[] */
	private $modifiers = [];

	/** @var callable[] */
	private $parameterModifiers = [];

	/** @var ILoader[] */
	private $loaders = [];

	/** @var Values[] */
	private $aliases = [];

	/**
	 * @param string $name
	 * @param callable|null $callback
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
	 * @param callable|null $callback
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
	 * @param Values $modifiers
	 * @throws ModifierException
	 * @throws TypeException
	 */
	public function addAlias($alias, Values $modifiers) {
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

	/**
	 * @param IResource $resource
	 * @return array
	 * @throws ModifierException
	 */
	public function modifiersFromResource(IResource $resource) {
		$modifiers = [];
		foreach ($resource->getAliases() as $alias => $args) {
			if (!isset($this->aliases[$alias])) {
				throw new ModifierException("Configuration for alias '$alias' not exists.");
			}

			$modifiers = array_merge($this->aliases[$alias]->call($args), $modifiers);
		}

		return $modifiers;
	}

	public function extractActiveAliases(IResource $resource) {
		$this->load();

		$aliases = [];
		foreach ($resource->getAliases() as $alias => $args) {
			if (!isset($this->aliases[$alias])) {
				throw new ModifierException("Configuration for alias '$alias' not exists.");
			}
			if ($args) {
				$aliases[$alias] = $args;
				continue;
			}
			$values = $this->aliases[$alias]->call($args);
			foreach ($values as $name => $_) {
				if (isset($this->modifiers[$name])) {
					$aliases[$alias] = $args;

					break;
				}
			}
		}

		return $aliases;
	}

	/**
	 * @param IResource $resource
	 * @return ImageParameters
	 * @throws ModifierException
	 */
	public function getImageParameters(IResource $resource) {
		$this->load();

		$parameters = new ImageParameters();
		foreach ($resource->getAliases() as $alias => $args) {
			if (!isset($this->aliases[$alias])) {
				throw new ModifierException("Configuration for alias '$alias' not exists.");
			}

			foreach ($this->aliases[$alias]->call($args) as $modifier => $values) {
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

		foreach ($resource->getAliases() as $alias => $args) {
			if (!isset($this->aliases[$alias])) {
				throw new ModifierException("Configuration for alias '$alias' not exists.");
			}

			$values = $this->aliases[$alias]->call($args);
			foreach ($values as $modifier => $values) {
				if (!isset($this->modifiers[$modifier])) {
					continue;
				}

				$callback = $this->modifiers[$modifier];
				if ($callback === null) {
					continue;
				}

				array_unshift($values, $image);
				call_user_func_array($callback, $values);
			}
		}
	}

}
