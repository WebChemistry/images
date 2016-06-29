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

- [Configuration](https://github.com/WebChemistry/Images/blob/master/manual/en/configuring.md)
- [Manipulation](https://github.com/WebChemistry/Images/blob/master/manual/en/manipulation.md)
- [Manipulation 2](https://github.com/WebChemistry/Images/blob/master/manual/en/property.md)
- [Addons (UploadControl, MultiUpload)](https://github.com/WebChemistry/Images/blob/master/manual/en/addons.md)
- [Macros](https://github.com/WebChemistry/Images/blob/master/manual/en/macros.md)
- [Helpers](https://github.com/WebChemistry/Images/blob/master/manual/en/helpers.md)

## Použití

- [Konfigurace](https://github.com/WebChemistry/Images/blob/master/manual/cs/configuring.md)
- [Manipulace obrázku](https://github.com/WebChemistry/Images/blob/master/manual/cs/manipulation.md)
- [Manipulace obrázku 2](https://github.com/WebChemistry/Images/blob/master/manual/cs/property.md)
- [Doplňky (UploadControl, MultiUpload)](https://github.com/WebChemistry/Images/blob/master/manual/cs/addons.md)
- [Makra](https://github.com/WebChemistry/Images/blob/master/manual/cs/macros.md)
- [Helpers](https://github.com/WebChemistry/Images/blob/master/manual/cs/helpers.md)
