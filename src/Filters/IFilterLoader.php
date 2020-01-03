<?php declare(strict_types = 1);

namespace WebChemistry\Images\Filters;

interface IFilterLoader {

	public function load(string $name): ?callable;

}