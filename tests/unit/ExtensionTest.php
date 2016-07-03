<?php

class ExtensionTest extends \Codeception\TestCase\Test {

	/** @var \Nette\DI\Compiler */
	private $compiler;

	protected function _before() {
		$compiler = new \Nette\DI\Compiler();
		$compiler->addExtension('images', new \WebChemistry\Images\DI\ImagesExtension());
		$compiler->addExtension('http', new \Nette\Bridges\HttpDI\HttpExtension());
		$compiler->addExtension('latte', new \Nette\Bridges\ApplicationDI\LatteExtension(__DIR__ . '/temp'));
		$compiler->addConfig([
			'parameters' => [
				'wwwDir' => __DIR__
			]
		]);
		$this->compiler = $compiler;
	}

	public function testCompile() {
		$this->compiler->compile();
	}

	public function testGettingImageStorage() {
		eval($this->compiler->compile());
		/** @var \Nette\DI\Container $container */
		$container = new Container();
		$this->assertNotNull($class = $container->getByType(\WebChemistry\Images\IImageStorage::class));
		$this->assertInstanceOf(\WebChemistry\Images\IImageStorage::class, $class);
		$this->assertInstanceOf(\WebChemistry\Images\AbstractStorage::class, $class);
	}

}
