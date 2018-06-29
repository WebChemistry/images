<?php declare(strict_types = 1);

namespace WebChemistry\Images\Storages;

use Nette\NotImplementedException;
use WebChemistry\Images\Image\ImageSize;
use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;
use WebChemistry\Images\Storage;
use WebChemistry\Images\Storages\Cloudinary\CloudinaryFacade;

class CloudinaryStorage extends Storage {

	/** @var CloudinaryFacade */
	private $facade;

	public function __construct(ModifierContainer $modifierContainer, array $config) {
		$this->facade = new CloudinaryFacade($config, $modifierContainer);
	}

	public function link(?IFileResource $resource): ?string {
		if ($resource === null) {
			return null;
		}

		return $this->facade->link($resource);
	}

	public function save(IResource $resource): IFileResource {
		if (!$resource instanceof ITransferResource) {
			return $this->createResource($resource->getId());
		}

		return $this->facade->save($resource);
	}

	public function copy(IFileResource $src, IFileResource $dest) {
		$this->facade->copy($src, $dest);
	}

	public function move(IFileResource $src, IFileResource $dest) {
		$this->facade->move($src, $dest);
	}

	public function delete(IFileResource $resource) {
		$this->facade->delete($resource);
	}

	public function getImageSize(IFileResource $resource): ImageSize {
		throw new NotImplementedException('Method is not implemented yet.');
	}

}
