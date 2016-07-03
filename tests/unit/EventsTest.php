<?php

class EventsTest extends \Codeception\TestCase\Test {

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
			],
			'services' => [
				'e' => _Events::class
			],
			'images' => [
				'imageStorage' => _MockStorage::class,
				'events' => [
					'onCreate' => [
						'@e::event',
						'_Events::event'
					]
				]
			]
		]);
		$this->compiler = $compiler;
	}

	public function testEvents() {
		$this->compiler->setClassName('Container_Events');
		eval($this->compiler->compile());
		/** @var \Nette\DI\Container $container */
		$container = new Container_Events();
		/** @var _MockStorage $class */
		$this->assertNotNull($class = $container->getByType(\WebChemistry\Images\IImageStorage::class));
		$this->assertInstanceOf(\WebChemistry\Images\IImageStorage::class, $class);
		$this->assertInstanceOf(_MockStorage::class, $class);

		$events = $class->getEvents()['onCreate'];
		$this->assertTrue(is_callable($events[0]));
		$events[0]();
		$this->assertTrue(_Events::$called);
		_Events::$called = FALSE;
		$this->assertTrue(is_callable($events[1]));
		$events[1]();
		$this->assertTrue(_Events::$called);
	}

}

class _Events {

	public static $called = FALSE;

	public function event() {
		self::$called = TRUE;
	}

}

class _MockStorage implements \WebChemistry\Images\IImageStorage {

	/** @var array */
	private $events;

	public function __construct($foo, array $bar) {
	}

	public function addEvent(callable $callback, $name) {
		$this->events[$name][] = $callback;
	}

	/**
	 * @return array
	 */
	public function getEvents() {
		return $this->events;
	}

	public function createImage() {
	}

	public function get($absoluteName, $size = NULL, $flag = NULL, $defaultImage = NULL, callable $callback = NULL) {
	}

	public function saveUpload(\Nette\Http\FileUpload $fileUpload, $namespace = NULL, callable $callback = NULL) {
	}

	public function saveImage(\Nette\Utils\Image $image, $fileName, $namespace = NULL, callable $callback = NULL) {
	}

	public function delete($absoluteName) {
	}

}
