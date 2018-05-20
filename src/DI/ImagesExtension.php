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
use WebChemistry\Images\Modifiers\IModifiers;
use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Parsers\ModifierParser;
use WebChemistry\Images\Parsers\Values;
use WebChemistry\Images\Storages\CloudinaryStorage;
use WebChemistry\Images\Storages\LocalStorage;
use WebChemistry\Images\Storages\S3Storage;
use WebChemistry\Images\Template\ImageFacade;
use WebChemistry\Images\Template\Macros;

class ImagesExtension extends Nette\DI\CompilerExtension {

	/** @var array */
	public $defaults = [
		'local' => [
			'enable' => true,
			'defaultImage' => null,
			'wwwDir' => null,
			'assetsDir' => 'assets',
			'modifiers' => [],
			'aliases' => [],
		],
		'cloudinary' => [
			'enable' => false,
			'config' => [
				'apiKey' => null,
				'apiSecret' => null,
				'cloudName' => null,
				'secure' => false,
			],
			'aliases' => [],
		],
		's3' => [
			'enable' => false,
			'defaultImage' => null,
			'namespaceBC' => false,
			'config' => [
				'bucket' => null,
				'version' => 'latest',
				'region' => 'eu-west-1',
				'credentials' => [
					'key' => null,
					'secret' => null
				]
			],
			'aliases' => [],
			'modifiers' => [],
		],
		'default' => 'local',
		'registerControl' => true,
		'registerType' => true,
	];

	/** @var array */
	private $cfg = [];

	private function parseConfig() {
		if (!$this->cfg) {
			$this->cfg = $this->validateConfig($this->defaults);
			if ($this->cfg['local']['wwwDir'] === null) {
				$this->cfg['local']['wwwDir'] = $this->getContainerBuilder()->parameters['wwwDir'];
			}
		}

		return $this->cfg;
	}

	/**
	 * @param Nette\DI\ServiceDefinition $modifiers
	 * @param array $services
	 * @param string $prefix
	 */
	protected function addModifiersFromArray(Nette\DI\ServiceDefinition $modifiers, array $services, $prefix) {
		$builder = $this->getContainerBuilder();

		foreach ($services as $name => $modifier) {
			if (!Nette\Utils\Strings::startsWith($modifier, '@')) {
				$modifier = $builder->addDefinition($this->prefix($prefix . '.' . $name))
					->setFactory($modifier);
			}

			$modifiers->addSetup('addLoader', [$modifier]);
		}
	}

	/**
	 * @param Nette\DI\ServiceDefinition $modifiers
	 * @param array $aliases
	 */
	protected function addAliasesFromArray(Nette\DI\ServiceDefinition $modifiers, array $aliases) {
		foreach ($aliases as $alias => $configuration) {
			$parsed = ModifierParser::parse($configuration);
			$parsed = new Nette\DI\Statement(Values::class, [$parsed->getValues(), $parsed->getVariables()]);

			$modifiers->addSetup('addAlias', [$alias, $parsed]);
		}
	}

	/**
	 * @param string $suffix
	 * @return Nette\DI\ServiceDefinition
	 */
	protected function createModifiers($suffix) {
		return $this->getContainerBuilder()->addDefinition($this->prefix('modifiers.' . $suffix))
			->setType(IModifiers::class)
			->setFactory(ModifierContainer::class)
			->setAutowired(false);
	}

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$config = $this->parseConfig();

		$builder->addDefinition($this->prefix('imageFactory'))
			->setType(IImageFactory::class)
			->setFactory(ImageFactory::class);

		// local
		if ($config['local']['enable']) {
			$modifiers = $this->createModifiers('local');

			// Modifiers and aliases
			$this->addModifiersFromArray($modifiers, $config['local']['modifiers'], 'modifier');
			$this->addAliasesFromArray($modifiers, $config['local']['aliases']);

			$def = $builder->addDefinition($this->prefix('storage.local'))
				->setType(IImageStorage::class)
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
				$def->setAutowired(false);
			}
		}

		// cloudinary
		if ($config['cloudinary']['enable']) {
			$modifiers = $this->createModifiers('cloudinary');

			// Aliases
			$this->addAliasesFromArray($modifiers, $config['cloudinary']['aliases']);

			$def = $builder->addDefinition($this->prefix('storage.cloudinary'))
				->setType(IImageStorage::class)
				->setFactory(CloudinaryStorage::class, [
					$config['cloudinary']['config'], $modifiers
				]);

			if ($config['default'] !== 'cloudinary') {
				$def->setAutowired(false);
			}
		}

		// AWS S3
		if($config['s3']['enable']){
			$modifiers = $this->createModifiers('s3');

			// Modifiers and aliases
			$this->addModifiersFromArray($modifiers, $config['s3']['modifiers'], 's3.modifier');
			$this->addAliasesFromArray($modifiers, $config['s3']['aliases']);

			$def = $builder->addDefinition($this->prefix('storage.s3'))
				->setType(IImageStorage::class)
				->setFactory(S3Storage::class, [
					'config' => $config['s3']['config'],
					'modifierContainer' => $modifiers,
					'defaultImage' => $config['s3']['defaultImage'],
				]);

			if ($config['s3']['namespaceBC']) {
				$def->addSetup('setBackCompatibility');
			}

			if ($config['default'] !== 's3') {
				$def->setAutowired(false);
			}
		}

		$builder->addDefinition($this->prefix('template.facade'))
			->setFactory(ImageFacade::class);
	}

	public function beforeCompile() {
		$builder = $this->getContainerBuilder();

		$builder->getDefinition('nette.latteFactory')
			->addSetup(Macros::class . '::install(?->getCompiler())', ['@self'])
			->addSetup('addProvider', ['imageStorageFacade', $builder->getDefinition($this->prefix('template.facade'))]);

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
