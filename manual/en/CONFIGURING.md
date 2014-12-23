# Configuring

Default values in config.neon (All is optional):

```yaml
image:
    wwwDir: %wwwDir%
    imageDir: 'assets'
    original: 'original'
    noimage: 'noimage.png'
    models:
        bootstrap: [
                '768' => '',
                '992' => '(min-width:768px)',
                '1200' => '(min-width:992px)',
                NULL => '(min-wudth:1200)'
            ]
```