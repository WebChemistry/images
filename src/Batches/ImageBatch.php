<?php declare(strict_types = 1);

namespace WebChemistry\Images\Batches;

use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\PromiseFileResource;

class ImageBatch implements IBatch {

	/** @var IImageStorage */
	private $storage;

	/** @var PromiseFileResource[] */
	private $saveBatch = [];

	/** @var PromiseFileResource[] */
	private $copyBatch = [];

	/** @var PromiseFileResource[] */
	private $moveBatch = [];

	/** @var PromiseFileResource[] */
	private $deleteBatch = [];

	/** @var IBatch[] */
	private $batches = [];

	public function __construct(IImageStorage $storage) {
		$this->storage = $storage;
	}

	public function flush(): void {
		foreach ($this->saveBatch as $resource) {
			$resource->called($this->storage->save($resource->getSource()));
		}
		foreach ($this->copyBatch as $resource) {
			$this->storage->copy($resource->getSource(), $resource->getDestination());
		}
		foreach ($this->moveBatch as $resource) {
			$this->storage->move($resource->getSource(), $resource->getDestination());
		}
		foreach ($this->deleteBatch as $resource) {
			$this->storage->delete($resource);
		}
		foreach ($this->batches as $batch) {
			$batch->flush();
		}

		$this->saveBatch = $this->copyBatch = $this->moveBatch = $this->deleteBatch = $this->batches = [];
	}

	public function save(IResource $resource): IFileResource {
		return $this->saveBatch[] = new PromiseFileResource($resource);
	}

	public function copy(IFileResource $src, IFileResource $dest): IFileResource {
		return $this->copyBatch[] = new PromiseFileResource($src, $dest);
	}

	public function move(IFileResource $src, IFileResource $dest): IFileResource {
		return $this->moveBatch[] = new PromiseFileResource($src, $dest);
	}

	public function delete(IFileResource $resource): IFileResource {
		return $this->deleteBatch[] = new PromiseFileResource($resource);
	}

	public function addBatch(IBatch $batch): IBatch {
		$this->batches[] = $batch;

		return $this;
	}

}
