<?php

namespace WebChemistry\Images;

use WebChemistry\Images\Connectors\Manager;

class NamespaceStorage extends Storage {

	/**
	 * @param string  $noImage
	 * @param array   $settings
	 * @param Manager $connectorManager
	 * @param string  $namespace
	 */
	public function __construct($noImage, $settings, Manager $connectorManager, $namespace = NULL) {
		parent::__construct($noImage, $settings, $connectorManager);

		$this->namespace = $namespace;
	}
}
