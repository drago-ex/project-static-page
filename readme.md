# Drago Project Static Page

Static page administration for Drago Project.

The package adds a backend module for managing simple pages stored in the `static_page` table. Pages can be created, edited, deleted, filtered and sorted in the datagrid.

[![PHP version](https://badge.fury.io/ph/drago-ex%2Fproject-static-page.svg)](https://badge.fury.io/ph/drago-ex%2Fproject-static-page)
[![Coding Style](https://github.com/drago-ex/project-static-page/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/project-static-page/actions/workflows/coding-style.yml)

## Requirements

- PHP >= 8.3, < 9
- [Drago Project](https://github.com/drago-ex/project)

## Installation

```bash
composer require drago-ex/project-static-page
```

## Files copied into the project

The files are copied into the project by [drago-ex/project-tools](https://github.com/drago-ex/project-tools),
as set in `extra.drago-tools` of this package's `composer.json`.

## Database migration

```bash
php vendor/bin/migration db:migrate --packages
```

## Assets

The package has no own scripts. The toolbar of the content field and the copy button of code blocks come from
[drago-ex/texy-toolbar](https://github.com/drago-ex/texy-toolbar); set them up as its README shows (`admin.js` for
the toolbar, `front.js` for the copy button). Without them the pages work too.

The list of pages comes from [drago-ex/datagrid](https://github.com/drago-ex/datagrid); set up its assets in `admin.js` and
`admin.scss` as its README shows.

## Backend

The backend page presenter is available as:

```latte
{link :Backend:Page:}
```

With [drago-ex/project-backend-ui](https://github.com/drago-ex/project-backend-ui), the page gets its menu item from `Backend/Page/5.menu.neon`.

The module uses the permission resource `Backend:Page`.

## Frontend

A published page is shown at the top level of the site, e.g. `/o-nas` (and `/en/o-nas`). The route is added by
`app/Presentation/Front/Page/8.conf.neon`, before the front router, and matches only slugs of published pages;
drafts and other addresses go on to the other routes. The administration does not allow a slug whose address
already belongs to another page of the site (`admin`, `sign`, `en`…).

```latte
{link :Front:Page: slug: 'o-nas'}
```

A published page with "Show in the menu" belongs to the front menu, ordered by "Position in the menu".
`App\Model\Page\PageMenu::getItems()` returns those pages (slug => title) from a cache, which the administration
cleans when a page is saved or deleted. The package has no front layout, so print them in yours:

```php
$this->template->menuPages = $this->pageMenu->getItems();
```

```latte
<a n:foreach="$menuPages as $slug => $title" n:href=":Front:Page:default, slug: $slug">{$title}</a>
```

Page content is written in [Texy](https://texy.info/en/syntax). Raw HTML in it is limited to safe tags and links
to safe URLs; content headings start at `<h2>`, as the page title is the `<h1>`. Code blocks with a language
(`/--code php`, also html, latte, css, js, sql, neon) are highlighted with Bootstrap 5 classes. All of it comes from
[drago-ex/texy-toolbar](https://github.com/drago-ex/texy-toolbar), whose DI extension is registered in
`app/Presentation/Front/Page/8.conf.neon`.

The description of a page is printed as `<meta name="description">` by the front layout of drago-ex/project-front.

The resource `Front:Page` is allowed to guests.

## Access rules

The access rules are in `acl.neon` next to the presenters: `app/Presentation/Backend/Page/acl.neon` (the resource `Backend:Page`, for the administrator only)
and `app/Presentation/Front/Page/acl.neon` (the resource `Front:Page` for everyone).
[drago-ex/permission](https://github.com/drago-ex/permission) merges them with its base roles, in which the administrator is allowed everything.

The privileges of the administration (`Backend:Page`) are `default` (the page), `edit` (adding and editing pages)
and `delete`. Its buttons are shown only to those who may use them. With
[drago-ex/project-permission](https://github.com/drago-ex/project-permission), roles can be given these privileges
in its administration.
