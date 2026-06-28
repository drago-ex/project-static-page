# Drago Project Static Page

Static page administration for Drago Project.

The package adds a backend module for managing simple pages stored in the `static_page` table. Pages can be created, edited, deleted, filtered and sorted in the datagrid.

## Installation

```bash
composer require drago-ex/project-static-page
```

Run project setup after installation:

```bash
php vendor/bin/drago-setup
```

The setup command can run the package migration:

```bash
php vendor/bin/migration db:migrate vendor/drago-ex/project-static-page/migrations
```

It can also generate permission providers:

```bash
php vendor/bin/create-page-permission
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

If you use the bundled backend sidebar, add the menu item in your backend presenter:

```php
$builder->addSection('Content')
	->addItem('Static pages', 'Page:')
	->setIcon('fa-regular fa-file-lines');
```

The module uses the permission resource `Backend:Page`. Write actions are checked with the `pages-write` privilege. The backend provider allows the administrator role.

## Frontend Usage

The package includes a simple frontend presenter for listing published pages and rendering a detail by slug:

```latte
{link :Front:Page:}
{link :Front:Page:detail, slug: 'about'}
```

Add routes to your frontend router if you want clean URLs:

```php
$router->withModule('Front')
	->addRoute('[<lang=cs cs|en>/]pages/<slug>', 'Page:detail')
	->addRoute('[<lang=cs cs|en>/]pages', 'Page:default');
```

You can also use `PageRepository` directly from any presenter or component:

```php
$pages = $this->pageRepository->getPublishedPages()->recordAll();
$page = $this->pageRepository->getPublishedBySlug($slug);
```

The bundled detail template uses `|noescape` for page content. Use it only when the content is trusted HTML edited by an administrator.

The frontend permission provider uses the resource `Front:Page`. The guest role can use `pages-read` and `pages-view`, so public page listing and page detail can stay available without login.
