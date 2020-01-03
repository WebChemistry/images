<?php declare(strict_types = 1);

namespace WebChemistry\Images\Transactions;

use LogicException;
use Throwable;
use WebChemistry\Images\Exceptions\RollbackException;
use WebChemistry\Images\Exceptions\TransactionClosedException;
use WebChemistry\Images\Exceptions\TransactionException;
use WebChemistry\Images\Exceptions\TransactionPersistedException;
use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\Promise\InternalPromiseResource;
use WebChemistry\Images\Resources\Promise\PromiseResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;

class Transaction implements ITransaction {

	/** @var IImageStorage */
	private $storage;

	// commit

	/** @var InternalPromiseResource[] */
	private $save = [];

	/** @var InternalPromiseResource[] */
	private $copy = [];

	/** @var InternalPromiseResource[] */
	private $move = [];

	// rollback

	/** @var IFileResource[] */
	private $rollbackToDelete = [];

	/** @var PromiseResource[] */
	private $rollbackToMove = [];

	/** @var bool */
	private $closed = false;

	/** @var bool */
	private $fail = false;

	/** @var bool */
	private $persisted = false;

	public function __construct(IImageStorage $storage) {
		$this->storage = $storage;
	}

	public function isClosed(): bool {
		return $this->closed;
	}

	public function isPersisted(): bool {
		return $this->persisted;
	}

	/**
	 * @throws TransactionException
	 */
	public function commit(): void {
		$this->errorIfClosed();
		$this->closed = true;

		try {
			foreach ($this->copy as $resource) {
				$this->rollbackToDelete[] = $copy = $this->storage->copy($resource->getSource(), $resource->getDestination());

				$resource->setId($copy->getId());
			}
			foreach ($this->move as $resource) {
				$this->rollbackToMove[] = $resource;

				$move = $this->storage->move($resource->getSource(), $resource->getDestination());

				$resource->setId($move->getId());
			}
			foreach ($this->save as $resource) {
				$this->rollbackToDelete[] = $save = $this->storage->save($resource->getSource());

				$resource->setId($save->getId());
			}
		} catch (Throwable $exception) {
			$this->rollback();

			throw new TransactionException('Transaction failed, reason: ' . $exception->getMessage(), 0, $exception);
		}
	}

	protected function safeCall(callable $callback, array &$fails): void {
		try {
			$callback();
		} catch (Throwable $exception) {
			$fails[] = $exception;
		}
	}

	public function persist(): void {
		$this->errorIfNotClosed();
		$this->errorIfPersisted();

		$this->persisted = true;

		if ($this->fail) {
			$this->error($this->save);
			$this->error($this->move);
			$this->error($this->copy);
		} else {
			$this->success($this->save);
			$this->success($this->move);
			$this->success($this->copy);
		}
	}

	/**
	 * @throws RollbackException
	 */
	public function rollback(): void {
		$this->errorIfPersisted();

		$this->closed = true;
		$this->fail = true;

		$fails = [];
		foreach ($this->rollbackToDelete as $resource) {
			$this->safeCall(function () use ($resource): void {
				$this->storage->delete($resource);
			}, $fails);
		}

		foreach ($this->rollbackToMove as $resource) {
			$this->safeCall(function () use ($resource): void {
				$this->storage->move($resource->getDestination(), $resource->getSource());
			}, $fails);
		}

		$this->persist();

		if ($fails) {
			throw new RollbackException($fails);
		}
	}

	public function save(ITransferResource $resource): IFileResource {
		$internal = $this->save[] = PromiseResource::create($resource);

		return $internal->getResource();
	}

	public function copy(IFileResource $src, IFileResource $dest): IFileResource {
		$internal = $this->copy[] = PromiseResource::create($src, $dest);

		return $internal->getResource();
	}

	public function move(IFileResource $src, IFileResource $dest): IFileResource {
		$internal = $this->move[] = PromiseResource::create($src, $dest);

		return $internal->getResource();
	}

	private function errorIfClosed(): void {
		if ($this->isClosed()) {
			throw new TransactionClosedException('Transaction is closed');
		}
	}

	private function errorIfNotClosed(): void {
		if (!$this->isClosed()) {
			throw new LogicException('Call commit() or rollback() first');
		}
	}

	private function errorIfPersisted(): void {
		if ($this->isPersisted()) {
			throw new TransactionPersistedException('Transaction is already persisted');
		}
	}

	/**
	 * @param InternalPromiseResource[] $resources
	 */
	private function error(array $resources): void {
		foreach ($resources as $resource) {
			$resource->error();
		}
	}

	/**
	 * @param InternalPromiseResource[] $resources
	 */
	private function success(array $resources): void {
		foreach ($resources as $resource) {
			$resource->success();
		}
	}

}
