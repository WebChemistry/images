<?php declare(strict_types = 1);

namespace WebChemistry\Images\Storages\Cloudinary;

use Cloudinary;
use WebChemistry\Images\ImageStorageException;
use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Resources\FileResource;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;

class CloudinaryFacade {

	/** @var ModifierContainer */
	private $modifierContainer;

	public function __construct(array $config, ModifierContainer $modifierContainer) {
		$result = [];
		foreach ($config as $name => $value) {
			$result[strtolower(preg_replace('#(.)(?=[A-Z])#', '$1_', $name))] = $value;
		}

		$this->modifierContainer = $modifierContainer;
		Cloudinary::config($result);
	}

	private function parseId(IResource $resource) {
		return substr($resource->getId(), 0, strrpos($resource->getId(), '.'));
	}

	public function save(ITransferResource $resource) {
		try {
			$result = Cloudinary\Uploader::upload($resource->getLocation(), array_merge($this->modifierContainer->modifiersFromResource($resource), [
				'public_id' => $this->parseId($resource),
				'resource_type' => 'image',
			]));
		} catch (Cloudinary\Error $e) {
			throw new ImageStorageException($e->getMessage(), $e->getCode(), $e);
		}

		return new FileResource($result['public_id']);
	}

	public function delete(IFileResource $resource) {
		try {
			Cloudinary\Uploader::destroy($resource->getId());
		} catch (Cloudinary\Error $e) {
			throw new ImageStorageException($e->getMessage(), $e->getCode(), $e);
		}
	}

	public function link(IFileResource $resource) {
		$options = $this->modifierContainer->modifiersFromResource($resource);

		return Cloudinary::cloudinary_url($resource->getId(), $options);
	}

	public function move(IFileResource $src, IFileResource $dest) {
		if ($src->getId() === $dest->getId()) {
			throw new ImageStorageException('Cannot move same resources.');
		}

		return Cloudinary\Uploader::rename($src->getId(), $dest->getId());
	}

	public function copy(IFileResource $src, IFileResource $dest) {
		if ($src->getId() === $dest->getId()) {
			throw new ImageStorageException('Cannot copy same resources.');
		}

		return Cloudinary\Uploader::upload($this->link($src), array_merge($this->modifierContainer->modifiersFromResource($dest), [
			'public_id' => $this->parseId($dest)
		]));
	}

}
