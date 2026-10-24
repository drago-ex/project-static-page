# Drago Project Static Page

[English](readme.md) | Čeština

Administrace statických stránek pro Drago Project.

Balíček přidává modul administrace pro správu jednoduchých stránek uložených v tabulce `static_page`. Stránky lze v datagridu vytvářet, upravovat, mazat, filtrovat a řadit.

[![PHP version](https://badge.fury.io/ph/drago-ex%2Fproject-static-page.svg)](https://badge.fury.io/ph/drago-ex%2Fproject-static-page)
[![Coding Style](https://github.com/drago-ex/project-static-page/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/project-static-page/actions/workflows/coding-style.yml)

## Požadavky

- PHP >= 8.3, < 9
- Composer

## Instalace

```bash
composer require drago-ex/project-static-page
```

## Soubory projektu

Kopírování souborů obstarává automaticky [drago-ex/project-tools](https://github.com/drago-ex/project-tools),
který musí být v projektu nainstalovaný. Bez něj zkopírujte soubory ručně podle sekce `copy`
v `composer.json` tohoto balíčku. Pro přeskočení balíčku nastavte `"skip": true` v
`extra.drago-tools.packages.<package-name>` v kořenovém `composer.json`.

## Migrace databáze

```bash
php vendor/bin/migration db:migrate --packages
```

## Assety

Balíček nemá vlastní skripty. Lišta pole s obsahem a tlačítko pro kopírování bloků kódu pocházejí z
[drago-ex/texy-toolbar](https://github.com/drago-ex/texy-toolbar); nastavte je podle jeho README (`admin.js` pro
lištu, `front.js` pro tlačítko kopírování). Stránky fungují i bez nich.

## Oprávnění

Statické stránky fungují bez oprávnění i bez [drago-ex/permission](https://github.com/drago-ex/permission):
administrace pak zobrazuje všechna svá tlačítka.

Pro statická oprávnění jsou pravidla přístupu připravená v [`permission.php`](permission.php); drago-ex/permission
z nich vygeneruje provider (nastavení popisuje jeho README):

```bash
php vendor/bin/create-permission --packages
```

Aby tlačítka pro přidání, úpravu a mazání viděly jen role s privilegiem, přidejte trait `WriteAccess` z
drago-ex/permission; jeho `isWriteAllowed()` se ptá na privilegium `{component}-write`. V
`app/Presentation/Backend/Page/Component/BaseControl.php` trait použijte a výsledek předejte do šablony
(s `public bool $writeAllowed = false;` v `BaseTemplate`):

```php
use Drago\Permission\WriteAccess;

abstract class BaseControl extends ExtraControl implements OffcanvasHandle, ModalHandle
{
	use Component;
	use WriteAccess;

	public function createRender(): Template
	{
		// ...
		$template->writeAllowed = $this->isWriteAllowed();
```

potom podle něj skryjte akce gridu a tlačítko pro přidání zobrazte jen s oprávněním:

```php
$grid->addActionEdit(fn(int $id) => $this->handleEdit($id))
	->setVisible($this->isWriteAllowed());
```

```latte
{embed '../@datagrid-heading.latte', addButton: $writeAllowed}
```

Pro dynamická oprávnění nastavovaná v administraci nainstalujte
[drago-ex/project-permission](https://github.com/drago-ex/project-permission) a ručně naimportujte privilegia administrace stránek
do jeho tabulky `resources`, aby je bylo možné přidělovat rolím:

```bash
php vendor/bin/migration db:migrate vendor/drago-ex/project-static-page/migrations-permission
```

V projektu sestaveném přes [netis](https://github.com/netis-cms/netis) tyto změny udělá project-preset
a privilegia naimportuje webový instalátor z project-install.

## Instalované soubory

Balíček kopíruje:

- `resources/app/Model/Page` do `app/Model/Page`
- `resources/app/Presentation/Backend/Page` do `app/Presentation/Backend/Page`
- `resources/app/Presentation/Front/Page` do `app/Presentation/Front/Page`

## Administrace

Presenter stránek v administraci je dostupný jako:

```latte
{link :Backend:Page:}
```

S [drago-ex/project-backend-ui](https://github.com/drago-ex/project-backend-ui) se v postranním menu objeví položka
„Statické stránky“ v sekci „Obsah“ (`Backend/Page/5.menu.neon`).

Modul používá zdroj oprávnění `Backend:Page`. Zápisové akce se kontrolují privilegiem `pages-write`. Provider administrace povoluje roli administrátora.

## Veřejná část

Publikovaná stránka se zobrazí v nejvyšší úrovni webu, např. `/o-nas` (a `/en/o-nas`). Routu přidává
`app/Presentation/Front/Page/8.conf.neon` před router veřejné části a odpovídá jen slugům publikovaných stránek;
koncepty a ostatní adresy pokračují na další routy. Administrace nedovolí slug, jehož adresa
už patří jiné stránce webu (`admin`, `sign`, `en`…).

```latte
{link :Front:Page: slug: 'o-nas'}
```

Publikovaná stránka se zaškrtnutým „Zobrazit v menu“ patří do menu veřejné části, seřazená podle „Pořadí v menu“.
`App\Model\Page\PageMenu::getItems()` vrací tyto stránky (slug => název) z cache, kterou administrace vyčistí
po uložení nebo smazání stránky. Balíček nemá layout veřejné části, vypište je proto ve svém:

```php
$this->template->menuPages = $this->pageMenu->getItems();
```

```latte
<a n:foreach="$menuPages as $slug => $title" n:href=":Front:Page:default, slug: $slug">{$title}</a>
```

V layoutu veřejné části to dělá [drago-ex/project-preset](https://github.com/drago-ex/project-preset).

Obsah stránky se píše v [Texy](https://texy.info/cs/syntax). Přímé HTML v něm je omezené na bezpečné značky a odkazy
na bezpečné URL; nadpisy obsahu začínají od `<h2>`, protože nadpis stránky je `<h1>`. Bloky kódu s jazykem
(`/--code php`, také html, latte, css, js, sql, neon) se zvýrazní třídami Bootstrapu 5. Vše pochází z
[drago-ex/texy-toolbar](https://github.com/drago-ex/texy-toolbar), jehož DI rozšíření je zaregistrované v
`app/Presentation/Front/Page/8.conf.neon`.

Popis stránky vypíše layout veřejné části z drago-ex/project-front jako `<meta name="description">`.

Provider oprávnění veřejné části používá zdroj `Front:Page` a povoluje ho návštěvníkům.
