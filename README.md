[![Build Status](https://travis-ci.org/WebChemistry/images.svg?branch=master)](https://travis-ci.org/WebChemistry/images)

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
enable: yes
defaultImage: null
wwwDir: %wwwDir%
assetsDir: assets
modifiers: []
aliases: []
hashResolver: WebChemistry\Images\Resolvers\HashResolver ## Vysvětleno níže
namespaceResolver: WebChemistry\Images\Resolvers\NamespaceResolver ## Vysvětleno níže
registerControl: yes ## Zaregistruje UploadControl
registerType: yes ## Zaregistruje doctrine typ 'image' 
safeLink: %productionMode% ## metoda link() se zotavuje z chyb a loguje tyto chyby do tracy, web nespadne do 500 kvůli chybnému obrázku
```

### Skladba cesty k obrázků

%wwwDir%/%assetsDir%/namespace/resize/image.png
%wwwDir%/%assetsDir%/namespace/original/image.png

namespace/ - Má na starosti třída namespaceResolver
resize/ - Má na starosti třída hashResolver 

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
        $modifierContainer->addModifier('custom', function (ModifierParam $param, $foo) {
            // zpracovani obrazku $param->getImage()
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

## Dávkování obrázků

```php
$batch = $storage->createBatch();

$entity->image = $batch->save($resource);
$this->em->persist($entity);

$entity2->image = $batch->save($resource2);
$this->em->persist($entity2);

$batch->flush();
$this->em->flush();
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

Pro náhledový obrázek a input pro odstranění obrázků:
```php
$form->addImageUpload('image', 'Obrázek')
    ->setDelete('Odstranit obrázek')
    ->setNamespace('namespace');
    
$form->onSuccess[] = function ($form, array $values) use ($storage) {
	$image = $values['image'];
	if ($image->getDelete()) {
		$storage->delete($image->getDelete());
	}
	if ($image->getUpload()) {
    	$resource = $storage->save($image->getUpload());
	} else {
		$resource = $image->getDefaultValue();
	}
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
