# Generation of images using url

## Using macro (examples)

**Basic:**
```html
    {imgLink 'namespace/file.jpg', '200x400', 'fit'}
```

**For baseUri:**
```html
    {imgLink '//namespace/file.jpg', '200x400', 'fit'}
```

**Attribute:**
```html
    <a n:imgLink="'//namespace/file.jpg', '200x400', 'fit'">Link</a>
```

## Using link method

```html
    {link :ImageStorage:Generate: 'namespace/file.jpg', '200x400', 'fit'}
```
