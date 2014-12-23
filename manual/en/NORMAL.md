# Normal macros

```html
    {img string namespaceAndFilename[, string size[, string|array|null flags = FIT]]}
```

## Namespaces

```html
    {img 'filename.jpg'} 
```

Generate: %basePath%/%assetsDir%/original/filename.jpg

```html
    {img 'namespace/filename.jpg'}
```

Generate: %basePath%/%assetsDir%/namespace/original/filename.jpg

```html
    <a n:img="'namespace/filename.jpg'">
        <img n:img="'namespace/filename.jpg'">
    </a>
```

Generate:
```html
    <a href="{$basePath}/{$assetsDir}/namespace/original/filename.jpg">
        <img src="{$basePath}/{$assetsDir}/namespace/original/filename.jpg">
    </a>
```

## No image
If script could not find image, it replaced with noimage.

```html
    {img 'imageNotExist.jpg'} 
```

Generate: %basePath%/%assetsDir%/%noimage%


## Resize image

```html
    {img 'namespace/filename.jpg', '200x300'}
```

Generate and create image if does not exist: %basePath%/%assetsDir%/namespace/200x300_0/filename.jpg

Only width:
```html
    {img 'namespace/filename.jpg', '200'} or {img 'namespace/filename.jpg', '200x'}
```

Only height:
```html
    {img 'namespace/filename.jpg', 'x300'}
```

## Flags

Flag **is not** case-sensitive.
We use flags from [Nette\Utils\Image](http://api.nette.org/2.2.6/source-Utils.Image.php.html#100-113).

```html
    {img 'namespace/filename.jpg', '200x300', 'exact'}
```

We can combine flags
```html
    {img 'namespace/filename.jpg', '200x300', ['shrink_only', 'stretch']}
```