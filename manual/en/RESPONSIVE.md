# Responsive images

We need javascript from client-side directory.

```html
<a n:img="'namespace'">
    <picture data-settings="[]">
        <source n:img="'namespace', '768x'" >
        <source n:img="'namespace', '1200x'" media="(min-width: 768px)">
        <source n:img="'namespace'" media="(min-width: 768px)">
        <noscript>
            <img n:img="'namespace'">
        </noscript>
    </picture>
</a>
```