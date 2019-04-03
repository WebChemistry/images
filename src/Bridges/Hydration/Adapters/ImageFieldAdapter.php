<?php declare(strict_types = 1);

namespace WebChemistry\Images\Bridges\Hydration\Adapters;

use Nettrine\Hydrator\Adapters\IFieldAdapter;
use Nettrine\Hydrator\Arguments\FieldArgs;
use Nettrine\Hydrator\IPropertyAccessor;
use Nettrine\Hydrator\Metadata;
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

	public function isWorkable(FieldArgs $args): bool {
		$metadata = $args->getMetadata();
		$field = $args->getField();
		return !$metadata->isAssociation($field) && $metadata->getFieldMapping($field)['type'] === 'image';
	}

	public function work(FieldArgs $args): void {
		/** @var IFileResource|StateResource|null  $value */
		$value = $args->getValue();
		/** @var object|null $object */
		$object = $args->object;
		$field = $args->getField();

		if (!$value) {
			return;
		}
		if ($value instanceof StateResource) {
			if ($value->getDelete()) {
				$this->imageStorage->delete($value->getDelete());
			}
			if (!$value->getUpload()) {
				$value = $value->getDefaultValue();
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

		$this->imageStorage->save($value);

	}

}
