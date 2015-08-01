<?php

class LatteTest extends \Codeception\TestCase\Test {

	/** @var Nette\Application\UI\ITemplateFactory */
	protected $factory;

	protected function _before() {
		/** @var \Nette\Application\UI\ITemplateFactory factory */
		$this->factory = E::getByType('Nette\Application\UI\ITemplateFactory');

		E::createDirs('%www%/assets', [
			'namespace/namespace/namespace/250x150_4',
			'mynoimage/original'
		]);

		E::copy('%data%/test.png', [
			'%www%/assets/namespace/namespace/namespace/250x150_4/%name%',
			'%www%/assets/mynoimage/original/noimage.png'
		]);
	}

	/**
	 * This method is called after the last test of this test class is run.
	 *
	 * @since Method available since Release 3.4.0
	 */
	public static function tearDownAfterClass() {
		E::truncateDirectory('%www%/assets');
	}

	private function createTemplate($file) {
		$template = $this->factory->createTemplate();

		return $template->getLatte()->renderToString(E::directory('%data%/templates/' . $file), array(
			'imageStorage' => E::getByType('WebChemistry\Images\Storage'),
			'basePath' => '',
			'baseUri' => ''
		));
	}

	protected function tearDown() {
	}

	public function testBaseMacros() {
		$content = $this->createTemplate('base.latte');

		$this->assertStringEqualsFile(E::dumpedFile('base'), $content);
	}

	public function testAttrMacros() {
		$content = $this->createTemplate('attr.latte');

		$this->assertStringEqualsFile(E::dumpedFile('attr'), $content);
	}
}
