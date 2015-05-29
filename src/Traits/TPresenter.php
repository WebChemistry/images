<?php

namespace WebChemistry\Images\Traits;

use WebChemistry;

trait TPresenter {

	/** @var WebChemistry\Images\Storage */
	public $imageStorage;

	/**
	 * @param WebChemistry\Images\Storage $storage
	 */
	public function injectImageStorage(WebChemistry\Images\Storage $storage) {
		$this->imageStorage = $storage;
	}

	public function createTemplate() {
		$template = parent::createTemplate();

		$template->imageStorage = $this->imageStorage;

		return $template;
	}
}
