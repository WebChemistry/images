<?php
namespace WebChemistry\Images\Tests;

use Nette\Bridges\ApplicationDI\LatteExtension;
use Nette\Bridges\HttpDI\HttpExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use WebChemistry\Images\DI\ImagesExtension;
use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Utils\ISafeLinkFactory;
use WebChemistry\Images\Utils\SafeLink;
use WebChemistry\Testing\TUnitTest;

class CompilerTest extends \Codeception\Test\Unit {

	use TUnitTest;

	/** @var Container */
	private $container;

	protected function _before() {
		@mkdir(__DIR__ . '/output');

		$loader = new ContainerLoader(__DIR__ . '/output', true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addConfig([
				'parameters' => [
					'wwwDir' => __DIR__ . '/output',
					'debugMode' => true,
				]
			]);

			$compiler->addExtension('images', new ImagesExtension());
			$compiler->addExtension('latte', new LatteExtension(__DIR__ . '/output', true));
			$compiler->addExtension('http', new HttpExtension());
		});

		$this->container = new $class();
	}

	protected function _after() {
		$this->services->fileSystem->removeDirRecursive(__DIR__ . '/output');
	}

	// tests
	public function testInstanceOf() {
		$this->assertInstanceOf(IImageStorage::class, $this->container->getByType(IImageStorage::class));
	}

	public function testSafeLinkFactory() {
		$factory = $this->container->getByType(ISafeLinkFactory::class);
		$this->assertInstanceOf(ISafeLinkFactory::class, $factory);
		$this->assertInstanceOf(SafeLink::class, $factory->create(function (): void {}));
	}

}
