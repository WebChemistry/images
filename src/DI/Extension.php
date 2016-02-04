<?php

namespace WebChemistry\Images\DI;

use Nette;

class Extension extends Nette\DI\CompilerExtension {

	/** @var array */
	protected $defaults = [
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
		]
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
	 * @return array
	 */
	public function getSettings() {
		if (method_exists($this, 'validateConfig')) {
			$config = $this->validateConfig($this->defaults, $this->config);
			$config['wwwDir'] = Nette\DI\Helpers::expand($config['wwwDir'], $this->getContainerBuilder()->parameters);
		} else {
			$config = $this->getConfig($this->defaults); // deprecated
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

		if ($config['registration']['upload']) {
			$init->addBody('WebChemistry\Images\Controls\Upload::register();');
		}

		if ($config['registration']['multiUpload']) {
			$init->addBody('WebChemistry\Images\Controls\MultiUpload::register();');
		}
	}
}
