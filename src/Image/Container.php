<?php

namespace WebChemistry\Images\Image;

use Nette;
use WebChemistry\Images\Bridges\Nette\Image as BridgesImage;
use WebChemistry\Images\Bridges\Nette\Image;
use WebChemistry\Images\Connectors\IConnector;
use WebChemistry\Images\Helpers\IHelper;

class Container extends PropertyAccess {

	/** @var bool|string */
	private $hash = FALSE;

	/** @var IConnector */
	protected $connector;

	/** @var array */
	public $onCreate = array();

	/**
	 * @param IConnector $connector
	 */
	public function __construct(IConnector $connector) {
		$this->connector = $connector;
	}

	/**
	 * @param BridgesImage $image
	 */
	protected function wakeUpCallbacks(BridgesImage $image) {
		foreach ($this->onCreate as $callback) {
			$callback($image);
		}
	}

	/**
	 * @param Image $image
	 */
	protected function processHelpers(Image &$image) {
		foreach ($this->useHelpers as $parameters) {
			list($name, $parameter) = $parameters;

			if (is_object($name)) {
				$class = $name;
			} else {
				$class = new $name;
			}

			/** @var IHelper $class */
			$class->invoke($image, $parameter);
		}
	}

	private function generateHash() {
		if (!$this->useHelpers || $this->hash !== FALSE) {
			return $this->hash;
		}

		$this->hash = NULL;

		foreach ($this->useHelpers as $parameters) {
			$this->hash .= preg_replace('#\s+#', '', (is_object($parameters[0]) ? get_class($parameters[0]) : $parameters[0])) . preg_replace('#\s+#', '', $parameters[1]);
		}

		$this->hash = md5($this->hash);
	}

	/**
	 * @return Image
	 */
	public function getNetteImageClass() {
		return $this->getInfo($this)
					->getNetteImageClass();
	}

	/**
	 * @return bool
	 */
	public function isResize() {
		return $this->getWidth() || $this->getHeight() || $this->generateHash();
	}

	/**
	 * @return Info
	 */
	public function getOriginal() {
		$original = clone $this;
		$original->setWidth(NULL)
				 ->setHeight(NULL)
				 ->setFlag(NULL)
				 ->setHelperClasses([]);

		return new Info($original, $this->connector);
	}

	/**
	 * @return Info
	 */
	public function getInfo() {
		$this->generateHash();

		return new Info($this, $this->connector, $this->hash);
	}

	/**
	 * @return Info
	 */
	public function getUniqueImageName() {
		return $this->connector->getUniqueImageName($this->getInfo());
	}

	/************************* Deprecated **************************/

	/**
	 * @return Info
	 * @deprecated
	 */
	public function createInfo(IImage $class = NULL) {
		trigger_error('createInfo is deprecated, please use getInfo');

		return $this->getInfo();
	}

	/**
	 * @deprecated
	 */
	public function getUniqueImage() {
		trigger_error('getUniqueImage is deprecated, please use getUniqueImageName');

		return $this->getUniqueImageName();
	}
}
