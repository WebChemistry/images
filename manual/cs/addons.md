# Doplňky

## Upload control

Slouží pro automatické nahrávání a odstraňování obrázků.

**Instalace:**

Při registrování extenze se automaticky připojí k Nette formulářím, pokud to nezakážeme v config souboru.

**Použítí:**

```php

protected function createComponentForm() {
    $form = new Nette\Application\UI\Form;

    $row = $this->getFromDatabase();

    $form->addImageUpload('upload', 'Upload')
            ->setDefaultValue($row->upload) // Obsahuje např. namespace/upload.png
            ->setNamespace('namespace');

    // nebo
    $form->addImageUpload('upload2', 'Upload 2', 'namespace', $row->upload);

    $form->onSuccess[] = $this->afterForm;

    return $form;
}

public function afterForm($form, $values) {
    $row = $this->getFromDatabase();
    
    $row->upload = $values->upload; // Obsahuje namespace/unikatniNazevObrazku.png nebo NULL, když není vyplněno pole nebo zaškrtnuto odstranění.

    $row->update();
}

```

**Html náhledu**

```html
<div class="upload-preview-image-container">
    <a href="/assets/namespace/original/upload.png"><img class="upload-preview-image" src="/assets/namespace/original/upload.png"></a>
</div>
```

## Povinné pole

Pří výchozí hodnotě se objeví checkbox + upload.

**Nastanou tyto situace:**

Obrázek nemá výchozí hodnotu nebo obrázek neexistuje:<br>
*Obrázek není nahrán*: Chyba.<br>
*Obrázek je nahrán*: Úspěch.

Pole má výchozí hodnotu:<br>
*Obrázek není nahrán a checkbox není zaškrnutý*: Úspěch.<br>
*Obrázek je nahrán a checkbox není zaškrtnutý*: Úspěch. (Možná v budoucnu chyba?)<br>
*Obrázek je nahrán a checkbox je zaškrtnutý*: Úspěch.<br>
*Obrázek není nahrán a checkbox je zaškrtnutý*: Chyba.<br>

## Funkce

```php

$upload->setPreviewSize(200, 200); // Nastaví pevnou velikost náhledu

$upload->isUpload(); // FALSE = Zaškrtnuto mazání nebo obrázek nebyl nahrán

$upload->getRawValue(); // Vrátí bolean nebo pole uploadů

```
