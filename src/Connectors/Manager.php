<?php

namespace WebChemistry\Images\Connectors;

use Nette;
use WebChemistry\Images\ImageStorageException;

class Manager extends Nette\Object {

	private $connectors = [];

	/**
	 * @param string     $name
	 * @param IConnector $connector
	 */
	public function addConnector($name, IConnector $connector, array $settings = array()) {
		$connector->setSettings($settings);

		$this->connectors[$name] = $connector;
	}

	/**
	 * @param string $name
	 * @return IConnector
	 * @throws ImageStorageException
	 */
	public function getConnector($name) {
		if (!isset($this->connectors[$name])) {
			throw new ImageStorageException("Connector '$name' is not exists.");
		}

		return $this->connectors[$name];
	}
}