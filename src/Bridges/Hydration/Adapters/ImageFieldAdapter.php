<?php declare(strict_types = 1);

namespace WebChemistry\Images\Bridges\Hydration\Adapters;

use WebChemistry\DoctrineHydration\Adapters\IFieldAdapter;
use WebChemistry\DoctrineHydration\IPropertyAccessor;
use WebChemistry\DoctrineHydration\Metadata;
use WebChemistry\DoctrineHydration\SkipValueException;
use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\StateResource;

class ImageFieldAdapter implements IFieldAdapter {

	/** @var IImageStorage */
	private $imageStorage;

	/** @var IPropertyAccessor */
	private $propertyAccessor;

	public function __construct(IImageStorage $imageStorage, IPropertyAccessor $propertyAccessor) {
		$this->imageStorage = $imageStorage;
		$this->propertyAccessor = $propertyAccessor;
	}

	public function isWorkable($object, string $field, Metadata $metadata, array $settings): bool {
		return !$metadata->isAssociation($field) && $metadata->getFieldMapping($field)['type'] === 'image';
	}

	/**
	 * @param object|null $object
	 * @param string $field
	 * @param IFileResource|StateResource|null $value
	 * @param Metadata $metadata
	 * @param array $settings
	 * @return \WebChemistry\Images\Resources\IFileResource|null
	 * @throws SkipValueException
	 */
	public function work($object, string $field, $value, Metadata $metadata, array $settings) {
		if (!$value) {
			if ($object) {
				throw new SkipValueException();
			} else {
				return null;
			}
		}
		if ($value instanceof StateResource) {
			if ($value->getDelete()) {
				$this->imageStorage->delete($value->getDelete());
			}
			if (!$value->getUpload()) {
				return $value->getDefaultValue();
			}

			$value = $value->getUpload();
		} else if ($object && ($image = $this->propertyAccessor->get($object, $field))) {
			$this->imageStorage->delete($image);
		}

		$settings = $settings['images'][$field] ?? [];

		if (isset($settings['alias'])) {
			$value->setAlias($settings['alias']);
		}
		if (isset($settings['aliases'])) {
			$value->setAliases($settings['aliases']);
		}
		if (isset($settings['name'])) {
			$value->setName($settings['name']);
		}
		if (isset($settings['suffix'])) {
			$value->setSuffix($settings['suffix']);
		}

		return $this->imageStorage->save($value);

	}

}
