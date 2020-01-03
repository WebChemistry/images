<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Promise;

use WebChemistry\Images\Resources\Filters\ResourceFilter;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Resource;
use WebChemistry\Images\Resources\ResourceException;

final class PromiseResource extends Resource implements IFileResource {

	/** @var callable[] */
	private $then = [];

	/** @var callable[] */
	private $error = [];

	/** @var IResource */
	private $source;

	/** @var IResource|null */
	private $destination;

	public function __construct(IResource $source, ?IResource $destination = null) {
		$this->source = $source;
		$this->destination = $destination;
	}

	public function then(callable $callback): self {
		$this->then[] = $callback;

		return $this;
	}

	public function error(callable $callback): self {
		$this->error[] = $callback;

		return $this;
	}

	/**
	 * @return self
	 * @throws ResourceException
	 */
	public function getOriginal(): IFileResource {
		return new self($this->source, $this->destination);
	}

	public function getSource(): IResource {
		return $this->source;
	}

	public function getDestination(): ?IResource {
		return $this->destination;
	}

	// internals

	public static function create(IResource $source, ?IResource $destination = null): InternalPromiseResource {
		$resource = new static($source, $destination);

		return new InternalPromiseResource($resource, function (string $id) use ($resource): void {
			$resource->_setId($id);
		}, function () use ($resource): void {
			foreach ($resource->then as $callback) {
				$callback($resource);
			}
		}, function () use ($resource): void {
			foreach ($resource->error as $callback) {
				$callback($resource);
			}
		});
	}

	private function _setId(string $id): void {
		$this->parseId($id);
	}

}
