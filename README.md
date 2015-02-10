##Installation

With composer:
```php
composer require webchemistry\images:1.2.2
```

Config.neon:
```yaml
extensions:
    images: WebChemistry\Images\DI\Extension
```

Image storage in Presenter (as Trait):

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter {
    use \WebChemistry\Images\Traits\TPresenter;
}
?>
```

Generate image´s address via Presenter:

```php
<?php

class GeneratePresenter extends BasePresenter {
    use \WebChemistry\Images\Traits\TGenerator;
}
?>
```

##Usage

- [Configuring](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/en/CONFIGURING.md)
- [Generation of images in presenter](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/en/GENERATION.md)
- [Macros](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/en/NORMAL.md)
- [Responsive images](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/en/RESPONSIVE.md)
- [Manipulation in presenter](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/en/MANIPULATION.md)
- [Image upload](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/en/ADDONS.md)