# Konfigurace

**Výchozí hodnoty:**

```yaml
image:
    wwwDir: %wwwDir%
    assetsDir: 'assets'
    defaultImage: 'default/default.png'
    registration:
        upload: yes
        multiUpload: yes
    checkbox:
        caption: 'Odstranit obrázek' # Nahradí globálně titulek u checboxu 
	helpers:
		crop: WebChemistry\Images\Helpers\Crop
		sharpen: WebChemistry\Images\Helpers\Sharpen
		quality: WebChemistry\Images\Helpers\Quality
	quality: 85 # Použije se tato hodnota pro všechny obrázky, pokud není stanoveno jinak pro jednotlivé obrázky
```
