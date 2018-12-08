<?php declare(strict_types = 1);

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
use WebChemistry\Images\Modifiers\BaseModifiers;
use WebChemistry\Images\Modifiers\IModifiers;
use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Resolvers\HashResolver;
use WebChemistry\Images\Resolvers\IHashResolver;
use WebChemistry\Images\Resolvers\INamespaceResolver;
use WebChemistry\Images\Resolvers\NamespaceResolver;
use WebChemistry\Images\Resources\Meta\IResourceMetaFactory;
use WebChemistry\Images\Resources\Meta\ResourceMetaFactory;
use WebChemistry\Images\Storages\LocalStorage;
use WebChemistry\Images\Template\ImageFacade;
use WebChemistry\Images\Template\Macros;

class ImagesExtension extends Nette\DI\CompilerExtension {

	/** @var array */
	public $defaults = [
		'local' => [ // deprecated
		],
		'enable' => true,
		'defaultImage' => null,
		'wwwDir' => null,
		'assetsDir' => 'assets',
		'modifiers' => [],
		'aliases' => [],
		'hashResolver' => HashResolver::class,
		'namespaceResolver' => NamespaceResolver::class,
		'default' => null, // deprecated
		'registerControl' => true,
		'registerType' => true,
	];

	private function parseConfig(): array {
		$config = $this->validateConfig($this->defaults);
		if ($config['local']) {
			throw new Nette\DeprecatedException('ImageStorage: "local" section is deprecated.');
		}
		if ($config['wwwDir'] === null) {
			$config['local']['wwwDir'] = $this->getContainerBuilder()->parameters['wwwDir'];
		}

		return $config;
	}

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$config = $this->parseConfig();

		// global
		$imageFactory = $builder->addDefinition($this->prefix('imageFactory'))
			->setType(IImageFactory::class)
			->setFactory(ImageFactory::class);

		$builder->addDefinition($this->prefix('hashResolver'))
			->setType(IHashResolver::class)
			->setFactory($config['hashResolver']);

		$builder->addDefinition($this->prefix('namespaceResolver'))
			->setType(INamespaceResolver::class)
			->setFactory($config['namespaceResolver']);

		$resourceMetaFactory = $builder->addDefinition($this->prefix('resourceMetaFactory'))
			->setType(IResourceMetaFactory::class)
			->setFactory(ResourceMetaFactory::class);

		$builder->addDefinition($this->prefix('template.facade'))
			->setFactory(ImageFacade::class);

		// local
		if (!$config['enable']) {
			return;
		}

		$modifiers = $builder->addDefinition($this->prefix('modifiers.local'))
			->setType(IModifiers::class)
			->setFactory(ModifierContainer::class)
			->setAutowired(false);

		$config['modifiers'][] = BaseModifiers::class;

		DIHelper::addModifiersFromArray($modifiers, $config['modifiers']);
		DIHelper::addAliasesFromArray($modifiers, $config['aliases']);

		$builder->addDefinition($this->prefix('storage.local'))
			->setType(IImageStorage::class)
			->setFactory(LocalStorage::class,
				[
					$config['local']['wwwDir'],
					$config['local']['assetsDir'],
					'@' . Nette\Http\IRequest::class,
					$imageFactory,
					$resourceMetaFactory,
					$config['local']['defaultImage'],
				]
			);
	}

	public function beforeCompile() {
		$builder = $this->getContainerBuilder();

		$def = $builder->getDefinition('nette.latteFactory');
		$def = DIHelper::fixFactoryDefinition($def);

		$def->addSetup(Macros::class . '::install(?->getCompiler())', ['@self'])
			->addSetup('addProvider', ['imageStorageFacade', $builder->getDefinition($this->prefix('template.facade'))]);

		// kdyby registration
		if (class_exists(Connection::class)) {
			foreach ($builder->findByTag(OrmExtension::TAG_CONNECTION) as $name => $_) {
				$builder->getDefinition($name)
					->addSetup('?->getDatabasePlatform()->registerDoctrineTypeMapping(?, ?)', ['@self', 'db_' . ImageType::TYPE, ImageType::TYPE]);
			}
		}
	}

	public function afterCompile(Nette\PhpGenerator\ClassType $class) {
		$config = $this->getConfig();
		$init = $class->getMethods()['initialize'];

		if ($config['registerControl']) {
			$init->addBody(UploadControl::class . '::register();');
		}
		if ($config['registerType'] && class_exists(Type::class)) {
			$init->addBody(Type::class . '::addType(?, ?);', [ImageType::TYPE, ImageType::class]);
		}
	}

}
