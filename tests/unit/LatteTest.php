<?php

use Environment as E;

class LatteTest extends \Codeception\TestCase\Test {
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	protected function _before() {
		E::copy('/test.png', array(
			'namespace/namespace/namespace/250x150_4',
			'noimage.png' => 'mynoimage/original'
		), '/assets');
	}

	protected function createLatte($file) {
		/** @var Nette\Application\UI\ITemplateFactory $factory */
		$factory = E::getByType('Nette\Application\UI\ITemplateFactory');
		$latte = $factory->createTemplate();

		return $latte->getLatte()->renderToString(E::getDataDir('/templates/' . $file), array(
			'imageStorage' => E::getByType('WebChemistry\Images\Storage'),
			'basePath' => '',
			'baseUri' => ''
		));
	}

	private function fileEquals($file, $content) {
		$this->assertEquals(file_get_contents(E::getDataDir('/expected/' . $file)), $content);
	}

    protected function _after() {
	}

    public function testBaseMacros() {
		$content = $this->createLatte('base.latte');
		$this->fileEquals('base.dmp', $content);
	}

	public function testAttrMacros() {
		$content = $this->createLatte('attr.latte');
		$this->fileEquals('attr.dmp', $content);
	}
}