<?php declare(strict_types = 1);

namespace WebChemistry\Images\Transactions;

use LogicException;
use Nette\SmartObject;
use Throwable;
use WebChemistry\Images\Exceptions\TransactionClosedException;
use WebChemistry\Images\Exceptions\TransactionException;
use WebChemistry\Images\Exceptions\TransactionPersistedException;

final class TransactionComposite implements ITransactional {

	use SmartObject;

	/** @var ITransaction[] */
	private $transactions = [];

	/** @var bool */
	private $persisted = false;

	public function addTransaction(ITransaction $transaction): void {
		if ($transaction->isClosed()) {
			throw new LogicException('Cannot add closed transaction');
		}
		if ($transaction->isPersisted()) {
			throw new LogicException('Cannot add persisted transaction');
		}

		$this->transactions[] = $transaction;
	}

	/**
	 * @throws TransactionException
	 */
	public function commit(): void {
		foreach ($this->transactions as $index => $transaction) {
			try {
				$transaction->commit();
			} catch (Throwable $exception) {
				$this->rollback();

				if ($exception instanceof TransactionClosedException) {
					throw new TransactionClosedException(sprintf('Transaction %d is closed', $index), 0, $exception);
				}
				if ($exception instanceof TransactionPersistedException) {
					throw new TransactionPersistedException(sprintf('Transaction %d is persisted', $index), 0, $exception);
				}

				throw new TransactionException(sprintf('Transaction %d failed', $index), 0, $exception);
			}
		}
	}

	public function persist(): void {
		foreach ($this->transactions as $transaction) {
			$transaction->persist();
		}
	}

	public function rollback(): void {
		foreach ($this->transactions as $transaction) {
			if ($transaction->isPersisted()) {
				continue;
			}

			$transaction->rollback();
		}
	}

}
