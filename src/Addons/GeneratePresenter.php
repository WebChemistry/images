<?php

namespace WebChemistry\Images\Addons;

use Nette, WebChemistry, Tracy;

class GeneratePresenter extends Nette\Application\UI\Presenter {

	use WebChemistry\Images\Traits\TPresenter;

	/** @var bool */
	private $resize = FALSE;

	public function __construct($resize = FALSE) {
		$this->resize = (bool) $resize;
	}

	public function actionDefault($name, $size = NULL, $flag = NULL, $noimage = NULL) {
		try {
			$image = $this->imageStorage->get($name, $size, $flag, $noimage);

			if (!$image->getInfo()->isImageExists() && !$this->resize) {
				$info = $image->getOriginal();
			} else {
				$info = $image->getInfo();
			}
		} catch (\Exception $e) {
			Tracy\Debugger::getLogger()
						  ->log($e);

			$this->error('Image error.');
		}

		if ($info->isImageExists()) {
			$image = $info->getNetteImageClass();

			$image->send($info->getImageType());
		} else {
			$this->error('Image not found.');
		}

		$this->terminate();
	}
}
