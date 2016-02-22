<?php

class MultiUploadPresenter extends \Nette\Application\UI\Presenter {

	/** @var \WebChemistry\Images\FileStorage\FileStorage */
	public $imageStorage;

	/** @var \Nette\Application\UI\Form */
	protected $form;

	public function renderDefault() {
		$this->terminate();
	}

	public function getForm() {
		if ($this->form) {
			return $this->form;
		}
		$form = new \Nette\Application\UI\Form();
		$form['upload'] = new \WebChemistry\Images\Controls\MultiUpload();

		$form->addSubmit('submit');

		return $this->form = $form;
	}

	protected function createComponentUpload() {
		return $this->getForm();
	}

}
