# Configuring

Default values in config.neon (All is optional):

```yaml
image:
    wwwDir: %wwwDir%
    registration:
        texy: no
        upload: yes
        multiUpload: no
        presenter: yes
    assetsDir: 'assets'
    noimage: 'noimage/noimage.png'
    settings:
        connectors: # For future update
        upload:
            label: 'Delete this image?'
        helpers:
            crop: WebChemistry\Images\Helpers\Crop
            sharpen: WebChemistry\Images\Helpers\Sharpen
            quality: WebChemistry\Images\Helpers\Quality
    router:
        mask: 'show-image/<name>[/<size>[/<flag>[/<noimage>]]]'
        resize: no # Disallow create resized image
        flag: 0
        disable: no
```