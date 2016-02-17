<?php

class MockLatte implements \Nette\Application\UI\ITemplateFactory {

	public function createTemplate(\Nette\Application\UI\Control $control = NULL) {
		return new \Nette\Bridges\ApplicationLatte\Template(new \Latte\Engine());
	}

}


class Helper {

	public static function removeFilesRecursive($dir) {
		foreach (\Nette\Utils\Finder::findFiles('*')->from($dir) as $row) {
			unlink($row);
		}
	}

}