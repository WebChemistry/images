<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Promise;

use Nette\SmartObject;
use WebChemistry\Images\Resources\IResource;

/**
 * @internal
 */
final class InternalPromiseResource {

	use SmartObject;

	/** @var PromiseResource */
	private $resource;

	/** @var callable */
	private $setId;

	/** @var callable */
	private $onSuccess;

	/** @var callable */
	private $onError;

	public function __construct(PromiseResource $resource, callable $setId, callable $onSuccess, callable $onError) {
		$this->resource = $resource;
		$this->setId = $setId;
		$this->onSuccess = $onSuccess;
		$this->onError = $onError;
	}

	public function getSource(): IResource {
		return $this->resource->getSource();
	}

	public function getDestination(): ?IResource {
		return $this->resource->getDestination();
	}

	public function getResource(): PromiseResource {
		return $this->resource;
	}

	public function success(): void {
		($this->onSuccess)();
	}

	public function error(): void {
		($this->onError)();
	}

	public function setId(string $id): void {
		($this->setId)($id);
	}

}
