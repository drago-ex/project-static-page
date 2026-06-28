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

## Installed Files

The package copies:

- `resources/app/Model/Page` to `app/Model/Page`
- `resources/app/Presentation/Backend/Page` to `app/Presentation/Backend/Page`

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

The module uses the permission resource `Backend:Page`. Write actions are checked with the `pages-write` privilege.

## Frontend Usage

Use `PageRepository` from any frontend presenter or component and load only published pages by slug:

```php
use App\Model\Page\PageRepository;

final class PagePresenter extends BasePresenter
{
	public function __construct(
		private readonly PageRepository $pageRepository,
	) {
		parent::__construct();
	}

	public function renderDefault(string $slug): void
	{
		$page = $this->pageRepository->getPublishedBySlug($slug);
		if ($page === null) {
			$this->error();
		}

		$this->template->page = $page;
	}
}
```

Template example:

```latte
{templateType App\Presentation\Front\Page\PageTemplate}

{block content}
	<h1>{$page->title}</h1>
	<div>{$page->content|noescape}</div>
{/block}
```

Use `|noescape` only when the page content is trusted HTML edited by an administrator.
