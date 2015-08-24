<?php

namespace WebChemistry\Images\DI;

use Nette;
use WebChemistry\Images\ImageStorageException;

class Extension extends Nette\DI\CompilerExtension {

	protected $defaults = array(
		'noimage' => 'noimage/noimage.png',
		'registration' => array(
			'texy' => FALSE,
			'upload' => TRUE,
			'multiUpload' => FALSE,
			'presenter' => TRUE
		),
		'assetsDir' => 'assets',
		'wwwDir' => '%wwwDir%',
		'settings' => array(
			'connectors' => array(
				// TODO: Future update
			),
			'upload' => array(
				'label' => 'Delete this image?'
			),
			'helpers' => array(
				'crop' => 'WebChemistry\Images\Helpers\Crop',
				'sharpen' => 'WebChemistry\Images\Helpers\Sharpen',
				'quality' => 'WebChemistry\Images\Helpers\Quality'
			)
		),
		'router' => array(
			'mask' => 'show-image/<name>[/<size>[/<flag>[/<noimage>]]]',
			'resize' => FALSE,
			'flag' => 0,
			'disable' => FALSE
		)
	);

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();

		$config = $this->getSettings();

		$builder->addDefinition($this->prefix('connector.localhost'))
				->setClass('WebChemistry\Images\Connectors\Localhost', array(
					$config['wwwDir'], $config['assetsDir']
				));

		$manager = $builder->addDefinition($this->prefix('manager'))
						   ->addSetup('addConnector', array('default', $this->prefix('@connector.localhost')))
						   ->addSetup('addConnector', array('localhost', $this->prefix('@connector.localhost')))
						   ->setClass('WebChemistry\Images\Connectors\Manager');

		foreach ($config['settings']['connectors'] as $name => $settings) {
			if (!$settings['class']) {
				throw new ImageStorageException("Missing section for connector '$name'");
			}

			if (!is_object($settings['class'])) {
				$settings['class'] = new $settings['class'];
			}

			$manager->addSetup('addConnector', array(
				$name, $settings['class'], isset($settings['settings']) ? $settings['settings'] : array()
			));
		}

		$builder->addDefinition($this->prefix('storage'))
				->setClass('WebChemistry\Images\Storage', array(
					$config['noimage'], $config['settings'], $this->prefix('@manager')
				));

		if ($config['router']['disable'] === FALSE) {
			$builder->addDefinition($this->prefix('routerFactory'))
					->setFactory('WebChemistry\Images\Router\Factory::createRouter')
					->setClass('Nette\Application\IRouter')
					->setArguments(array($config['router']))
					->setAutowired(FALSE);

			if ($config['registration']['presenter']) {
				$builder->addDefinition($this->prefix('presenter'))
						->setClass('WebChemistry\Images\Addons\GeneratePresenter', array($config['router']['resize']));
			}
		}
	}

	public function beforeCompile() {
		$builder = $this->getContainerBuilder();

		$config = $this->getSettings();

		$builder->getDefinition('nette.latteFactory')
				->addSetup('WebChemistry\Images\Macros\Macros::install(?->getCompiler())', array('@self'));

		if ($config['router']['disable'] === FALSE) {
			$builder->getDefinition('router')
					->addSetup('WebChemistry\Images\Router\Factory::prepend($service, ?)', array($this->prefix('@routerFactory')));

			$builder->getDefinition('nette.presenterFactory')
					->addSetup('setMapping', array(
						array('ImageStorage' => 'WebChemistry\Images\Addons\*Presenter')
					));
		}
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

	public function afterCompile(Nette\PhpGenerator\ClassType $class) {
		$methods = $class->getMethods();
		$init = $methods['initialize'];

		$config = $this->getSettings();

		if ($config['registration']['texy']) {
			$init->addBody('WebChemistry\Images\Texy::register($this->getByType(?), $this->getService(?), $this->getByType(?)->getUrl()->getBaseUrl());', array(
				'Texy', $this->prefix('storage'), 'Nette\Http\IRequest'
			));
		}

		if ($config['registration']['upload']) {
			$init->addBody('WebChemistry\Images\Addons\UploadControl::register();');
		}

		if ($config['registration']['multiUpload']) {
			$init->addBody('WebChemistry\Images\Addons\MultiUploadControl::register();');
		}
	}
}
