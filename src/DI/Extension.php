<?php

namespace WebChemistry\Images\DI;

use Nette;
use WebChemistry\Images\ImageStorageException;

class Extension extends Nette\DI\CompilerExtension {

	/** @var array */
	public $defaults = [
		'defaultImage' => 'default/default.png',
		'registration' => [
			'upload' => TRUE,
			'multiUpload' => TRUE
		],
		'assetsDir' => 'assets',
		'wwwDir' => '%wwwDir%',
		'helpers' => [
			'crop' => 'WebChemistry\Images\Helpers\Crop',
			'sharpen' => 'WebChemistry\Images\Helpers\Sharpen'
		],
		'checkbox' => [
			'caption' => NULL
		],
		'quality' => 85
	];

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$config = $this->getSettings();

		$builder->addDefinition($this->prefix('storage'))
				->setClass('WebChemistry\Images\AbstractStorage')
				->setFactory('WebChemistry\Images\FileStorage\FileStorage', [$config['defaultImage'], $config]);
	}

	public function beforeCompile() {
		$builder = $this->getContainerBuilder();

		$builder->getDefinition('nette.latteFactory')
				->addSetup('WebChemistry\Images\Template\Macros::install(?->getCompiler())', array('@self'));
	}

	/**
	 * @throws ImageStorageException
	 * @return array
	 */
	public function getSettings() {
		if (method_exists($this, 'validateConfig')) {
			$config = $this->validateConfig($this->defaults, $this->config);
			$config['wwwDir'] = Nette\DI\Helpers::expand($config['wwwDir'], $this->getContainerBuilder()->parameters);
		} else {
			$config = $this->getConfig($this->defaults); // deprecated
		}
		
		// Validation
		$quality = $config['quality'];
		if (!is_int($quality) || $quality < 0 || $quality > 100) {
			throw new ImageStorageException('Quality must be an integer from 0 to 100.');
		}

		return $config;
	}

	/**
	 * @param Nette\PhpGenerator\ClassType $class
	 */
	public function afterCompile(Nette\PhpGenerator\ClassType $class) {
		$methods = $class->getMethods();
		$init = $methods['initialize'];

		$config = $this->getSettings();

		if ($config['registration']['upload'] && class_exists('Nette\Forms\Form')) {
			$init->addBody('WebChemistry\Images\Controls\Upload::register();');
		}

		if ($config['registration']['multiUpload'] && class_exists('Nette\Forms\Form')) {
			$init->addBody('WebChemistry\Images\Controls\MultiUpload::register();');
		}

		if ($config['checkbox']['caption']) {
			$init->addBody('WebChemistry\Images\Controls\Checkbox::$globalCaption = ?;', [$config['checkbox']['caption']]);
		}
	}
}
