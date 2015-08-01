# Manipulace v presenteru

## Odstraòování obrázkù

```php
<?php

class ImagePresenter extends BasePresenter {

    public function handleDeleteImage() {
        $this->imageStorage->delete('namespace/image.jpg'); // Odstraní všechny obrázky (i upravené) s jménem image.jpg
    }
}
?>
```

## Nahrávání obrázkù

```php
<?php

class ImagePresenter extends BasePresenter {

    public function afterUpload($form, $values) {
        /** @var \WebChemistry\Images\Image\Image $file */
        $file = $this->imageStorage->saveUpload($values->upload, 'namespace');

        $absoluteName = (string) $file->getInfo(); // Vrátí namespace/nazevObrazku.xxx
        
        // Vlastní zpracování obrázkù
        $file = $this->imageStorage->saveUpload($values->upload, 'namespace', FALSE); // Obrázek se automaticky nenahraje
        
        $file->setNameWithoutSuffix('nazev');
        $file->setNamespace('myNamespace');
        
        $file->save();
        
        // Pøi nastavování height, width, flag apod. se obrázek nahraje do specifické složky, ne do original!
    }

}
?>
```

## Víceurovòové namespace

```php
<?php

class ImagePresenter extends BasePresenter {

    public function afterUpload($form, $values) {
        /** @var \WebChemistry\Images\Image\Upload */
        $file = $this->imageStorage->saveUpload($values->upload, 'namespace/secondNamespace');

        $imageName = (string) $file->getInfo();
    }

}
?>
```

## Nahrání obrázkù pøes string

```php
<?php

class ImagePresenter extends BasePresenter {

    public function afterUpload($form, $values) {
        /** @var \WebChemistry\Images\Image\Content */
        $file = $this->imageStorage->saveConten($values->upload, 'filename.jpg', 'namespace');

        $imageName = (string) $file->getInfo();
    }

}
?>
```

## Získání obrázkù

```php
<?php

class ImagePresenter extends BasePresenter {

    public function manipulation($upload, $url) {
        $this->imageStorage->get('namespace/image.png');
        
        // Vlastní velikosti, flag, helpery apod.
        $this->imageStorage->get('namespace/image.png', '200x100');
        $this->imageStorage->get('namespace/image.png', '200x100', 'fill');
        $this->imageStorage->get('namespace/image.png', '200x100|sharpen|crop:20,20,10,10');
        
        // Vlastní "lokální" noimage
        $this->imageStorage->get('namespace/image.png', NULL, NULL, 'myNoImage/image.png');
    }

}
?>
```

## Manipulace s urèitým namespace

```php
<?php

class ImagePresenter extends BasePresenter {

    public function manipulation($upload, $url) {
        $storageNamespace = $this->imageStorage->createNamespace('images');
        
        $storageNamespace->saveUpload($upload); // Uloží obrázek s namespace 'images'
        $storageNamespace->delete('name.jpg'); // Odstraní obrázek namespace/name.jpg
        $storageNamespace->saveContent(file_get_contents($url), 'image.png'); // Uloží obrázek jako images/image.png
    }

}
?>
```