<?php

namespace App\Presenters;

use Nette;
use WebChemistry\Images\Traits\TPresenter;


class ImagePresenter extends Nette\Application\UI\Presenter {

	use TPresenter;

	protected function createComponentForm() {
		$form = new Nette\Application\UI\Form;

		$form->addImageUpload('upload_namespace', 'Image upload', 'namespace')
				->setDefaultValue('namespace/outer-space-fantasy-hd-wallpaper-1920x1080-6665.jpg');

		$form->addImageUpload('upload_original', 'Image upload');

		$form->addSubmit('submit', 'Submit');

		$form->onSuccess[] = $this->afterForm;

		return $form;
	}

	public function afterForm(Nette\Application\UI\Form $form, $values) {

	}
}
