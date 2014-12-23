# Manipulation in presenter

## Delete images

```php
<?php

class ImagePresenter extends BasePresenter {

    public function handleDeleteImage($imageName) {
        $this->imageStorage->setNamespace('namespace'); // Optional

        $count = $this->imageStorage->delete($imageName); // Delete images from %wwwDir%/%assetsDir%[/%namespace%]/*/%imageName%

        if ($count === 0) {
            $this->flashMessage('We are not deleting any image.', 'error');
        } else {
            $this->flashMessage(sprintf('We are deleting %d images.', $count));
        }
    }
}
?>
```

## Upload images

```php
<?php

class ImagePresenter extends BasePresenter {

    public function afterUpload($form, $values) {
        /** @var \WebChemistry\Images\Image|boolean */
        $file = $this->imageStorage->upload($values->image);

        if ($file === FALSE) {
            $form->addError('Image did not upload');
        }

        $imageName = (string) $file; // or $file->shortName or $file->getShortName();
    }

}
?>
```

## Save image from content

```php
<?php

class ImagePresenter extends BasePresenter {

    public function saveFromContent($url) {
        /** @var \WebChemistry\Images\Image */
        $file = $this->imageStorage->saveContent(file_get_contents($url));
    }

}
?>
```

## Save image from Image class

```php
<?php

class ImagePresenter extends BasePresenter {

    public function saveFromClass(\Nette\Utils\Image $class) {
        /** @var \WebChemistry\Images\Image */
        $file = $this->imageStorage->saveContent($class);
    }

}
?>
```