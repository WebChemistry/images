<?php declare(strict_types = 1);

namespace Test;

use Latte\Engine;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Nette\StaticClass;
use WebChemistry\Images\Facades\LocationFacade;
use WebChemistry\Images\Facades\StorageFacade;
use WebChemistry\Images\Filters\FilterRegistry;
use WebChemistry\Images\Filters\IFilterRegistry;
use WebChemistry\Images\Filters\ImageFilter;
use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Image\ImageFactory;
use WebChemistry\Images\Filters\IModifiers;
use WebChemistry\Images\Filters\ModifierContainer;
use WebChemistry\Images\Resolvers\DefaultImageResolver;
use WebChemistry\Images\Resolvers\HashResolver;
use WebChemistry\Images\Resolvers\IHashResolver;
use WebChemistry\Images\Resolvers\ImageSuffixResolver;
use WebChemistry\Images\Resolvers\NamespaceResolver;
use WebChemistry\Images\Resources\Meta\IResourceMetaFactory;
use WebChemistry\Images\Resources\Meta\ResourceMetaFactory;
use WebChemistry\Images\Storages\LocalStorage;
use WebChemistry\Images\Template\TemplateImageFacade;
use WebChemistry\Images\Template\Macros;
use WebChemistry\Images\Utils\FixOrientation;

class ObjectHelper {

	use StaticClass;

	public static function createStorage(string $wwwDir, string $assetsDir, IFilterRegistry $filterRegistry, array $defaults = [], ?IHashResolver $hashResolver = null): LocalStorage {
		$url = new UrlScript('http://example.com/');
		$request = new Request($url);
		$storageFacade = new StorageFacade($hashResolver ?: new HashResolver(), new NamespaceResolver(),
			$imageFactory = new ImageFactory(), new ImageFilter($filterRegistry));
		$locationFacade = new LocationFacade($wwwDir, $assetsDir, $storageFacade);
		$safeLinkFactory = new SafeLinkFactory(new DefaultImageResolver($defaults));

		return new LocalStorage(null, $request, $storageFacade, $locationFacade, $imageFactory, new ImageSuffixResolver(), $safeLinkFactory, new FixOrientation());
	}

	public static function createFilterRegistry(): IFilterRegistry {
		return new FilterRegistry();
	}

	public static function createHashResolver(): HashResolver {
		return new CustomHashResolver();
	}

	public static function createNamespaceResolver(): NamespaceResolver {
		return new NamespaceResolver();
	}

	public static function createServeFactory(?IModifiers $modifiers = null, ?IHashResolver $hashResolver = null): IResourceMetaFactory {
		return new ResourceMetaFactory(
			$modifiers ?: self::createFilterRegistry(), self::createImageFactory(), $hashResolver ?: self::createHashResolver(), self::createNamespaceResolver()
		);
	}

	public static function createImageFactory(): IImageFactory {
		return new ImageFactory();
	}

	public static function createLocalStorage(string $wwwDir, string $assetsDir, ?IResourceMetaFactory $serveFactory = null, ?string $defualtImage = null): LocalStorage {
		$url = new UrlScript('http://example.com/');
		$request = new Request($url);

		return new LocalStorage(
			$wwwDir, $assetsDir, null, $serveFactory ?: self::createServeFactory(), $request, self::createImageFactory(), false, $defualtImage
		);
	}

	public static function createLatte(IImageStorage $imageStorage): TemplateMock {
		$latte = new TemplateMock($engine = new Engine());
		Macros::install($engine->getCompiler());
		$latte->getEngine()->addProvider('imageStorageFacade',
			new TemplateImageFacade($imageStorage)
		);

		return $latte;
	}

}
