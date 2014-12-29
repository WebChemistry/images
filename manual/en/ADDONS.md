# Addons

## Upload Control

For auto-uploading and auto-deleting images + preview image.

**Usage:**

```php

protected function createComponentForm() {
    $form = new Nette\Application\UI\Form;

    $row = $this->getFromDatabase();

    $form->addUploadImage('upload', 'Upload')
            ->setDefaultValue($row->upload)
            ->setNamespace('upload');

    $form->onSuccess[] = $this->afterForm;

    return $form;
}

public function afterForm($form, $values) {
    $row = $this->getFromDatabase();
    $row->upload = $values->upload;

    $row->update();
}

```