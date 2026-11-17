# Drago Project Static Page

Static pages for Drago Project: pages such as "About us" with their own address and an optional item in the
website menu, written in the administration.

[![PHP version](https://badge.fury.io/ph/drago-ex%2Fproject-static-page.svg)](https://badge.fury.io/ph/drago-ex%2Fproject-static-page)
[![Coding Style](https://github.com/drago-ex/project-static-page/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/project-static-page/actions/workflows/coding-style.yml)

## Requirements

- PHP >= 8.3, < 9
- MySQL or MariaDB
- [Drago Project](https://github.com/drago-ex/project)

## Installation

```bash
composer require drago-ex/project-static-page
```

[Project Tools](https://github.com/drago-ex/project-tools) copies the files of the package into the project,
where you adapt them. Set up what [Assets](#assets) lists and build the assets again (`npm run vite:build`).

## Database

The migration creates the table `static_page`, empty:

```bash
php vendor/bin/migration db:migrate --packages
```

Pages are then written in the administration. `bin/project-setup` of Drago Project runs the migration for you.

## Assets

The package has no scripts of its own. It uses:

- [drago-ex/datagrid](https://github.com/drago-ex/datagrid): the list of pages, set up in `admin.js` and `admin.scss`.
- [drago-ex/texy-toolbar](https://github.com/drago-ex/texy-toolbar): the toolbar of the content field (`admin.js`)
  and the copy button of code blocks (`front.js`).

A page on the website is in the look of the module `blog` of [compostrap](https://github.com/compostrap/compostrap)
(the article and its content). [Front UI](https://github.com/drago-ex/project-front-ui) already imports it; otherwise
import it in `assets/front.scss`:

```scss
@import 'compostrap/src/scss/blog';
```

## Administration

```latte
{link :Backend:Page:}
```

The menu item comes from `Backend/Page/5.menu.neon` with
[Backend UI](https://github.com/drago-ex/project-backend-ui). Pages are created, edited, deleted, filtered and
sorted in a datagrid. A page has a title, content, slug, meta description, status (draft or published) and
"Show in the menu" with its position. A slug whose address already belongs to another page of the site (`admin`,
`sign`, `en`…) is refused.

## Website

A published page is shown at the top level of the site, e.g. `/o-nas` (and `/en/o-nas`). The route is added by
`Front/Page/8.conf.neon` before the front router and matches only slugs of published pages.

```latte
{link :Front:Page: slug: 'o-nas'}
```

Content is written in [Texy](https://texy.info/en/syntax) and printed safely: raw HTML is limited to safe tags and
links, headings start at `<h2>` and code blocks with a language are highlighted. The description is the
`<meta name="description">` of the page.

Pages with "Show in the menu" are returned by `App\Model\Page\PageMenu::getItems()` (slug => title, cached,
cleaned when a page is saved or deleted). Print them in your layout
([Front UI](https://github.com/drago-ex/project-front-ui) does it in its navbar):

```php
$this->template->menuPages = $this->pageMenu->getItems();
```

```latte
<a n:foreach="$menuPages as $slug => $title" n:href=":Front:Page:default, slug: $slug">{$title}</a>
```

## Permissions

The rules are in `Backend/Page/acl.neon` (resource `Backend:Page`, the administrator only) and
`Front/Page/acl.neon` (resource `Front:Page`, everyone). The privileges of `Backend:Page` are `default`, `edit`
and `delete`; buttons are shown only to those who may use them.
[Project Permission](https://github.com/drago-ex/project-permission) gives them to other roles.

The same files describe the privileges (`permissionLabels`) for the permissions page of [Project Permission](https://github.com/drago-ex/project-permission).
