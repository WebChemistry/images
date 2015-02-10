# Configuring

Default values in config.neon (All is optional):

```yaml
image:
    wwwDir: %wwwDir%
    assetsDir: 'assets'
    noimage: 'noimage/noimage.png'
    settings:
        upload:
            label: 'Delete this image?'
    router:
        mask: 'show-image/<name>[/<size>[/<flag>[/<noimage>]]]'
        resize: no # Disallow create resized image
        flag: 0
        disable: no
```