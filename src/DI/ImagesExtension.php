<?php

namespace WebChemistry\Images\DI;

use Nette;
use WebChemistry\Images\Controls\Checkbox;
use WebChemistry\Images\Controls\MultiUpload;
use WebChemistry\Images\Controls\Upload;
use WebChemistry\Images\FileStorage\FileStorage;
use WebChemistry\Images\Helpers\Crop;
use WebChemistry\Images\Helpers\IHelper;
use WebChemistry\Images\Helpers\Sharpen;
use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\ImageStorageException;
use WebChemistry\Images\Template\Macros;
use Nette\Utils\Validators;

class ImagesExtension extends Nette\DI\CompilerExtension {

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
			'crop' => Crop::class,
			'sharpen' => Sharpen::class
		],
		'checkbox' => [
			'caption' => NULL
		],
		'quality' => 85,
		'events' => [
			'onCreate' => [],
			'onSave' => [],
			'onUploadSave' => []
		],
		'imageStorage' => FileStorage::class
	];

	/** @var array */
	private $settings = [];

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$config = $this->getSettings();

		unset($config['events']);
		$builder->addDefinition($this->prefix('storage'))
				->setClass(IImageStorage::class)
				->setFactory($config['imageStorage'], [$config['defaultImage'], $config]);
	}

	public function beforeCompile() {
		$builder = $this->getContainerBuilder();
		$config = $this->getSettings();

		$storage = $builder->getDefinition($this->prefix('storage'));

		$builder->getDefinition('nette.latteFactory')
				->addSetup(Macros::class . '::install(?->getCompiler())', array('@self'));

		foreach ($config['events'] as $name => $events) {
			foreach ($events as $event) {
				if (strpos($event, '::') !== FALSE) {
					list($class, $method) = explode('::', $event);
					if (strpos($event, '@') !== FALSE) {
						$event = [$builder->getDefinition(substr($class, 1)), $method];
					} else {
						$event = [new $class, $method];
					}
				}
				$storage->addSetup('addEvent', [$event, $name]);
			}
		}
	}

	/**
	 * @throws ImageStorageException
	 * @return array
	 */
	public function getSettings() {
		if (!$this->settings) {
			$config = $this->validateConfig($this->defaults);
			$config['wwwDir'] = Nette\DI\Helpers::expand($config['wwwDir'], $this->getContainerBuilder()->parameters);

			// Validation
			$quality = $config['quality'];
			if (!is_int($quality) || !Validators::isInRange($quality, [0, 100])) {
				throw new ImageStorageException('Quality must be an integer from 0 to 100.');
			}
			foreach ($config['events'] as $name => &$array) {
				Validators::assert($array, 'array');
			}
			foreach ($config['helpers'] as $name => $class) {
				if (!class_exists($class) || $class instanceof IHelper) {
					throw new ImageStorageException("Helper $name must be instance of " . IHelper::class);
				}
			}

			$this->settings = $config;
		}

		return $this->settings;
	}

	/**
	 * @param Nette\PhpGenerator\ClassType $class
	 */
	public function afterCompile(Nette\PhpGenerator\ClassType $class) {
		$methods = $class->getMethods();
		$init = $methods['initialize'];
		$config = $this->getSettings();

		if ($config['registration']['upload'] && class_exists('Nette\Forms\Form')) {
			$init->addBody(Upload::class . '::register();');
		}
		if ($config['registration']['multiUpload'] && class_exists('Nette\Forms\Form')) {
			$init->addBody(MultiUpload::class . '::register();');
		}
		if ($config['checkbox']['caption']) {
			$init->addBody(Checkbox::class . '::$globalCaption = ?;', [$config['checkbox']['caption']]);
		}
	}

}
