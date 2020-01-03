<?php declare(strict_types = 1);

namespace WebChemistry\Images\Filters;

use Nette\SmartObject;

abstract class BaseFilterLoader implements IFilterLoader {

	use SmartObject;

	public function load(string $name): ?callable {
		if (method_exists($this, $name)) {
			return [$this, $name];
		}

		return null;
	}

}
