<?php

namespace WebChemistry\Images\Image;

use Nette;
use WebChemistry\Images\Connectors\IConnector;
use WebChemistry\Images\Connectors\Localhost;

class Info extends Nette\Object {

	const ORIGINAL = 'original';

	const PREFIX_SEP = '_._';

	/** @var Container */
	private $image;

	private $imageSize;

	private $hash;

	/** @var IConnector */
	private $connector;

	/** @var string */
	private $prefix;

	/**
	 * @param IImage      $image
	 * @param IConnector  $connector
	 * @param string|null $hash
	 */
	public function __construct(IImage $image, IConnector $connector, $hash = NULL) {
		$this->image = $image;
		$this->hash = $hash;
		$this->connector = $connector;
	}

	/************************* Image\Image methods **************************/

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->image->getUrl();
	}

	/**
	 * @return Container|IImage
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * @return int|string
	 */
	public function getHeight() {
		return $this->image->getHeight();
	}

	/**
	 * @return int|string
	 */
	public function getWidth() {
		return $this->image->getWidth();
	}

	/**
	 * @return int
	 */
	public function getFlag() {
		return $this->image->getFlag();
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->image->getName();
	}

	/**
	 * @return string
	 */
	public function getNamespace() {
		return $this->image->getNamespace();
	}

	/**
	 * @return string
	 */
	public function getPrefix() {
		return $this->prefix;
	}

	/**
	 * @return bool
	 */
	public function isBaseUri() {
		return $this->image->isBaseUri();
	}

	/************************* Directories **************************/

	/**
	 * Directory name contains size, flag and hash of helpers
	 *
	 * @return string
	 */
	public function getBaseFolder() {
		return $this->sizeFolder() . $this->flagFolder() . $this->hashFolder();
	}

	/**
	 * Namespace directories
	 *
	 * @return string
	 */
	public function namespaceFolder() {
		if ($this->image->getNamespace()) {
			return $this->image->getNamespace();
		}

		return NULL;
	}

	/**
	 * @return string
	 */
	public function getNameWithPrefix() {
		$prefix = $this->getPrefix();

		return ($prefix ? $prefix . self::PREFIX_SEP : NULL) . $this->image->getName();
	}

	/**
	 * @return null|string
	 */
	private function hashFolder() {
		if ($this->hash) {
			return '-' . $this->hash;
		}

		return NULL;
	}

	/**
	 * @return string
	 */
	private function sizeFolder() {
		$width = $this->image->getWidth();
		$height = $this->image->getHeight();

		if ($width && $height) {
			return $width . 'x' . $height;
		} else {
			if ($width) {
				return $width . 'x';
			} else {
				if ($height) {
					return 'x' . $height;
				}
			}
		}

		return self::ORIGINAL;
	}

	/**
	 * @return null|string
	 */
	private function flagFolder() {
		if ($this->image->getFlag()) {
			return '_' . $this->image->getFlag();
		}

		return NULL;
	}

	/**
	 * @return null|string
	 */
	private function getConnectorPrefix() {
		if (!$this->connector instanceof Localhost) {
			return Nette\Utils\Strings::webalize(get_class($this->connector)) . ':';
		}

		return NULL;
	}

	/**
	 * @return string
	 */
	public function getAbsoluteName() {
		if ($this->image->getAbsoluteUrl()) {
			return $this->getConnectorPrefix() . $this->image->getAbsoluteUrl();
		}

		$folder = $this->getConnectorPrefix() . $this->namespaceFolder();

		return  ($folder ? $folder . '/' : NULL) . $this->getNameWithPrefix();
	}

	/************************* Others **************************/

	/**
	 * @return array
	 */
	public function getImageSize() {
		if (!$this->imageSize) {
			$this->imageSize = $this->connector->getImageSize($this);
		}

		return $this->imageSize;
	}

	/**
	 * @return int
	 */
	public function getImageType() {
		$info = $this->getImageSize();

		return $info[2];
	}

	/**
	 * @return \WebChemistry\Images\Bridges\Nette\Image
	 */
	public function getNetteImageClass() {
		return $this->connector->getNetteImage($this);
	}

	/**
	 * @return bool
	 */
	public function isImageExists() {
		return $this->connector->isExists($this);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getAbsoluteName();
	}

	public function generatePrefix() {
		$this->prefix = Nette\Utils\Random::generate();
	}

	/************************* Deprecated **************************/

	/**
	 * @deprecated
	 */
	public function getImageClass() {
		trigger_error('getImageClass is deprecated, please use getNetteImageClass');

		return Nette\Utils\Image::fromFile($this->getAbsolutePath());
	}

	/**
	 * @deprecated
	 */
	public function getAbsoluteNamespace() {
		trigger_error('getAbsoluteNamespace is deprecated, please use getAbsoluteNamespacePath');

		return $this->assetsDir . $this->namespaceFolder();
	}
}
