# Generace url adres obrázkù

## Makra

**Základ:**
```html
    {imgLink 'namespace/file.jpg', '200x400', 'fit'}
```

**Absolutní adresa**
```html
    {imgLink '//namespace/file.jpg', '200x400', 'fit'}
```

**n: makro**
```html
    <a n:imgLink="'//namespace/file.jpg', '200x400', 'fit'">Link</a>
```

## Použítím link

```html
    {link :ImageStorage:Generate: 'namespace/file.jpg', '200x400', 'fit'}
```
