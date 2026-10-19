# Drago Project Static Page

Static page administration for Drago Project.

The package adds a backend module for managing simple pages stored in the `static_page` table. Pages can be created, edited, deleted, filtered and sorted in the datagrid.

[![PHP version](https://badge.fury.io/ph/drago-ex%2Fproject-static-page.svg)](https://badge.fury.io/ph/drago-ex%2Fproject-static-page)
[![Coding Style](https://github.com/drago-ex/project-static-page/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/project-static-page/actions/workflows/coding-style.yml)

## Requirements

- PHP >= 8.3, < 9
- Composer

## Installation

```bash
composer require drago-ex/project-static-page
```

## Project files

File copying is handled automatically by [drago-ex/project-tools](https://github.com/drago-ex/project-tools),
which must be installed in your project. Without it, copy the files manually according to the `copy` section
in this package's `composer.json`. To skip this package, set `"skip": true` under
`extra.drago-tools.packages.<package-name>` in your root `composer.json`.

## Database migration

```bash
php vendor/bin/migration db:migrate --packages
```

## Assets

The package has no own scripts. The toolbar of the content field comes from
[drago-ex/texy-toolbar](https://github.com/drago-ex/texy-toolbar), as does the copy button of code blocks; set
them up as its README shows (`admin.js` for the toolbar, `front.js` for the copy button). Without them the pages
work too.

## Permission provider

With [drago-ex/permission](https://github.com/drago-ex/permission), the access rules from [`permission.php`](permission.php)
are generated into the project by:

```bash
php vendor/bin/create-permission --packages
```

## Installed Files

The package copies:

- `resources/app/Model/Page` to `app/Model/Page`
- `resources/app/Presentation/Backend/Page` to `app/Presentation/Backend/Page`
- `resources/app/Presentation/Front/Page` to `app/Presentation/Front/Page`

## Backend

The backend page presenter is available as:

```latte
{link :Backend:Page:}
```

With [drago-ex/project-backend-ui](https://github.com/drago-ex/project-backend-ui), the item "Static pages"
appears in the sidebar under "Content" (`Backend/Page/5.menu.neon`).

The module uses the permission resource `Backend:Page`. Write actions are checked with the `pages-write` privilege. The backend provider allows the administrator role.

## Frontend

A published page is shown at the top level of the site, e.g. `/o-nas` (and `/en/o-nas`). The route is added by
`app/Presentation/Front/Page/8.conf.neon`, before the front router, and matches only slugs of published pages;
drafts and other addresses go on to the other routes. The administration does not allow a slug whose address
already belongs to another page of the site (`admin`, `sign`, `en`…).

```latte
{link :Front:Page: slug: 'o-nas'}
```

Page content is written in [Texy](https://texy.info/en/syntax). Raw HTML in it is limited to safe tags and links
to safe URLs; content headings start at `<h2>`, as the page title is the `<h1>`. Code blocks with a language
(`/--code php`, also html, latte, css, js, sql, neon) are highlighted by
[drago-ex/texy-toolbar](https://github.com/drago-ex/texy-toolbar) with Bootstrap 5 classes. Its DI extension is
registered by `app/Presentation/Front/Page/8.conf.neon` as `texy`; if the project registers it already, remove
it there.

The description of a page is printed as `<meta name="description">` by the front layout of drago-ex/project-front.

The frontend permission provider uses the resource `Front:Page` and allows it to guests.
