<?php

namespace WebChemistry\Images\DI;


use Doctrine\DBAL\Types\Type;
use Kdyby\Doctrine\Connection;
use Kdyby\Doctrine\DI\OrmExtension;
use Nette;
use WebChemistry\Images\Controls\UploadControl;
use WebChemistry\Images\Doctrine\ImageType;
use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Image\ImageFactory;
use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Parsers\ModifierParser;
use WebChemistry\Images\Storages\CloudinaryStorage;
use WebChemistry\Images\Storages\LocalStorage;
use WebChemistry\Images\Storages\S3Storage;
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
		's3' => [
			'enable' => FALSE,
			'defaultImage' => NULL,
			'namespaceBC' => FALSE,
			'config' => [
				'bucket' => null,
				'version' => 'latest',
				'region' => 'eu-west-1',
				'credentials' => [
					'key' => null,
					'secret' => null
				]
			],
			'aliases' => []
		],
		'default' => 'local',
		'registerControl' => TRUE,
		'registerType' => TRUE,
	];

	/** @var array */
	private $cfg = [];

	private function parseConfig() {
		if (!$this->cfg) {
			$this->cfg = $this->validateConfig($this->defaults);
			if ($this->cfg['local']['wwwDir'] === NULL) {
				$this->cfg['local']['wwwDir'] = $this->getContainerBuilder()->parameters['wwwDir'];
			}
		}

		return $this->cfg;
	}

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$config = $this->parseConfig();

		$builder->addDefinition($this->prefix('imageFactory'))
			->setClass(IImageFactory::class)
			->setFactory(ImageFactory::class);

		$builder->addDefinition($this->prefix('imageModifiers'))
			->setClass(IImageModifiers::class)
			->setFactory(ImageModifiers::class);

		// local
		if ($config['local']['enable']) {
			$modifiers = $builder->addDefinition($this->prefix('modifiers.local'))
				->setClass(ModifierContainer::class)
				->setAutowired(FALSE);

			foreach ($config['local']['modifiers'] as $name => $modifier) {
				if (!Nette\Utils\Strings::startsWith($modifier, '@')) {
					$modifier = $builder->addDefinition($this->prefix('modifier.' . $name))
						->setClass($modifier);
				}

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
						'@' . Nette\Http\IRequest::class,
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

		// AWS S3
		if($config['s3']['enable']){
			$modifiers = $builder->addDefinition($this->prefix('modifiers.s3'))
				->setClass(ModifierContainer::class)
				->setAutowired(FALSE);

			foreach ($config['s3']['aliases'] as $alias => $toParse) {
				$modifiers->addSetup('addAlias', [$alias, ModifierParser::parse($toParse)]);
			}

			$def = $builder->addDefinition($this->prefix('storage.s3'))
				->setClass(IImageStorage::class)
				->setFactory(S3Storage::class, [
					'config' => $config['s3']['config'],
					'modifierContainer' => $modifiers,
					'defaultImage' => $config['s3']['defaultImage'],
				]);

			if ($config['s3']['namespaceBC']) {
				$def->addSetup('setBackCompatibility');
			}

			if ($config['default'] !== 's3') {
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

		if (class_exists(Connection::class)) {
			foreach ($builder->findByTag(OrmExtension::TAG_CONNECTION) as $name => $_) {
				$builder->getDefinition($name)
					->addSetup('?->getDatabasePlatform()->registerDoctrineTypeMapping(?, ?)', ['@self', 'db_' . ImageType::TYPE, ImageType::TYPE]);
			}
		}
	}

	public function afterCompile(Nette\PhpGenerator\ClassType $class) {
		$init = $class->getMethods()['initialize'];

		if ($this->cfg['registerControl']) {
			$init->addBody(UploadControl::class . '::register();');
		}
		if ($this->cfg['registerType'] && class_exists(Type::class)) {
			$init->addBody(Type::class . '::addType(?, ?);', [ImageType::TYPE, ImageType::class]);
		}
	}

}
