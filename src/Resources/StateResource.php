<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources;

use WebChemistry\Images\Resources\Transfer\ITransferResource;

class StateResource {

	/** @var IFileResource|null */
	private $resource;

	/** @var ITransferResource|null */
	private $upload;

	/** @var bool */
	private $delete;

	public function __construct(?IFileResource $resource = null, ?ITransferResource $upload = null, bool $delete = false) {
		$this->resource = $resource;
		$this->upload = $upload;
		$this->delete = $delete;
	}

	protected function toDelete(): bool {
		return $this->delete || ($this->resource && $this->upload);
	}

	public function getDelete(): ?IFileResource {
		if (!$this->toDelete()) {
			return null;
		}

		return $this->resource;
	}

	public function getUpload(): ?ITransferResource {
		return $this->upload;
	}

	public function getDefaultValue(): ?IFileResource {
		if ($this->toDelete()) {
			return null;
		}

		return $this->resource;
	}

}
