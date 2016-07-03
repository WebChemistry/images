# Image editing

## Width

```php
$image->setWidth(50);
$image->setWidth('25%');

$image->setSize('50');
$image->setSize('25%');
```

## Height

```php
$image->setHeight(50);
$image->setHeight('25%');

$image->setSize('x50');
$image->setSize('x25%');
```

## Image name

```php
// Default name is test.png

$image->setName('image.png'); // current name is image.png
```

```php
// Default name is test.png

$image->setSuffix('jpg'); // current name is test.jpg
```

```php
// Default name is test.png

$image->setNameWithoutSuffix('img'); // current name is img.png
```

## Namespace

```php
$image->setNamespace('namespace');
```

```php
$image->setAbsoluteName('namespace/image.png');
```

## Flags

```php
$image->setFlag('exact');

// or
$image->setFlag(8);

// we can combinate
$image->setFlag(['stretch', 'shrink_only']);

// or
$image->setFlag([1, 2]);
```

## Helpers

```php
$image->setHelpers([
    'crop:50,25,10,10',
    'sharpen'
]);
