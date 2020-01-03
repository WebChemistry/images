<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Filters;

use Nette\SmartObject;

final class ResourceFilter {

	use SmartObject;

	/** @var string */
	private $name;

	/** @var mixed[] */
	private $arguments;

	public function __construct(string $name, array $arguments) {
		$this->name = $name;
		$this->arguments = $arguments;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getArguments(): array {
		return $this->arguments;
	}

}
