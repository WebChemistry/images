<?php declare(strict_types = 1);

namespace WebChemistry\Images\Transactions;

use WebChemistry\Images\Exceptions\TransactionException;

interface ITransactional {

	/**
	 * @throws TransactionException
	 */
	public function commit(): void;

	public function persist(): void;

	public function rollback(): void;

}