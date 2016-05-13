# Manipulace v presenteru

## Odstraňování obrázků

```php
<?php

class ImagePresenter extends BasePresenter {

    public function handleDeleteImage() {
        $this->imageStorage->delete('namespace/image.jpg');
    }
}
?>
```

## Nahrávání obrázků

```php
<?php

class ImagePresenter extends BasePresenter {

    public function afterUpload($form, $values) {
        /** @var \WebChemistry\Images\Image\Image $file */
        $absoluteName = $this->imageStorage->saveUpload($values->upload, 'namespace'); // Vrátí namespace/nazevObrazku.xxx
    }

}
?>
```

## Víceurovňové namespace

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

## Nahrání obrázku přes Nette\Image

```php
<?php

class ImagePresenter extends BasePresenter {

    public function afterUpload($form, $values) {
    	$image = Nette\Utils\Image::fromString($values->content);
        $absoluteName = $this->storage->saveImage($image, 'filename.jpg', 'namespace', function (WebChemistry\Images\FileStorage\Image\Image $image) {
        	// Úpravy obrázku před uložením
        	$image->setQuality(100);
        });
    }

}
?>
```

## Získání obrázku

```php
<?php

class ImagePresenter extends BasePresenter {

    public function manipulation($upload, $url) {
        $this->imageStorage->get('namespace/image.png');
        
        // Vlastní velikosti, flag, helpery apod.
        $this->imageStorage->get('namespace/image.png', '200x100');
        $this->imageStorage->get('namespace/image.png', '200x100', 'fill');
        $this->imageStorage->get('namespace/image.png', '200x100|sharpen|crop:20,20,10,10');
        
        // Vlastní výchozí obrázek
        $this->imageStorage->get('namespace/image.png', NULL, NULL, 'myNoImage/image.png');
    }

}
?>
```