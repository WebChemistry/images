<?php

namespace WebChemistry\Images;


use Nette\Application\UI\ITemplate;
use WebChemistry\Images\Template\ImageFacade;

trait TPresenter {

	/** @var ImageFacade */
	private $imageFacade;

	public function inject_ImageStorage(ImageFacade $imageFacade) {
		$this->imageFacade = $imageFacade;
	}

	/**
	 * @return ITemplate
	 */
	public function createTemplate(): ITemplate {
		$template = parent::createTemplate();

		$template->_imageFacade = $this->imageFacade;

		return $template;
	}

}
