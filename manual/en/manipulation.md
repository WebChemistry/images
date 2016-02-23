# Manipulation in presenter

## Deleting

```php
<?php

class ImagePresenter extends BasePresenter {

    public function handleDeleteImage() {
        $this->imageStorage->delete('namespace/image.jpg');
    }
}
?>
```

## Uploading

```php
<?php

class ImagePresenter extends BasePresenter {

    public function afterUpload($form, $values) {
        /** @var \WebChemistry\Images\Image\Image $file */
        $absoluteName = $this->imageStorage->saveUpload($values->upload, 'namespace'); // return namespace/nazevObrazku.xxx
    }

}
?>
```

## Multilevel of namespace

```php
<?php

class ImagePresenter extends BasePresenter {

    public function afterUpload($form, $values) {
        /** @var \WebChemistry\Images\Image\Upload */
        $absoluteName = $this->imageStorage->saveUpload($values->upload, 'namespace/secondNamespace');
    }

}
?>
```

## Uploading of image via Nette\Image

```php
<?php

class ImagePresenter extends BasePresenter {

    public function afterUpload($form, $values) {
    	$image = Nette\Utils\Image::fromString($values->content);
        $absoluteName = $this->storage->saveImage($image, 'filename.jpg', 'namespace')
    }

}
?>
```

## Getting

```php
<?php

class ImagePresenter extends BasePresenter {

    public function manipulation($upload, $url) {
        $this->imageStorage->get('namespace/image.png');

        $this->imageStorage->get('namespace/image.png', '200x100');
        $this->imageStorage->get('namespace/image.png', '200x100', 'fill');
        $this->imageStorage->get('namespace/image.png', '200x100|sharpen|crop:20,20,10,10');
        
        // Custom default image
        $this->imageStorage->get('namespace/image.png', NULL, NULL, 'myNoImage/image.png');
    }

}
?>
```