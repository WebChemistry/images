<?php

class Environment {
	/** @var \Nette\DI\Container */
	private static $container;

	private static $dirs = [
		'data' => NULL,
		'www' => NULL,
		'temp' => NULL
	];

	private static $params = [];

	private static $debug;

	public function set($name, $value) {
		self::$params[$name] = $value;
	}

	public function get($name) {
		return self::$params[$name];
	}

	public static function dumpToFile($file, $content) {
		@mkdir(self::getTempDir('/dumps'));

		file_put_contents(self::getTempDir('/dumps/' . $file . '.dmp'), $content);
	}

	public static function isDebug() {
		return self::$debug;
	}

	public static function initNette(array $robotLoader = [], $debug = FALSE) {
		$configurator = new Nette\Configurator;

		self::$debug = $debug;

		self::createDirs(__DIR__ . '/../temp');

		$configurator->setTempDirectory(self::getTempDir());

		if ($debug === TRUE) {
			$configurator->enableDebugger(self::getLogDir());
		}

		foreach($robotLoader as $path) {
			$configurator->createRobotLoader()
				->addDirectory($path)
				->register();
		}

		self::setConfigurator($configurator);

		$configurator->addConfig(self::getDataDir('/test.config.neon'));

		$container = $configurator->createContainer();

		self::setContainer($container);
	}

	public static function getContainer() {
		return self::$container;
	}

	public static function setConfigurator(Nette\Configurator $configurator) {
		$configurator->addParameters([
			'wwwDir' => self::getWwwDir()
		]);
	}

	private static function createDirs($tempDir) {
		self::$dirs = [
			'www' => __DIR__ . '/../www',
			'temp' => $tempDir,
			'data' => __DIR__ . '/../_data',
			'log' => __DIR__ . '/../temp/log'
		];

		foreach (self::$dirs as $path) {
			@mkdir($path);
		}
	}

	public static function getLogDir($path = NULL) {
		return self::$dirs['log'] . $path;
	}

	public static function getWwwDir($path = NULL) {
		return self::$dirs['www'] . $path;
	}

	public static function getDataDir($path = NULL) {
		return self::$dirs['data'] . $path;
	}

	public static function getTempDir($path = NULL) {
		return self::$dirs['temp'] . $path;
	}

	public static function setContainer(\Nette\DI\Container  $container) {
		self::$container = $container;

		self::createDirs($container->parameters['tempDir']);
	}

	public static function getByType($type) {
		return self::$container->getByType($type);
	}

	public static function cleanFrom($path) {
		foreach (\Nette\Utils\Finder::findFiles('*')->from(self::getWwwDir($path)) as $row) {
			unlink($row);
		}
		$dirs = iterator_to_array(\Nette\Utils\Finder::findDirectories('*')->from(self::getWwwDir($path)));

		usort($dirs, function($a, $b) {
			return strlen($b) - strlen($a);
		});

		foreach ($dirs as $row) {
			rmdir($row);
		}
	}

	public static function copy($original, array $paths, $basePath = NULL) {
		$original = self::getWwwDir($original);
		$origName = basename($original);
		$wwwDir = self::getWwwDir($basePath);
		@mkdir($wwwDir);
		foreach ($paths as $name => $path) {
			$dirs = explode('/', $path);
			$baseDir = '/';

			foreach ($dirs as $dir) {
				$baseDir .= $dir . '/';
				@mkdir($wwwDir . $baseDir);
			}
			$copyPath = $wwwDir . $baseDir . (!is_numeric($name) ? $name : $origName);
			if (!file_exists($copyPath)) {
				copy($original, $copyPath);
			}
		}
	}
}

class Startup extends \Codeception\Platform\Extension {

	private static $isFirst = TRUE;

	public static $events = [
		'result.print.after' => 'cleaner'
	];

	public function cleaner() {
		if (!Environment::isDebug()) {
			$files = \Nette\Utils\Finder::findFiles('*')->from(Environment::getTempDir());

			foreach ($files as $file) {
				unlink($file);
			}
		}
	}
}

function dd($var) {
	\Codeception\Util\Debug::debug($var);
	die();
}