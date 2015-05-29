<?php

namespace WebChemistry\Images;

use WebChemistry\Images\Connectors\Manager;

class ConnectorStorage extends Storage {

	/**
	 * @param string  $noImage
	 * @param array   $settings
	 * @param Manager $connectorManager
	 * @param string  $connectorName
	 * @throws ImageStorageException
	 */
	public function __construct($noImage, $settings, Manager $connectorManager, $connectorName) {
		parent::__construct($noImage, $settings, $connectorManager);

		$this->connector = $connectorManager->getConnector($connectorName);
	}
}
