[![Build Status](https://travis-ci.org/WebChemistry/images.svg?branch=master)](https://travis-ci.org/WebChemistry/images)

## Installation

Composer:
```php
composer require webchemistry/images
```

config:
```yaml
extensions:
    images: WebChemistry\Images\DI\Extension
```

Presenter trait:

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter {
    use WebChemistry\Images\TPresenter;
}
?>
```

## Usage

- [Configuration](https://github.com/WebChemistry/images/blob/master/manual/configuring.md)
- [Manipulation](https://github.com/WebChemistry/images/blob/master/manual/manipulation.md)
- [Manipulation 2](https://github.com/WebChemistry/images/blob/master/manual/property.md)
- [Addons (UploadControl, MultiUpload)](https://github.com/WebChemistry/images/blob/master/manual/addons.md)
- [Macros](https://github.com/WebChemistry/images/blob/master/manual/macros.md)
- [Helpers](https://github.com/WebChemistry/images/blob/master/manual//helpers.md)
- [Events](https://github.com/WebChemistry/images/blob/master/manual/events.md)

