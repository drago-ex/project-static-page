# Agent Guide

`drago-ex/project-static-page` (type drago-tools-resource, PHP >=8.3 <9): static pages with their own URL and an
optional front menu item, managed in the administration. The workspace rules are in `../AGENTS.md`; dependencies
are in `composer.json`.

## Layout
- `migrations/001_page.sql`: `static_page` (slug unique, title, description, content, status draft/published,
  `in_menu`, `menu_position`).
- `resources/app/Model/Page`: `PageRepository` (published pages for the front), `PageSlugs`, `PageMenu`.
- `resources/app/Presentation/Backend/Page`: `PagePresenter`, `Component/Page` (`PageControl`, `Page.latte`),
  `5.menu.neon`, `acl.neon`, `Translate/cs.neon`.
- `resources/app/Presentation/Front/Page`: `PagePresenter`, `PageRouter`, `8.conf.neon`, `acl.neon`.
- `tests/`: Nette Tester without a database.

## How it works
- Front: `PageRouter` (added in `8.conf.neon`, loaded before the front router in `9.conf.neon`) matches
  `[<lang>/]<slug>` only for published slugs, checked by `PageSlugs` (cached). There is no page listing; drafts
  give 404. `Front:Page` is allowed to guests on the whole resource.
- `PageControl::isReservedSlug()` asks the router and the presenter factory whether `/slug` already leads to
  another presenter and refuses such a slug.
- `PageMenu` caches the menu pages (`PageRepository::getMenuPages()`, slug => title). The package has no front
  layout; project-front-ui prints them in its navbar. The page of the website is `article.blog-article` with the text
  in `blog-content` (the compostrap module `blog`, imported by `front.scss` of project-front-ui). `PageControl` cleans `PageSlugs` and `PageMenu` after save
  and delete.
- The description is set as `$description`, which the front layout prints as meta description.
- Content is Texy converted by `Drago\Texy\TexyFormatter` (safe tags, http(s)/mailto links, headings from `<h2>`,
  highlighted code), so the template prints it with `|noescape`. `8.conf.neon` registers `TexyExtension` as
  `texy` (other packages register the same entry); `PageControl` takes the toolbar and the preview from
  `Drago\Texy\TexyToolbar`.
- The form fields are listed in `Page.latte`, so a new field must be added there too. The form is a full-screen
  offcanvas with two columns: title and content left (`col-lg-8`), cards right (`col-lg-4`, no headings): status;
  `in_menu` + `menu_position`; slug + description. Buttons are in the `offcanvas-actions` bar of compostrap (the cards are `card border-0 bg-body-tertiary`, without a shadow). Nothing is hidden in
  tabs or accordions, so a field with an error stays visible.

## Permissions
`BaseControl` uses `AccessCheck`; grid actions `setVisible($this->isAllowed(...))`,
`addButton: $control->isAllowed('edit')` of `@datagrid-heading.latte`. Privileges of `Backend:Page`: `default`;
`edit` (`createComponentPage`, the Edit of the row menu); `delete` (dialog, form, the Delete of the row menu and of the bar); `publish`
(the menu of the status badge in the grid, `setState()` of drago-ex/datagrid, both ways); `export` (CSV). `openModal`, `openOffcanvas`, `preview` need only the page
(`#[Access]`). project-permission reads the privileges from the code.

Every grid has inline filters and no row click that opens the form: a row has a menu of three dots (row actions of drago-ex/datagrid, `addActionEdit()` and `addActionDelete()`, the latter calls `handleDelete([$id])`), the bar under the table has the bulk actions (Export, Delete), a click anywhere on a row chooses it (`setRowClickAction('select')`), states are badges that are only read (`setBadges()`). The name of a row or bulk action is its privilege, and the control has a `handle<Name>()` method the callback calls, so `PrivilegeCatalog` finds the privilege; `export` comes from `DataGrid::handleExport()`. `BaseControl::handleDelete(array $ids)` opens one confirmation for all chosen items (`$deleteTitles`, the hidden field `ids` of the delete form) and `deleteItem()` runs for each. The state (published or draft, `handlePublish(int $id, bool $publish)`) is changed in its column by the menu of its badge (`setState()`), not by a bulk action. The grid has the columns Title, Updated at (date range filter) and Status last; the slug is only in the form.

## Commands
```bash
composer phpstan
composer tester     # PageRouter with PageSlugs; the repository is an anonymous subclass on a lazy Dibi connection
```
