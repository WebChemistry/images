# Addons

## Upload control
Automatic uploading and deleting of image with preview.

**Installation:**
Extension automatically register addons to nette forms.

We can disable in config:
```php
images:
    registration:
        upload: no
```

**Usage:**

```php

protected function createComponentForm() {
    $form = new Nette\Application\UI\Form;

    $row = $this->getFromDatabase();

    $form->addImageUpload('upload', 'Upload')
            ->setDefaultValue($row->upload) // Contains e.g. namespace/upload.png
            ->setRequired()
            ->addRule($form::MAX_FILE_SIZE, NULL, 1024)
            ->setNamespace('namespace');

    $form->onSuccess[] = $this->successForm;

    return $form;
}

public function successForm($form, $values) {
    $row = $this->getFromDatabase();
    
    $row->upload = $values->upload; // Contains e.g. namespace/uniqueNameOfImage.png or NULL (when input not filled or checkbox is checked)

    $row->update();
}

```

## Functions

```php

$upload->getCheckbox()->setHeight(150); // Height of preview
$upload->getCheckbox()->setWidth(150); // Width of preview
$upload->getCheckbox()->caption = 'My caption'; // Sets caption locally
WebChemistry\Images\Controls\Checkbox::$globalCaption = 'My global caption'; // Sets caption globally

```

## MultiUpload
Auto-registration disabling:
```yaml
images:
    registration:
        multiUpload: no
```

```php
protected function createComponentForm() {
    $form = new Nette\Application\UI\Form;

    $row = $this->getFromDatabase();

    $form->addMultiImageUpload('upload', 'Upload', 'namespace')
        ->setDefaultValue([
            'namespace/firstImage.png',
            'namespace/secondImage.png'
        ]);

    $form->onSuccess[] = $this->successForm;

    return $form;
}

public function successForm($form, $values) {
    $row = $this->getFromDatabase();

    $row->upload = $values->upload; // Contains array

    $row->update();
}

```
