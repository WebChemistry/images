##Installation

With composer:
```php
composer require webchemistry\images:@dev
```

Config.neon:
```yaml
extensions:
    images: WebChemistry\Images\Extension
```

Image storage in Presenter (as Trait):

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter {
    use \Webchemistry\Images\TPresneter;
}
?>
```

Generate image´s address via Presenter:

```php
<?php

class GeneratePresenter extends BasePresenter {
    use \WebChemistry\Images\TPresenterGenerator;
}
?>
```

##Usage

- [Configuring](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/en/CONFIGURING.md)
- [Normal macros](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/en/NORMAL.md)
- [Responsive macros](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/en/RESPONSIVE.md)
- [Manipulation in presenter](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/en/MANIPULATION.md)
- [Addons](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/en/ADDONS.md)