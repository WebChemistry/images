<?php declare(strict_types = 1);

namespace WebChemistry\Images\DI;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Connection;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Definition;
use Nette\DI\Statement;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use WebChemistry\Images\Controls\AdvancedUploadControl;
use WebChemistry\Images\Controls\UploadControl;
use WebChemistry\Images\Doctrine\ImageType;
use WebChemistry\Images\Facades\LocationFacade;
use WebChemistry\Images\Facades\StorageFacade;
use WebChemistry\Images\Filters\IImageFilter;
use WebChemistry\Images\Filters\ImageFilter;
use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Filters\IFilterRegistry;
use WebChemistry\Images\Filters\FilterRegistry;
use WebChemistry\Images\Image\ImageFactory;
use WebChemistry\Images\Resolvers\DefaultImageResolver;
use WebChemistry\Images\Resolvers\HashResolver;
use WebChemistry\Images\Resolvers\IDefaultImageResolver;
use WebChemistry\Images\Resolvers\IHashResolver;
use WebChemistry\Images\Resolvers\IImageSuffixResolver;
use WebChemistry\Images\Resolvers\ImageSuffixResolver;
use WebChemistry\Images\Resolvers\INamespaceResolver;
use WebChemistry\Images\Resolvers\NamespaceResolver;
use WebChemistry\Images\Storages\LocalStorage;
use WebChemistry\Images\Template\TemplateImageFacade;
use WebChemistry\Images\Template\Macros;
use WebChemistry\Images\Utils\FixOrientation;
use WebChemistry\Images\Utils\ISafeLinkFactory;
use WebChemistry\Images\Utils\SafeLink;

final class ImagesExtension extends CompilerExtension {

	protected function expectService(string $default): Schema {
		return Expect::anyOf(Expect::string(), Expect::type(Statement::class), Expect::type(Definition::class))
				->default($default);
	}

	public function getConfigSchema(): Schema {
		$parameters = $this->getContainerBuilder()->parameters;

		return Expect::structure([
			'classes' => Expect::structure([
				'hashResolver' => $this->expectService(HashResolver::class),
				'namespaceResolver' => $this->expectService(NamespaceResolver::class),
				'imageFactory' => $this->expectService(ImageFactory::class),
				'filterRegistry' => $this->expectService(FilterRegistry::class),
				'imageFilter' => $this->expectService(ImageFilter::class),
				'imageSuffixResolver' => $this->expectService(ImageSuffixResolver::class),
				'storage' => $this->expectService(LocalStorage::class),
				'defaultImageResolver' => $this->expectService(DefaultImageResolver::class),
			]),
			'registration' => Expect::structure([
				'formControls' => Expect::bool(true),
				'type' => Expect::bool(class_exists(Type::class) && class_exists(Connection::class)),
			]),
			'defaultImages' => Expect::arrayOf('string'),
			'enable' => Expect::bool(true),
			'wwwDir' => Expect::string($parameters['wwwDir']),
			'assetsDir' => Expect::string('assets')->nullable(),
			'safeLink' => Expect::bool(!$parameters['debugMode']),
		]);
	}

	public function loadConfiguration() {
		$this->registerGlobal();
		$this->registerLocal();
	}

	private function registerGlobal(): void {
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();
		$classes = $config->classes;

		$builder->addFactoryDefinition($this->prefix('safeLinkFactory'))
			->setImplement(ISafeLinkFactory::class)
			->getResultDefinition()
				->setFactory(SafeLink::class, [
					'safeLink' => $config->safeLink,
				]);

		$builder->addDefinition($this->prefix('fixOrientation'))
			->setType(FixOrientation::class);

		$builder->addDefinition($this->prefix('imageFactory'))
			->setType(IImageFactory::class)
			->setFactory($classes->imageFactory);

		$builder->addDefinition($this->prefix('hashResolver'))
			->setType(IHashResolver::class)
			->setFactory($classes->hashResolver);

		$builder->addDefinition($this->prefix('namespaceResolver'))
			->setType(INamespaceResolver::class)
			->setFactory($classes->namespaceResolver);

		$builder->addDefinition($this->prefix('template.facade'))
			->setFactory(TemplateImageFacade::class);

		$builder->addDefinition($this->prefix('filterRegistry'))
			->setType(IFilterRegistry::class)
			->setFactory($classes->filterRegistry);

		$builder->addDefinition($this->prefix('imageFilter'))
			->setType(IImageFilter::class)
			->setFactory($classes->imageFilter);

		$builder->addDefinition($this->prefix('imageSuffixResolver'))
			->setType(IImageSuffixResolver::class)
			->setFactory($classes->imageSuffixResolver);

		$builder->addDefinition($this->prefix('defaultImageResolver'))
			->setType(IDefaultImageResolver::class)
			->setFactory($classes->defaultImageResolver, [
				'defaults' => $config->defaultImages,
			]);
	}

	private function registerLocal(): void {
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();
		if (!$config->enable) {
			return;
		}

		$builder->addDefinition($this->prefix('locationFacade'))
			->setFactory(LocationFacade::class, [
				'wwwDir' => $config->wwwDir,
				'assetsDir' => $config->assetsDir,
			]);

		$builder->addDefinition($this->prefix('storageFacade'))
			->setType(StorageFacade::class);

		$builder->addDefinition($this->prefix('storage'))
			->setType(IImageStorage::class)
			->setFactory($config->classes->storage);
	}

	public function beforeCompile() {
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$builder->getDefinition('nette.latteFactory')
			->getResultDefinition()
				->addSetup('?::install(?->getCompiler())', [Macros::class, '@self'])
				->addSetup('addProvider', ['imageStorageFacade', $builder->getDefinition($this->prefix('template.facade'))]);

		// doctrine registration
		if ($config->registration->type) {
			foreach ($builder->findByType(Connection::class) as $def) {
				$def->addSetup('?::register(?);', [ImageType::class, '@self']);
			}
		}
	}

	public function afterCompile(ClassType $class) {
		$config = $this->getConfig();
		$init = $class->getMethods()['initialize'];

		if ($config->registration->formControls) {
			$init->addBody(UploadControl::class . '::register();');
			$init->addBody(AdvancedUploadControl::class . '::register();');
		}
	}

}
