<?php

namespace WebChemistry\Images\Storages;


use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;
use WebChemistry\Images\Storage;
use WebChemistry\Images\Storages\S3\S3Facade;

class S3Storage extends Storage {

	const ORIGINAL = 'original';

	/** @var \WebChemistry\Images\Modifiers\ModifierContainer  */
	private $modifierContainer;

	/** @var \WebChemistry\Images\Storages\S3\S3Facade  */
	private $facade;

	/** @var null|string  */
	private $defaultImage;

	/**
	 * @param array                                             $config
	 * @param \WebChemistry\Images\Modifiers\ModifierContainer  $modifierContainer
	 * @param \WebChemistry\Images\Image\IImageFactory          $imageFactory
	 * @param string|NULL                                       $defaultImage
	 */
	public function __construct(array $config, ModifierContainer $modifierContainer, IImageFactory $imageFactory, $defaultImage = NULL) {
		$this->modifierContainer = $modifierContainer;
		$this->facade = new S3Facade($config, $modifierContainer, $imageFactory);
		$this->defaultImage = $defaultImage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function link(IFileResource $resource) {
		$parameters = $this->modifierContainer->getImageParameters($resource);
		$defaultImage = $parameters->getDefaultImage() ? : $this->defaultImage;
		if (($location = $this->facade->link($resource)) === FALSE && $defaultImage) {
			$default = $this->createResource($defaultImage);
			$default->setAliases($resource->getAliases());
			$location = $this->facade->link($default);
		}

		return $location;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(IResource $resource) {
		if (!$resource instanceof ITransferResource) {
			return $resource; // or throw exception?
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
	public function setBackCompatibility($backCompatibility = TRUE) {
		$this->facade->setBackCompatibility($backCompatibility);
	}
}
