<?php declare(strict_types = 1);

namespace WebChemistry\Images\Controls;

use Nette\SmartObject;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;

final class AdvancedUploadControlValue {

	use SmartObject;

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
