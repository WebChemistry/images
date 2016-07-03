# Helpers

## Custom helper

```php

class MyHelper implements WebChemistry\Images\Helpers\IHelper {

	public function invoke(Image $image, array $parameters) {
		$image->sharpen();
	}
	
}

```

**Registration:**

```yaml
images:
	helpers:
		myHelper: MyHelper
```

# Default helpers and their using

## Crop

```html
    {img 'name.jpg', '200x150|crop:50,50,50%,50%'}
```

```php
    $imageStorage->get('name.jpg', '200x150|crop:50,50,50%,50%')
```

## Sharpen

```html
    {img 'name.jpg', '200x150|sharpen'}
```

## Combination

```html
    {img 'name.jpg', '200x150|sharpen|crop:50,50,50,50'}
```
