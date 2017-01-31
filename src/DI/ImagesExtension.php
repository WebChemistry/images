<?php

namespace WebChemistry\Images\DI;


use Nette;
use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Image\ImageFactory;
use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Parsers\ModifierParser;
use WebChemistry\Images\Storages\CloudinaryStorage;
use WebChemistry\Images\Storages\LocalStorage;
use WebChemistry\Images\Template\IImageModifiers;
use WebChemistry\Images\Template\ImageFacade;
use WebChemistry\Images\Template\ImageModifiers;
use WebChemistry\Images\Template\Macros;

class ImagesExtension extends Nette\DI\CompilerExtension {

	/** @var array */
	public $defaults = [
		'local' => [
			'enable' => TRUE,
			'defaultImage' => NULL,
			'wwwDir' => NULL,
			'assetsDir' => 'assets',
			'modifiers' => [],
			'aliases' => [],
		],
		'cloudinary' => [
			'enable' => FALSE,
			'config' => [
				'apiKey' => NULL,
				'apiSecret' => NULL,
				'cloudName' => NULL,
				'secure' => FALSE,
			],
			'aliases' => [],
		],
		'default' => 'local',
	];

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		$builder->addDefinition($this->prefix('imageFactory'))
			->setClass(IImageFactory::class)
			->setFactory(ImageFactory::class);

		$builder->addDefinition($this->prefix('imageModifiers'))
			->setClass(IImageModifiers::class)
			->setFactory(ImageModifiers::class);

		// local
		if ($config['local']['enable']) {
			if ($config['local']['wwwDir'] === NULL) {
				$config['local']['wwwDir'] = $builder->parameters['wwwDir'];
			}

			$modifiers = $builder->addDefinition($this->prefix('modifiers.local'))
				->setClass(ModifierContainer::class)
				->setAutowired(FALSE);

			foreach ($config['local']['modifiers'] as $modifier) {
				$modifiers->addSetup('addLoader', [$modifier]);
			}
			foreach ($config['local']['aliases'] as $alias => $configuration) {
				$modifiers->addSetup('addAlias', [$alias, ModifierParser::parse($configuration)]);
			}

			$def = $builder->addDefinition($this->prefix('storage.local'))
				->setClass(IImageStorage::class)
				->setFactory(LocalStorage::class,
					[
						$config['local']['wwwDir'],
						$config['local']['assetsDir'],
						$modifiers,
						'@' . Nette\Http\Request::class,
						'@' . IImageFactory::class,
						$config['local']['defaultImage'],
					]
				);

			if ($config['default'] !== 'local') {
				$def->setAutowired(FALSE);
			}
		}

		// cloudinary
		if ($config['cloudinary']['enable']) {
			$modifiers = $builder->addDefinition($this->prefix('modifiers.cloudinary'))
				->setClass(ModifierContainer::class)
				->setAutowired(FALSE);

			foreach ($config['cloudinary']['aliases'] as $alias => $toParse) {
				$modifiers->addSetup('addAlias', [$alias, ModifierParser::parse($toParse)]);
			}

			$def = $builder->addDefinition($this->prefix('storage.cloudinary'))
				->setClass(IImageStorage::class)
				->setFactory(CloudinaryStorage::class, [
					$config['cloudinary']['config'], $modifiers
				]);

			if ($config['default'] !== 'cloudinary') {
				$def->setAutowired(FALSE);
			}
		}

		$builder->addDefinition($this->prefix('template.facade'))
			->setClass(ImageFacade::class);
	}

	public function beforeCompile() {
		$builder = $this->getContainerBuilder();

		$builder->getDefinition('nette.latteFactory')
			->addSetup(Macros::class . '::install(?->getCompiler())', ['@self']);
	}


}
