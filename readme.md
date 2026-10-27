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

A published page with "Show in the menu" belongs to the front menu, ordered by "Position in the menu".
`App\Model\Page\PageMenu::getItems()` returns those pages (slug => title) from a cache, which the administration
cleans when a page is saved or deleted. The package has no front layout, so print them in yours:

```php
$this->template->menuPages = $this->pageMenu->getItems();
```

```latte
<a n:foreach="$menuPages as $slug => $title" n:href=":Front:Page:default, slug: $slug">{$title}</a>
```

[drago-ex/project-preset](https://github.com/drago-ex/project-preset) does it in its front layout.

Page content is written in [Texy](https://texy.info/en/syntax). Raw HTML in it is limited to safe tags and links
to safe URLs; content headings start at `<h2>`, as the page title is the `<h1>`. Code blocks with a language
(`/--code php`, also html, latte, css, js, sql, neon) are highlighted with Bootstrap 5 classes. All of it comes from
[drago-ex/texy-toolbar](https://github.com/drago-ex/texy-toolbar), whose DI extension is registered in
`app/Presentation/Front/Page/8.conf.neon`.

The description of a page is printed as `<meta name="description">` by the front layout of drago-ex/project-front.

The frontend permission provider uses the resource `Front:Page` and allows it to guests.

---

## Optional add-ons

The package works without them. Set them up by hand as described below when the project needs them.

### Permissions

Static pages work without permissions and without [drago-ex/permission](https://github.com/drago-ex/permission):
the administration shows all its buttons.

For static permissions, the access rules are prepared in [`bin/create-page-permission`](bin/create-page-permission); drago-ex/permission
generates a provider from it (set up as its README shows):

```bash
php vendor/bin/drago-commands
```

To show the add, edit and delete buttons only to roles with the privilege, add the `WriteAccess` trait of
drago-ex/permission; its `isWriteAllowed()` asks for the `{component}-write` privilege. In
`app/Presentation/Backend/Page/Component/BaseControl.php` use the trait and pass the result to the template
(with `public bool $writeAllowed = false;` in `BaseTemplate`):

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

then hide the grid actions by it and show the add button only when allowed:

```php
$grid->addActionEdit(fn(int $id) => $this->handleEdit($id))
	->setVisible($this->isWriteAllowed());
```

```latte
{embed '../@datagrid-heading.latte', addButton: $writeAllowed}
```

For dynamic permissions set in the administration, install
[drago-ex/project-permission](https://github.com/drago-ex/project-permission) and import the privileges of the page administration
into its `resources` table by hand, so roles can be given them:

```bash
php vendor/bin/migration db:migrate vendor/drago-ex/project-static-page/migrations-permission
```

In a project composed by [netis](https://github.com/netis-cms/netis), project-preset makes these changes for you
and the web installer of project-install imports the privileges.
