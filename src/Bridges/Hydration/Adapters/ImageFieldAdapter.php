<?php declare(strict_types = 1);

namespace WebChemistry\Images\Bridges\Hydration\Adapters;

use Nettrine\Hydrator\Adapters\IFieldAdapter;
use Nettrine\Hydrator\Arguments\FieldArgs;
use Nettrine\Hydrator\IPropertyAccessor;
use WebChemistry\Images\IImageStorage;
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
		return !$args->metadata->isAssociation($args->field) && $args->metadata->getFieldMapping($args->field)['type'] === 'image';
	}

	public function work(FieldArgs $args): void {
		$value = $args->value;

		if (!$value) {
			if (!$args->object) {
				$args->value = null;
			}

			return;
		}

		if ($value instanceof StateResource) {
			if ($value->getDelete()) {
				$this->imageStorage->delete($value->getDelete());
			}
			if (!$value->getUpload()) {
				$args->value = $value->getDefaultValue();

				return;
			}

			$value = $value->getUpload();
		} else if ($args->object && ($image = $this->propertyAccessor->get($args->object, $args->field))) {
			$this->imageStorage->delete($image);
		}

		$settings = $args->settings['images'][$args->field] ?? [];

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

		$args->value = $this->imageStorage->save($value);
	}

}
