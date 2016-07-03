# Events

## onCreate

This events runs when image is resized:
```yaml
services:
    listen: Listener

images:
    events:
        onCreate:
            - EventListener::method
            - @listener::method
```

```php

class EventListener {

    public function method(WebChemistry\Images\Image\PropertyAccess $property, Nette\Utils\Image $image) {
        $image->sharpen();
    }
    
}

```

## onSave

Runs when method WebChemistry\Images\Image\PropertyAccess::save() is called

```yaml
services:
    listen: Listener

images:
    events:
        onSave:
            - EventListener::method
            - @listener::method
```

```php

class EventListener {

    public function method(WebChemistry\Images\Image\PropertyAccess $property, Nette\Utils\Image $image, $imageType = NULL) {
        $image->sharpen();
    }
    
}

```

## onUploadSave

Runs when method WebChemistry\Images\Image\PropertyAccess::saveUpload() is called

```yaml
services:
    listen: Listener

images:
    events:
        onUploadSave:
            - EventListener::method
            - @listener::method
```

```php

class EventListener {

    public function method(WebChemistry\Images\Image\PropertyAccess $property, Nette\Utils\Image $image, $imageType = NULL) {
        $image->sharpen();
    }
    
}

```
