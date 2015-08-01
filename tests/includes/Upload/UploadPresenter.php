<?php

namespace Test\Presenters;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

class UploadPresenter extends Presenter {

	public function actionDefault($form) {
		$this->template->ctrl = $this->getComponent($form ? : 'template');

		$this->template->setFile(__DIR__ . '/templates/default.latte');
	}

	/**
	 * Generates URL to presenter, action or signal.
	 *
	 * @param  string   destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array|mixed
	 * @return string
	 * @throws InvalidLinkException
	 */
	public function link($destination, $args = array()) {
		return NULL;
	}

	protected function createComponentTemplate() {
		$form = new Form();

		$form->addImageUpload('image', 'Image');

		$form->addImageUpload('imageNull', 'Image null')
			 ->setDefaultValue(NULL);

		$form->addImageUpload('imageExists', 'Image exists', 'namespace')
			 ->setDefaultValue('namespace/test.png');

		$form->addImageUpload('imageNotExists', 'Image not exists')
			 ->setDefaultValue('notTest/notTest.png');

		$form->addImageUpload('imageRequired', 'Image required', 'namespace')
			 ->setDefaultValue('namespace/test.png')
			 ->setRequired();

		$form->addImageUpload('imageRequiredNotExists', 'Image required and not exists.')
			 ->setDefaultValue('namespace/notExists.png');

		return $form;
	}

	protected function createComponentDelete() {
		$form = new Form;

		$form->addImageUpload('upload', '', 'delete')
			 ->setDefaultValue('delete/test.png');

		return $form;
	}

	protected function createComponentUpload() {
		$form = new Form();

		$form->addImageUpload('upload');

		$form->addImageUpload('uploadNamespace', '', 'upload');

		$form->addImageUpload('null');

		$form->addImageUpload('prefix');

		return $form;
	}

	protected function createComponentRequireUpload() {
		$form = new Form();

		$form->addImageUpload('upload', '', 'delete')
			 ->setRequired()
			 ->setDefaultValue('delete/test.png');

		return $form;
	}
}