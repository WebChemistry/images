<?php declare(strict_types = 1);

namespace WebChemistry\Images\Filters;

interface IFilterRegistry {

	public function addLoader(IFilterLoader $loader);

	public function addFilter(string $name, callable $callback);

	public function hasFilter(string $name): bool;

	/**
	 * @return mixed
	 */
	public function call(FilterArgs $args, string $name, array $arguments = []);

}