<?php

namespace WebChemistry\Images;

trait TPresenter {

	/** @var AbstractStorage @inject */
	public $imageStorage;

	public function createTemplate($template = NULL) {
		$template = $template ? : parent::createTemplate();

		$template->imageStorage = $this->imageStorage;

		return $template;
	}

}
