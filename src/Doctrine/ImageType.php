<?php declare(strict_types = 1);

namespace WebChemistry\Images\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use WebChemistry\Images\ImageStorageException;
use WebChemistry\Images\Resources\FileResource;
use WebChemistry\Images\Resources\IFileResource;

class ImageType extends Type {

	const TYPE = 'image';

	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
		return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform) {
		if (!$value instanceof IFileResource && $value !== null) {
			throw new ImageStorageException('Value must be instance of ' . IFileResource::class . ', ' . $this->debugType($value) . ' given.');
		} else if ($value === null || $value->isEmpty()) {
			return $value;
		}

		return $value->getId();
	}

	private function debugType($value) {
		if (is_object($value)) {
			return 'class of ' . get_class($value);
		} else {
			return gettype($value);
		}
	}

	public function convertToPHPValue($value, AbstractPlatform $platform) {
		if ($value === null) {
			return null;
		}

		return new FileResource($value);
	}

	public function getDefaultLength(AbstractPlatform $platform) {
		return $platform->getVarcharDefaultLength();
	}

	public function getName() {
		return self::TYPE;
	}


	public function requiresSQLCommentHint(AbstractPlatform $platform) {
		$platform->markDoctrineTypeCommented(self::TYPE);

		return parent::requiresSQLCommentHint($platform);
	}

}
