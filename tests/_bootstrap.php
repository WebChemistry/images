<?php

require __DIR__ . '/../../../autoload.php';

/** START TEST */
@mkdir(__DIR__ . '/temp');
@mkdir(__DIR__ . '/temp/log');
/*foreach (\Nette\Utils\Finder::findFiles('*')->from(__DIR__ . '/temp') as $file) {
	@unlink((string) $file);
}*/
/** END TEST */

$configurator = new Nette\Configurator;

//$configurator->enableDebugger(__DIR__ . '/temp/log');
$configurator->setTempDirectory(__DIR__ . '/temp');

$configurator->createRobotLoader()
			 ->addDirectory(__DIR__ . '/../src')
			 ->addDirectory(__DIR__ . '/includes')
			 ->register();

if (file_exists(__DIR__ . '/_data/config.neon')) {
	$configurator->addConfig(__DIR__ . '/_data/config.neon');
}

$configurator->addParameters(array(
	'wwwDir' => __DIR__ . '/www',
	'dataDir' => __DIR__ . '/_data'
));

$container = $configurator->createContainer();

/** START TEST */
new E($container);
/** END TEST */
class E {

	/** @var \Nette\DI\Container */
	private static $container;

	public function __construct(\Nette\DI\Container $container) {
		self::$container = $container;
	}

	public static function createDirs($basePath, array $dirs = array()) {
		$basePath = self::directory($basePath);

		foreach ($dirs as $dir) {
			$explode = explode('/', str_replace('\\', '/', $dir));
			$base = $basePath;

			foreach ($explode as $create) {
				$base .= '/' . $create;
				@mkdir($base);
			}
		}
	}

	/**
	 * @param string $file
	 * @param array  $destinations
	 */
	public static function copy($file, array $destinations = array()) {
		$fileName = basename($file);
		$file = self::directory($file);

		foreach ($destinations as $dest) {
			copy($file, str_replace('%name%', $fileName, self::directory($dest)));
		}
	}

	/**
	 * @param string $directory
	 */
	public static function truncateDirectory($directory) {
		$directory = self::directory($directory);

		foreach (\Nette\Utils\Finder::findFiles('*')->from($directory) as $file) {
			@unlink((string) $file);
		}
	}

	/**
	 * @param $type
	 * @return object
	 */
	public static function getByType($type) {
		return self::$container->getByType($type);
	}

	public static function directory($dir) {
		return str_replace(array(
			'%www%',
			'%app%',
			'%temp%',
			'%data%',
			'%tempTest%',
			'%logTest%'
		), array(
			self::$container->parameters['wwwDir'],
			self::$container->parameters['appDir'],
			self::$container->parameters['tempDir'],
			self::$container->parameters['dataDir'],
			__DIR__ . '/temp',
			__DIR__ . '/temp/logs'
		), $dir);
	}

	public static function truncateTemp() {
		@mkdir(self::directory('%tempTest%'));
		@mkdir(self::directory('%logTest%'));

	}

	public static function dumpFile($name, $content) {
		@mkdir(self::directory('%data%/dumped'));

		$file = self::directory('%data%/dumped/' . $name . '.dmp');

		file_put_contents($file, $content);
	}

	public static function dumpedFile($name) {
		return self::directory('%data%/dumped/' . $name . '.dmp');
	}
}