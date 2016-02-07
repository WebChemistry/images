<?php

namespace WebChemistry\Images;

use Nette\Application\UI\ITemplate;

trait TPresenter {

	/** @var AbstractStorage @inject */
	public $imageStorage;

	/**
	 * @param ITemplate $template
	 * @return ITemplate
	 */
	public function createTemplate($template = NULL) {
		$template = $template ? : parent::createTemplate();

		$template->imageStorage = $this->imageStorage;

		return $template;
	}

}
