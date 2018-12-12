<?php declare(strict_types = 1);

namespace Test;

use PHPUnit\Framework\TestCase;

class StateTester {

	/** @var StateRow[] */
	protected $states = [];

	/** @var TestCase */
	private $case;

	public function __construct(TestCase $case) {
		$this->case = $case;
	}

	public function addState(string $description) {
		$this->states[] = $state = new StateRow($description);

		return $state;
	}

	public function call(callable $test) {
		$name = $this->case->getName();
		foreach ($this->states as $i => $state) {
			$this->case->setName('State(' . $i . '): ' . $state->__getDescription());
			$test(...$state());
		}
		$this->case->setName($name);
	}

}
