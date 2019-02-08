<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources;

final class PromiseFileResource extends Resource implements IFileResource {

	/** @var callable[] */
	private $callbacks = [];

	/** @var IResource */
	private $source;

	/** @var IResource|null */
	private $destination;

	public function __construct(IResource $source, ?IResource $destination = null) {
		$this->source = $source;
		$this->destination = $destination;
	}

	public function then(callable $callback) {
		$this->callbacks[] = $callback;

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

	/**
	 * @internal
	 * @param IFileResource $resource|null
	 */
	public function called(?IFileResource $resource = null) {
		if ($resource) {
			$this->parseId($resource->getId());
		}

		foreach ($this->callbacks as $callback) {
			$callback($this);
		}
	}

}
