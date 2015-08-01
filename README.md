## Installation

Composer:
```php
composer require webchemistry\images:1.4.3
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
    use \WebChemistry\Images\Traits\TPresenter;
}
?>
```

## Usage

- [Konfigurace](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/cs/configuring.md)
- [Manipulace obrázku](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/cs/manipulation.md)
- [Manipulace obrázku 2](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/cs/property.md)
- [Doplňky (UploadControl)](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/cs/addons.md)
- [Makra](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/cs/macros.md)
- [Helpers](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/cs/helpers.md)
- [Router](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/cs/generation.md)
