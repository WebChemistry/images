# Doplňky

## Upload control

Slouží pro automatické nahrávání a odstraňování obrázků včetně náhledu.

**Instalace:**

Při registrování extenze se automaticky připojí k Nette formulářím, pokud to nezakážeme v config souboru.

**Použítí:**

```php

protected function createComponentForm() {
    $form = new Nette\Application\UI\Form;

    $row = $this->getFromDatabase();

    $form->addImageUpload('upload', 'Upload')
            ->setDefaultValue($row->upload) // Obsahuje např. namespace/upload.png
            ->setRequired()
            ->addRule($form::MAX_FILE_SIZE, NULL, 1024)
            ->setNamespace('namespace');

    $form->onSuccess[] = $this->successForm;

    return $form;
}

public function successForm($form, $values) {
    $row = $this->getFromDatabase();
    
    $row->upload = $values->upload; // Obsahuje namespace/unikatniNazevObrazku.png nebo NULL, když není vyplněno pole nebo zaškrtnuto odstranění.

    $row->update();
}

```

## Funkce

```php

$upload->getCheckbox()->setHeight(150); // Nastaví pevnou velikost náhledu
$upload->getCheckbox()->setWidth(150); // Vrátí bolean nebo pole uploadů

```
