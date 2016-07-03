# Configuration

**Default values:**

```yaml
image:
    wwwDir: %wwwDir%
    assetsDir: 'assets'
    defaultImage: 'default/default.png'
    registration:
        upload: yes
        multiUpload: yes
	helpers:
		crop: WebChemistry\Images\Helpers\Crop
		sharpen: WebChemistry\Images\Helpers\Sharpen
	checkbox:
	    caption: NULL # Sets caption globally
	quality: 85 # Sets quality globally
	callbacks: # More in section events
	    onCreate: []
	    onSave: []
	    onUploadSave: []
```
