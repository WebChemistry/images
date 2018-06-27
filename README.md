[![Build Status](https://travis-ci.org/WebChemistry/images.svg?branch=master)](https://travis-ci.org/WebChemistry/images)

[Starší verze 2](https://github.com/WebChemistry/images/tree/v2.2)

## Instalace

Composer:
```php
composer require webchemistry/images
```

config:
```yaml
extensions:
    images: WebChemistry\Images\DI\ImagesExtension
```

### Konfigurace

```yaml
local: ## Nastavení pro lokalni uloziste
    enable: yes
    defaultImage: null
    wwwDir: %wwwDir%
    assetsDir: assets
    modifiers: []
    aliases: []
cloudinary:
    enable: yes
    config:
      apiKey: null
      apiSecret: null
      cloudName: null
      secure: no
    aliases: []
s3:
    enable: yes
    defaultImage: null
    namespaceBC: no # Back Compatibility. Pokud je nastaveno na TRUE, soubory budou hledány také bez prefixu `/original/`
    config:
        bucket: 'Your-Bucket'
        version: 'latest'
        region: 'eu-west-1'
        credentials:
          key: 'AWS_KEY'
          secret: 'AWS_SECRET'
    aliases: []
default: local ## Výchozí uložiště [cloudinary, local, s3]
```

### Tvorba aliasů
Aliasy umožnují snadnou modifikací obrazků

**Použití jednoho modifieru**
```yaml
local:
    aliases:
      myAlias: "resize:12,50"
```

**Více modifierů**
```yaml
local:
    aliases:
      myAlias: "resize:12,50,exact|sharpen"
```

**Použití polí**
```yaml
cloudinary:
    aliases:
      myAlias: "border:[width: 4, color: #553311]"
```

**Použití proměnných**
```yaml
local:
    aliases:
      resizeExact: "resize:$1,$2,exact"
      resize: "resize:$1,$2,$3"
      resizeSquare: "resize:$1,$1,exact"
```

### Vlastní modifiery
V konfiguraci stačí zaregistrovat loader

```yaml
local:
    modifiers:
      - ModifiersLoader
```

vytvořit třídu a přidávat modifiery
```php
class ModifiersLoader implements WebChemistry\Images\Modifiers\ILoader {
    
    public function load(WebChemistry\Images\Modifiers\ModifierContainer $modifierContainer) {
        $modifierContainer->addModifiers('custom', function (Nette\Utils\Image $image, $param) {
            // zpracovani obrazku $image
        });
    }

}
```

a použití
```yaml
local:
    aliases:
      custom: "custom:param1"
```

### Ukladaní obrázků

**$upload** - Instance Nette\Utils\Upload
**$location** - Cesta obrázku uložená v řetězci
**$storage** - Instance WebChemistry\Images\IImageStorage

Nette upload
```php
// vytvorime zdroj pro obrazek
$resource = $storage->createUploadResource($upload);
// nebo z cesty
$resource = $storage->createLocalResource($location);

// pridame namespace
$resource->setNamespace('namespace');

// ulozime
$result = $storage->save($resource);

// zobrazime url adresu
echo $storage->link($result);
```

Před nahráním obrázku ho můžeme upravit
```php
$resource->setAlias("custom");

// Kombinace více aliasů
$resource->setAliases(["custom", "custom2"]);

$id = $resource->getId(); // Ziskání id
// nebo
$id = (string) $resource;
```

Obrázek se uloží v namespace/original/obrazek.jpg

### Získávání obrázků

**$id** Identifikátor ziskány z uloženeho obrázku viz sekce ukládání obrázků

```php
$resource = $storage->createResource($id);

$link = $storage->link($resource);
```

### Kopírování obrázků

**$id** Identifikátor ziskány z uloženeho obrázku viz sekce ukládání obrázků

```php
$resource = $storage->createResource($id);
$dest = $storage->createResource("namespace/obrazek.jpg"); 

// Muzeme zmodifikovat
$dest->setAlias("custom");

$storage->copy($resource, $dest);
```

Zkopíruje se jen originální obrázek a v případně se zmodifikuje.

### Přesouvání obrázků

**$id** Identifikátor ziskány z uloženeho obrázku viz sekce ukládání obrázků

```php
$resource = $storage->createResource($id);
$dest = $storage->createResource("namespace/obrazek.jpg"); 

// Muzeme zmodifikovat
$dest->setAlias("custom");

$storage->move($resource, $dest);
```

### Odstranění obrázků

**$id** Identifikátor ziskány z uloženeho obrázku viz sekce ukládání obrázků

```php
$resource = $storage->createResource($id);

$storage->delete($id);
```

Odstraní se jak originální obrázek, tak i jeho modifikace.

### Modifikace obrázků

**$id** Identifikátor ziskány z uloženeho obrázku viz sekce ukládání obrázků

1) Uložením

```php
$resource = $storage->createResource($id);
$resource->setAlias("custom");
$storage->save($resource);
```
Uloží se do namespace/custom/obrazek.jpg

2) Získáním adresy

```php
$resource = $storage->createResource($id);
$resource->setAlias("custom");
echo $storage->link($resource);
```
Uloží se do namespace/custom/obrazek.jpg

## Šablony

Zobrazení obrázku
```html
{img 'image.jpg'}
<img n:img="'image.jpg'">
```

Zobrazení s použitím modifikátorů obrázků
```html
{img 'image.jpg', custom}
<img n:img="'image.jpg', custom">

{* Kombinace dvou aliasů *}
{img image.jpg, custom, custom1}

{* Použití proměnných v aliasu *}
{img $resource, customVariables(15,15,exact)}

```

## Formuláře

Automatickou registraci provede extenze.
S touto komponentou odpadá povinnost vytvoření třídy pro obrázek.
```php
$form->addImageUpload('image', 'Obrazek')
    ->setRequired()
    ->setNamespace('namespace');

$form->onSuccess[] = function ($form, array $values) use ($storage) {
    $storage->save($values['image']);
};    
```

## Doctrine typ

Automatickou registraci provede extenze.
Položku pro obrázek lze vytvořit přes anotaci typ **image**:

```php
class Entity {
    
    /**
     * @ORM\Column(type="image")
     */
    protected $image;

}
```

**nullable=true** změna obrázku z povinného na nepovinný

Uložení nového obrázku, bere jen instaci IFileStorage nebo NULL v případě nastaveného nullable v anotaci Column
```php
$form->onSuccess[] = function ($form, $values) {
    $en = new Entity();
    $en->image = $this->storage->save($values->image);
    
    $this->em->persist($en);
    $this->em->flush();
};
```

Získání obrázku
```php
$en = $this->em->getRepository(Entity::class)->find(1);
if ($en->image !== NULL) { // V pripade nullable
    $link = $this->storage->link($en->image);
}
```
