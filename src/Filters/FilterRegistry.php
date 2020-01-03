<?php declare(strict_types = 1);

namespace WebChemistry\Images\Filters;

use Nette\SmartObject;
use ReflectionFunction;
use WebChemistry\Images\Exceptions\FilterException;

final class FilterRegistry implements IFilterRegistry {

	use SmartObject;

	/** @var IFilterLoader[] */
	protected $loaders = [];

	/** @var callable[] */
	protected $modifiers = [];

	public function addLoader(IFilterLoader $loader) {
		$this->loaders[] = $loader;

		return $this;
	}

	public function addFilter(string $name, callable $callback) {
		$this->modifiers[$name] = $callback;

		return $this;
	}

	public function hasFilter(string $name): bool {
		return isset($this->modifiers[$name]);
	}

	protected function tryModifierFromLoader(string $name): ?callable {
		foreach ($this->loaders as $loader) {
			$modifier = $loader->load($name);
			if (is_callable($modifier)) {
				return $modifier;
			}
		}

		return null;
	}

	public function call(FilterArgs $args, string $name, array $arguments = []) {
		if (!$this->hasFilter($name)) {
			$modifier = $this->tryModifierFromLoader($name);
			if (!$modifier) {
				throw new FilterException(sprintf('Filter %s not exists in registry or filter loaders', $name));
			}

			$this->addFilter($name, $modifier);
		}

		return $this->modifiers[$name]($args, ...$arguments);
	}

}
