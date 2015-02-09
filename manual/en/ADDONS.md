# Addons

## Upload Control

For auto-uploading and auto-deleting images + preview image.

**Installation:**

```php
WebChemistry\Images\Addons\UploadControl::register();
```

**Usage:**

```php

protected function createComponentForm() {
    $form = new Nette\Application\UI\Form;

    $row = $this->getFromDatabase();

    $form->addImageUpload('upload', 'Upload')
            ->setDefaultValue($row->upload)
            ->setNamespace('namespace');

    // or
    $form->addImageUpload('upload2', 'Upload 2', $row->upload, 'namespace');

    $form->onSuccess[] = $this->afterForm;

    return $form;
}

public function afterForm($form, $values) {
    $row = $this->getFromDatabase();
    $row->upload = $values->upload;

    $row->update();
}

```