# Helpers

## Custom helper

```php

class MyHelper extends WebChemistry\Images\Helpers\AbstractHelper {

	public function invoke(Image $image, $parameter = NULL) {
		$array = $this->formatParameters($parameter);
		
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
