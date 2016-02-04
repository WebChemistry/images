# Helpers

## Vytvoření nového helperu

**Třída:**
```php

class MyHelper extends WebChemistry\Images\Helpers\AbstractHelper {

	public function invoke(Image $image, $parameter = NULL) {
		$array = $this->formatParameters($parameter);
		
		$image->sharpen();
	}
	
}

```

**Registrace:**

```yaml
images:
	helpers:
		myHelper: MyHelper
```

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

## Kombinace

```html
    {img 'name.jpg', '200x150|sharpen|crop:50,50,50,50'}
```
