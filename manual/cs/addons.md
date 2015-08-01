# Doplòky

## Upload control

Slouží pro automatické nahrávání a odstraòování obrázkù.

**Instalace:**

Pøi registrování extenze se automaticky pøipojí k Nette formuláøím, pokud to nezakážeme v config souboru.

**Použítí:**

```php

protected function createComponentForm() {
    $form = new Nette\Application\UI\Form;

    $row = $this->getFromDatabase();

    $form->addImageUpload('upload', 'Upload')
            ->setDefaultValue($row->upload) // Obsahuje napø. namespace/upload.png
            ->setNamespace('namespace');

    // nebo
    $form->addImageUpload('upload2', 'Upload 2', 'namespace', $row->upload);

    $form->onSuccess[] = $this->afterForm;

    return $form;
}

public function afterForm($form, $values) {
    $row = $this->getFromDatabase();
    
    $row->upload = $values->upload; // Obsahuje namespace/unikatniNazevObrazku.png nebo NULL, když není vyplnìno pole nebo zaškrtnuto odstranìní.

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

Pøí výchozí hodnotì se objeví checkbox + upload.

**Nastanou tyto situace:**

Obrázek nemá výchozí hodnotu nebo obrázek neexistuje:
*Obrázek není nahrán*: Chyba.
*Obrázek je nahrán*: Úspìch.

Pole má výchozí hodnotu:
*Obrázek není nahrán a checkbox není zaškrnutý*: Úspìch.
*Obrázek je nahrán a checkbox není zaškrtnutý*: Úspìch. (Možná v budoucnu chyba?)
*Obrázek je nahrán a checkbox je zaškrtnutý*: Úspìch.
*Obrázek není nahrán a checkbox je zaškrtnutý*: Chyba.

## Funkce

```php

$upload->setPreviewSize(200, 200); // Nastaví pevnou velikost náhledu

$upload->isUpload(); // FALSE = Zaškrtnuto mazání nebo obrázek nebyl nahrán

$upload->getRawValue(); // Vrátí bolean nebo pole uploadù

```
