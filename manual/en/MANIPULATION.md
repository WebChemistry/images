# Manipulation in presenter

## Delete images

```php
<?php

class ImagePresenter extends BasePresenter {

    public function handleDeleteImage($imageName) {
        $imageName = 'namespace/image.jpg';
        $this->imageStorage->delete($imageName);
    }
}
?>
```

## Upload images

```php
<?php

class ImagePresenter extends BasePresenter {

    public function afterUpload($form, $values) {
        /** @var \WebChemistry\Images\Image\Info */
        $file = $this->imageStorage->saveUpload($values->upload, 'namespace');

        $imageName = (string) $file;
    }

}
?>
```

## Subfolders for namespace

```php
<?php

class ImagePresenter extends BasePresenter {

    public function afterUpload($form, $values) {
        /** @var \WebChemistry\Images\Image\Info */
        $file = $this->imageStorage->saveUpload($values->upload, 'namespace/subfolder');

        $imageName = (string) $file;
    }

}
?>
```

## Save image from content

```php
<?php

class ImagePresenter extends BasePresenter {

    public function saveFromContent($url) {
        /** @var \WebChemistry\Images\Image\Info */
        $file = $this->imageStorage->saveContent(file_get_contents($url), 'filename.jpg', 'namespace');
    }

}
?>
```

## Get image storage with namespace

```php
<?php

class ImagePresenter extends BasePresenter {

    public function manipulation($upload, $url) {
        $storageNamespace = $this->imageStorage->createNamespace('images');
        
        $storageNamespace->saveUpload($upload); // Save image with namespace 'images'
        $storageNamespace->delete('name.jpg'); // Delete image namespace/name.jpg
        $storageNamespace->saveContent(file_get_contents($url), 'image.png'); // Save image as images/image.png
        
        $this->imageStorage->saveContent(file_get_contents($url), 'image.png'); // Save image as image.png
    }

}
?>
```

## Editing image

```php
<?php

class ImagePresenter extends BasePresenter {

    public function after($form, $values) {
        // From upload
        $upload = $imageStorage->fromUpload($values->upload, 'namespace');

        // From content
        $upload = $imageStorage->fromContent($values->string, 'namespace');

        // Namespace
        $upload->setNamespace('namespace');
        // Sizes
        $upload->setSize('100x250');
        $upload->setHeight(100);
        $upload->setWidth(250);
        // Change quality
        $upload->setQuality(80);
        // Flags
        $upload->setFlag('exact');

        $upload->setCallback(function (Nette\Utils\Image $image) {
            // Proccess

            return $image;
        });

        // Uložení
        $info = $upload->save();
        
        (string) $info;
    }

}
?>
```