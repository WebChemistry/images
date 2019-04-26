<?php declare(strict_types = 1);

namespace WebChemistry\Images\DI;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Nette;
use WebChemistry\Images\Controls\AdvancedUploadControl;
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

	public function getConfigSchema(): Nette\Schema\Schema {
		$parameters = $this->getContainerBuilder()->parameters;

		return Nette\Schema\Expect::structure([
			'enable' => Nette\Schema\Expect::bool(true),
			'defaultImage' => Nette\Schema\Expect::string(null),
			'wwwDir' => Nette\Schema\Expect::string($parameters['wwwDir']),
			'assetsDir' => Nette\Schema\Expect::string('assets'),
			'modifiers' => Nette\Schema\Expect::array(),
			'aliases' => Nette\Schema\Expect::array(),
			'hashResolver' => Nette\Schema\Expect::string(HashResolver::class),
			'namespaceResolver' => Nette\Schema\Expect::string(NamespaceResolver::class),
			'registerControl' => Nette\Schema\Expect::bool(true),
			'registerType' => Nette\Schema\Expect::bool(class_exists(Connection::class)),
			'safeLink' => Nette\Schema\Expect::bool(!$parameters['debugMode']),
			'storageClass' => Nette\Schema\Expect::string(LocalStorage::class),
		]);
	}

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		/** @var \stdClass $config */
		$config = $this->getConfig();

		// global
		$builder->addDefinition($this->prefix('imageFactory'))
			->setType(IImageFactory::class)
			->setFactory(ImageFactory::class);

		$builder->addDefinition($this->prefix('hashResolver'))
			->setType(IHashResolver::class)
			->setFactory($config->hashResolver);

		$builder->addDefinition($this->prefix('namespaceResolver'))
			->setType(INamespaceResolver::class)
			->setFactory($config->namespaceResolver);

		$builder->addDefinition($this->prefix('template.facade'))
			->setFactory(ImageFacade::class);

		// local
		if (!$config->enable) {
			return;
		}

		$modifiers = $builder->addDefinition($this->prefix('modifiers'))
			->setType(IModifiers::class)
			->setFactory(ModifierContainer::class)
			->setAutowired(false);

		$resourceMetaFactory = $builder->addDefinition($this->prefix('resourceMetaFactory'))
			->setType(IResourceMetaFactory::class)
			->setFactory(ResourceMetaFactory::class, [$modifiers])
			->setAutowired(false);

		$config->modifiers[] = BaseModifiers::class;

		DIHelper::addModifiersFromArray($modifiers, $config->modifiers);
		DIHelper::addAliasesFromArray($modifiers, $config->aliases);

		$builder->addDefinition($this->prefix('storage'))
			->setType(IImageStorage::class)
			->setFactory($config->storageClass,
				[
					'wwwDir' => $config->wwwDir,
					'assetsDir' => $config->assetsDir,
					'metaFactory' => $resourceMetaFactory,
					'defaultImage' => $config->defaultImage,
					'safeLink' => $config->safeLink,
				]
			);
	}

	public function beforeCompile() {
		$builder = $this->getContainerBuilder();
		/** @var \stdClass $config */
		$config = $this->getConfig();

		$def = $builder->getDefinition('nette.latteFactory');
		/** @var Nette\DI\ServiceDefinition $def */
		$def = DIHelper::fixFactoryDefinition($def);

		$def->addSetup(Macros::class . '::install(?->getCompiler())', ['@self'])
			->addSetup('addProvider', ['imageStorageFacade', $builder->getDefinition($this->prefix('template.facade'))]);

		// doctrine registration
		if ($config->registerType) {
			foreach ($builder->findByType(Connection::class) as $name => $_) {
				/** @var Nette\DI\Definitions\ServiceDefinition $conn */
				$conn = $builder->getDefinition($name);
				$conn->addSetup('if (!' . Type::class . '::hasType(?)) { ' . Type::class . '::addType(?, ?); }', [
					ImageType::TYPE, ImageType::TYPE, ImageType::class,
				]);
				$conn->addSetup('?->getDatabasePlatform()->registerDoctrineTypeMapping(?, ?)', ['@self', 'db_' . ImageType::TYPE, ImageType::TYPE]);
			}
		}
	}

	public function afterCompile(Nette\PhpGenerator\ClassType $class) {
		/** @var \stdClass $config */
		$config = $this->getConfig();
		$init = $class->getMethods()['initialize'];

		if ($config->registerControl) {
			$init->addBody(UploadControl::class . '::register();');
			$init->addBody(AdvancedUploadControl::class . '::register();');
		}
	}

}
