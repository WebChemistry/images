<?php declare(strict_types = 1);

namespace Test;

use Latte\Engine;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Nette\StaticClass;
use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Image\ImageFactory;
use WebChemistry\Images\Modifiers\IModifiers;
use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Resolvers\HashResolver;
use WebChemistry\Images\Resolvers\IHashResolver;
use WebChemistry\Images\Resolvers\NamespaceResolver;
use WebChemistry\Images\Resources\Meta\IResourceMetaFactory;
use WebChemistry\Images\Resources\Meta\ResourceMetaFactory;
use WebChemistry\Images\Storages\LocalStorage;
use WebChemistry\Images\Template\ImageFacade;
use WebChemistry\Images\Template\Macros;

class ObjectHelper {

	use StaticClass;

	public static function createModifiers(): ModifierContainer {
		return new ModifierContainer();
	}

	public static function createHashResolver(): HashResolver {
		return new CustomHashResolver();
	}

	public static function createNamespaceResolver(): NamespaceResolver {
		return new NamespaceResolver();
	}

	public static function createServeFactory(?IModifiers $modifiers = null, ?IHashResolver $hashResolver = null): IResourceMetaFactory {
		return new ResourceMetaFactory(
			$modifiers ?: self::createModifiers(), self::createImageFactory(), $hashResolver ?: self::createHashResolver(), self::createNamespaceResolver()
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
			new ImageFacade($imageStorage)
		);

		return $latte;
	}

}
