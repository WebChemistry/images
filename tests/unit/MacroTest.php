<?php

class MacroTest extends \Codeception\TestCase\Test {

	protected function getLatte() {
		$latte = new \Latte\Engine();
		\WebChemistry\Images\Template\Macros::install($latte->getCompiler());

		return $latte;
	}

    public function testInlineMacro() {
		$this->assertContains('echo $imageStorage->get(\'test.png\', \'250x150\', \'exact\', \'noimage.png\')->getLink();',
			$this->getLatte()->compile(__DIR__ . '/../_data/templates/inline.latte'));
    }

	public function testAttrMacro() {
		$this->assertContains('<?php echo \' src="\' . $imageStorage->get(\'test.png\', \'250x150\', \'exact\', \'noimage.png\')->getLink() . \'"\' ?>',
			$this->getLatte()->compile(__DIR__ . '/../_data/templates/attr.latte'));
	}
}