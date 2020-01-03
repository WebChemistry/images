<?php declare(strict_types = 1);

namespace WebChemistry\Images\Exceptions;

use Throwable;

class RollbackException extends \Exception {

	/** @var Throwable[] */
	public $exceptions;

	/**
	 * @param Throwable[] $exceptions
	 */
	public function __construct(array $exceptions) {
		parent::__construct(sprintf('Rollback failed, %d exceptions', count($exceptions)), 0, $this->exceptions[0]);

		$this->exceptions = $exceptions;
	}

}
