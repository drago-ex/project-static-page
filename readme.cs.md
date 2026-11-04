# Drago Project Static Page

Administrace statických stránek pro Drago Project.

Balíček přidává modul administrace pro správu jednoduchých stránek uložených v tabulce `static_page`. Stránky lze v datagridu vytvářet, upravovat, mazat, filtrovat a řadit.

[![PHP version](https://badge.fury.io/ph/drago-ex%2Fproject-static-page.svg)](https://badge.fury.io/ph/drago-ex%2Fproject-static-page)
[![Coding Style](https://github.com/drago-ex/project-static-page/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/project-static-page/actions/workflows/coding-style.yml)

## Požadavky

- PHP >= 8.3, < 9
- [Drago Project](https://github.com/drago-ex/project)

## Instalace

```bash
composer require drago-ex/project-static-page
```

## Soubory kopírované do projektu

Soubory do projektu kopíruje [drago-ex/project-tools](https://github.com/drago-ex/project-tools)
podle nastavení v `extra.drago-tools` v `composer.json` tohoto balíčku.

## Migrace databáze

```bash
php vendor/bin/migration db:migrate --packages
```

## Assety

Balíček nemá vlastní skripty. Lišta pole s obsahem a tlačítko pro kopírování bloků kódu pocházejí z
[drago-ex/texy-toolbar](https://github.com/drago-ex/texy-toolbar); nastavte je podle jeho README (`admin.js` pro
lištu, `front.js` pro tlačítko kopírování). Stránky fungují i bez nich.

Seznam stránek pochází z [drago-ex/datagrid](https://github.com/drago-ex/datagrid); nastavte jeho assety v `admin.js`
a `admin.scss` podle jeho README.

## Administrace

Presenter stránek v administraci je dostupný jako:

```latte
{link :Backend:Page:}
```

S [drago-ex/project-backend-ui](https://github.com/drago-ex/project-backend-ui) dostane stránka položku menu z `Backend/Page/5.menu.neon`.

Modul používá zdroj oprávnění `Backend:Page`.

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

Obsah stránky se píše v [Texy](https://texy.info/cs/syntax). Přímé HTML v něm je omezené na bezpečné značky a odkazy
na bezpečné URL; nadpisy obsahu začínají od `<h2>`, protože nadpis stránky je `<h1>`. Bloky kódu s jazykem
(`/--code php`, také html, latte, css, js, sql, neon) se zvýrazní třídami Bootstrapu 5. Vše pochází z
[drago-ex/texy-toolbar](https://github.com/drago-ex/texy-toolbar), jehož DI rozšíření je zaregistrované v
`app/Presentation/Front/Page/8.conf.neon`.

Popis stránky vypíše layout veřejné části z drago-ex/project-front jako `<meta name="description">`.

Zdroj `Front:Page` je povolený návštěvníkům.

## Pravidla přístupu

Pravidla přístupu jsou v `acl.neon` vedle presenterů: `app/Presentation/Backend/Page/acl.neon` (zdroj `Backend:Page` jen pro administrátora)
a `app/Presentation/Front/Page/acl.neon` (zdroj `Front:Page` pro všechny).
[drago-ex/permission](https://github.com/drago-ex/permission) je sloučí se svými výchozími rolemi, ve kterých administrátor smí všechno.

Privilegia administrace (`Backend:Page`) jsou `default` (stránka), `edit` (přidání a úpravy stránek) a `delete`.
Její tlačítka se zobrazí jen těm, kdo je smí použít. S
[drago-ex/project-permission](https://github.com/drago-ex/project-permission) lze tato privilegia přidělit rolím
v jeho administraci.
