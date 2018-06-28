<?php declare(strict_types = 1);

namespace WebChemistry\Images\Storages;

use Nette\NotImplementedException;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Image\ImageSize;
use WebChemistry\Images\Modifiers\BaseModifiers;
use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;
use WebChemistry\Images\Storage;
use WebChemistry\Images\Storages\S3\S3Facade;

class S3Storage extends Storage {

	const ORIGINAL = 'original';

	/** @var \WebChemistry\Images\Modifiers\ModifierContainer */
	private $modifierContainer;

	/** @var \WebChemistry\Images\Storages\S3\S3Facade */
	private $facade;

	/** @var null|string  */
	private $defaultImage;

	public function __construct(array $config, ModifierContainer $modifierContainer, IImageFactory $imageFactory, ?string $defaultImage = null) {
		$modifierContainer->addLoader(new BaseModifiers());
		$this->modifierContainer = $modifierContainer;
		$this->facade = new S3Facade($config, $modifierContainer, $imageFactory);
		$this->defaultImage = $defaultImage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function link(IFileResource $resource): ?string {
		$parameters = $this->modifierContainer->getImageParameters($resource);
		$defaultImage = $parameters->getDefaultImage() ? : $this->defaultImage;
		if (($location = $this->facade->link($resource)) === null && $defaultImage) {
			$default = $this->createResource($defaultImage);
			$default->setAliases($resource->getAliases());
			$location = $this->facade->link($default);
		}

		return $location;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(IResource $resource): IFileResource {
		if (!$resource instanceof ITransferResource) {
			return $this->createResource($resource->getId());
		}
		$resource->setSaved();

		return $this->facade->save($resource);
	}

	/**
	 * {@inheritdoc}
	 */
	public function copy(IFileResource $src, IFileResource $dest) {
		$this->facade->copy($src, $dest);
	}

	/**
	 * {@inheritdoc}
	 */
	public function move(IFileResource $src, IFileResource $dest) {
		$this->facade->move($src, $dest);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(IFileResource $resource) {
		$this->facade->delete($resource);
	}

	/**
	 * @param bool $backCompatibility
	 *
	 * @return void
	 */
	public function setBackCompatibility($backCompatibility = true) {
		$this->facade->setBackCompatibility($backCompatibility);
	}

	public function getImageSize(IFileResource $resource): ImageSize {
		throw new NotImplementedException('Method is not implemented yet.');
	}

}
