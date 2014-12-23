# Responsive macros and client side

```html
    {img string namespaceAndFilename[, array|string sizes[, string|array|null flags = FIT]][, imgAttributes]}

    sizes = [size => media-queries]
    imgAttributes = [attrName => attrValue]
```

## Usage

```html
    {imgRes 'namespace/filename.jpg', ['768' => NULL, '1200x1800' => '(min-width: 768px)', NULL => '(min-width: 768px)']}
```

Generate:
```html
<picture data-settings="[]">
    <source src="/sb/www/assets/namespace/768x_0/filename.jpg">
    <source src="/sb/www/assets/namespace/1200x1800_0/filename.jpg" media="(min-width: 768px)">
    <source src="/sb/www/assets/namespace/original/filename.jpg" media="(min-width: 768px)">
    <noscript>
        <img src="/sb/www/assets/namespace/original/filename.jpg">
    </noscript>
</picture>
```

and after javascript:
```html
<picture data-settings="[]">
    <source src="/sb/www/assets/namespace/768x_0/filename.jpg">
    <source src="/sb/www/assets/namespace/1200x1800_0/filename.jpg" media="(min-width: 768px)">
    <source src="/sb/www/assets/namespace/original/filename.jpg" media="(min-width: 768px)">
    <img src="/sb/www/assets/namespace/original/filename.jpg">
</picture>
```

Image attributes

```html
    {imgRes 'namespace/filename.jpg', ['768' => NULL, '1200x1800' => '(min-width: 768px)', NULL => '(min-width: 768px)'], 'fit', ['class' => 'img-responsive', 'alt' => 'Image']}
```

Generate:

```html
<picture data-settings='{"class":"img-responsive","alt":"Image"}'>
    <source src="/sb/www/assets/namespace/768x_0/filename.jpg">
    <source src="/sb/www/assets/namespace/1200x1800_0/filename.jpg" media="(min-width: 768px)">
    <source src="/sb/www/assets/namespace/original/filename.jpg" media="(min-width: 768px)">
    <noscript>
        <img src="/sb/www/assets/namespace/original/filename.jpg" class="img-responsive" alt="Image">
    </noscript>
</picture>
```

and after javascript:
```html
<picture data-settings="{"class":"img-responsive","alt":"Image"}">
    <source src="/sb/www/assets/namespace/768x_0/filename.jpg">
    <source src="/sb/www/assets/namespace/1200x1800_0/filename.jpg" media="(min-width: 768px)">
    <source src="/sb/www/assets/namespace/original/filename.jpg" media="(min-width: 768px)">
    <img src="/sb/www/assets/namespace/original/filename.jpg" class="img-responsive" alt="Image">
</picture>
```
## Usage model

We can define and use model. ([Model´s configuring](https://github.com/AntikCz/WebChemistry-Images/blob/master/manual/en/CONFIGURING.md)).

```html
    {imgRes 'namespace/filename.jpg', 'bootstrap'}
```