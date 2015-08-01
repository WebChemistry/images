# Helpers

## Crop

```html
    {img 'name.jpg', '200x150|crop:50,50,50%,50%'}
```

```php
    $imageStorage->get('name.jpg', '200x150|crop:50,50,50%,50%')
```

## Kvalita

```html
    {img 'name.jpg', '200x150|quality:100'}
```

## Sharpen

```html
    {img 'name.jpg', '200x150|sharpen'}
```

## Kombinace

```html
    {img 'name.jpg', '200x150|sharpen|quality:50'}
```
