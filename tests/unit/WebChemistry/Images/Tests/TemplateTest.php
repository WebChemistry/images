<?php
namespace WebChemistry\Images\Tests;

use Latte\Engine;
use Nette\Http\FileUpload;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use WebChemistry\Images\Image\ImageFactory;
use WebChemistry\Images\Modifiers\Composite;
use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Parsers\ModifierParser;
use WebChemistry\Images\Storage;
use WebChemistry\Images\Storages\LocalStorage;
use WebChemistry\Images\Template\ImageFacade;
use WebChemistry\Images\Template\ImageModifiers;
use WebChemistry\Images\Template\Macros;
use WebChemistry\Test\Services;
use WebChemistry\Testing\TUnitTest;

class TemplateTest extends \Codeception\Test\Unit {

	use TUnitTest;

	/** @var TemplateMock */
	private $latte;

	/** @var Storage */
	private $storage;

	protected function _before() {
		@mkdir(__DIR__ . '/output');
		$modifierContainer = new ModifierContainer();
		$modifierContainer->addAlias('resize', ModifierParser::parse('resize:5,5'));
		$url = new UrlScript('http://example.com/');
		$request = new Request($url);
		$imageFactory = new ImageFactory();
		$storage = $this->storage = new LocalStorage(__DIR__, 'output', $modifierContainer, $request, $imageFactory, 'default/upload.gif');

		$latte = new Engine();
		$this->latte = new TemplateMock($latte);
		Macros::install($latte->getCompiler());
		$this->latte->_imageFacade = new ImageFacade($storage, new ImageModifiers($request, $storage));
	}

	private function createUploadResource() {
		if (!file_exists(UPLOAD_GIF)) {
			copy(IMAGE_GIF, UPLOAD_GIF);
		}
		$upload = new FileUpload([
			'name' => 'upload.gif',
			'tmp_name' => UPLOAD_GIF,
			'type' => 'image/gif',
			'error' => 0,
			'size' => 1,
		]);

		return $this->storage->createUploadResource($upload);
	}

	protected function _after() {
		$this->services->fileSystem->removeDirRecursive(__DIR__ . '/output');
	}

	public function testBasicNoImage() {
		$string = $this->latte->renderToString($this->getFile('basic'));

		$this->assertSame('', trim($string));
	}

	public function testBasic() {
		$this->storage->save($this->createUploadResource());
		$string = $this->latte->renderToString($this->getFile('basic'));

		$this->assertSame('/output/original/upload.gif', trim($string));
	}

	public function testModifiers() {
		$this->storage->save($this->createUploadResource());
		$string = $this->latte->renderToString($this->getFile('resize'));

		$this->assertSame('http://example.com/output/resize/upload.gif', trim($string));
	}

	public function testAttr() {
		$this->storage->save($this->createUploadResource());
		$string = $this->latte->renderToString($this->getFile('attr'));

		$this->assertSame('<img src="/output/original/upload.gif">', trim($string));
	}

	private function getFile($name) {
		return __DIR__ . '/data/template/' . $name . '.latte';
	}

}

class TemplateMock {

	/** @var Engine */
	private $engine;

	/** @var array */
	private $params = [];

	public function __construct(Engine $engine) {
		$this->engine = $engine;
	}

	public function compile($name) {
		return $this->engine->compile($name);
	}

	public function renderToString($name, array $params = []) {
		return $this->engine->renderToString($name, $this->params + $params);
	}

	public function __set($name, $value) {
		$this->params[$name] = $value;
	}

}
