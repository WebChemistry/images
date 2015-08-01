# Makra

## Základ

```html
    {img 'filename.jpg'} 
```

Vygeneruje: %basePath%/assets/original/filename.jpg

**Jako n: attribut**

```html
    <a n:img="'filename.jpg'">
        <img n:img="'filename.jpg'">
    </a>
```

Vygeneruje:
```html
    <a href="{$basePath}/assets/original/filename.jpg">
        <img src="{$basePath}/assets/original/filename.jpg">
    </a>
```

## Namespace

```html
    {img 'namespace/filename.jpg'}
```

Vygeneruje: %basePath%/%assets%/namespace/original/filename.jpg

**Vícenásobné namespace**

```html
    <img n:img="'namespace/subfolder/filename.jpg'">
```

Vygeneruje:
```html
    <img src="{$basePath}/assets/namespace/subfolder/original/filename.jpg">
```

## No image
Když obrázek neexistuje nahrádí se noimage, když ani ten neexistuje, tak hastagem #noimage

```html
    {img 'imageNotExist.jpg'} 
```

Vygeneruje: %basePath%/assets/noimage/original/noimage.png

**Zmìna velikosti funguje i na noimage**

```html
    {img 'imageNotExist.jpg', '700x100'} 
```

Vygeneruje: %basePath%/assets/noimage/700x100_0/noimage.png

**Nastavení "lokální" noimage**

```html
    {img 'imageNotExist.jpg', NULL, NULL, 'noavatar/noavatar.png'}
```

Vygeneruje: %basePath%/assets/noavatar/original/noavatar.png

## Manipulace s obrázky

```html
    {img 'namespace/filename.jpg', '200x300'}
```

Vygeneruje (vytvoøí obrázek, pokud neexistuje): %basePath%/assets/namespace/200x300_0/filename.jpg

**Pouze šíøka**

```html
    {img 'namespace/filename.jpg', '200'} nebo {img 'namespace/filename.jpg', '200x'}
```

**Pouze výška**
```html
    {img 'namespace/filename.jpg', 'x300'}
```

## Flags

Flag **není** case-sensitive.
Používám z [Nette\Utils\Image](http://doc.nette.org/cs/2.3/images#toc-zmena-velikosti).

```html
    {img 'namespace/filename.jpg', '200x300', 'exact'}
```

Mùžeme kombinovat
```html
    {img 'namespace/filename.jpg', '200x300', ['shrink_only', 'stretch']}
```