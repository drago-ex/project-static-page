# Agent Guide

Use this file as package-specific guidance when working in this repository. Keep changes aligned with the existing public API, project conventions, and supported PHP range.

## Package Overview
- **Package**: `drago-ex/project-static-page`
- **Type**: drago-tools-resource
- **Purpose**: Static page administration for Drago Project.
- **PHP**: >=8.3 <9
- **Runtime dependencies**: nette/application (^3.2), nette/di (^3.2), nette/caching (^3.3), drago-ex/application (^4.0), drago-ex/component (^3.0), drago-ex/database (^5.0), drago-ex/datagrid (^1.0), drago-ex/form (^3.0), drago-ex/migration (^1.0), drago-ex/project-auth (^2.0), drago-ex/project-backend (^1.0), drago-ex/translator (^4.0.1), drago-ex/utils (^2.0), drago-ex/texy-toolbar (^1.0)
- **License**: MIT

## Repository Layout
- `resources/app/Presentation/Backend/Page/acl.neon` and `resources/app/Presentation/Front/Page/acl.neon`: the ACL of the package (resources = presenter names, rules only for roles other than the administrator), merged by Nette with `role.acl.neon` of drago-ex/permission (required); a new presenter gets its resource in the `acl.neon` next to it. No bin script or providers.
- `migrations/`: database migrations.
- `resources/`: application resources contributed to the consuming Drago project.
- `.github/`: CI workflows; keep their PHP versions and commands consistent with Composer and the package scripts.
- `composer.json`: package metadata, dependency constraints, autoloading, and available Composer scripts.

## Key Parts
- `static_page` (migration `001_page.sql`; default `migration-priority`): slug (unique), title, description (meta description, set as `$description` for the front layout), content, status draft/published, `in_menu` + `menu_position` (front menu). `Model/Page/PageMenu` caches the menu pages (`PageRepository::getMenuPages()`, slug => title); the package itself has no front layout, project-preset prints them in its navbar. `PageControl` cleans both `PageSlugs` and `PageMenu` after save and delete. The backend form fields are listed in `Component/Page/Page.latte`, so a new form field must be added there too. Backend texts are translated in `Backend/Page/Translate/cs.neon` (the package owns its translations). `PageRepository` reads published pages for the front.
- Backend `Backend:Page` (administrators) manages pages with the datagrid; front `Front:Page` (guests allowed on the whole resource, the `Authorization` trait checks the action as the privilege) shows a published page by slug at the top level (`/o-nas`); there is no page listing, drafts give 404.
- `Front/Page/PageRouter` (added in `Front/Page/8.conf.neon`, loaded before the front router in `9.conf.neon`) matches `[<lang>/]<slug>` only for published slugs, checked by `Model/Page/PageSlugs` (cached; the administration calls `clean()` after save and delete). `PageControl::isReservedSlug()` asks the router and the presenter factory whether `/slug` already leads to another presenter and refuses such a slug.
- Page content is Texy markup converted by `Drago\Texy\TexyFormatter` of drago-ex/texy-toolbar (safe HTML tags, http(s)/mailto links, http(s) images, headings from `<h2>`, highlighted code), so the template prints it with `|noescape` safely. `Front/Page/8.conf.neon` registers `TexyExtension` as `texy` (other packages register the same entry); `PageControl` takes the toolbar options and the preview from `Drago\Texy\TexyToolbar`, whose labels are translated by texy-toolbar itself.

## Buttons and permissions
- Permission code is in the package (2026-11-03, step 6 of the ACL-in-neon rework; before, only README steps and project-preset-permission copies): `BaseControl` uses `AccessCheck` of drago-ex/permission, grid actions `setVisible($this->isAllowed(...))`, `addButton: $control->isAllowed('edit')`. Privileges of `Backend:Page`: `default`; `edit` (`createComponentPage`, row edit); `delete` (delete dialog + form, row delete). `#[Access]` (page enough): `openModal`, `openOffcanvas`, `preview`.
- `@datagrid-heading.latte` shows the add button with `addButton: true`.
- The add/edit form in the full-screen offcanvas has two columns (2026-10-24, the user's choice over tabs or an accordion, which would hide fields with errors): the title and the content on the left (`col-lg-8`), the settings in cards on the right (`col-lg-4`), and the buttons in a `sticky-bottom` bar, so they stay visible while the content scrolls. A new field goes into the fitting card, nothing is hidden.
- Page form cards (no headings, the field labels are enough): status; in_menu and menu_position; slug and meta description.
- Privileges for drago-ex/project-permission are read from the code (`PrivilegeCatalog` of drago-ex/permission: pages, `handle*()` signals, forms, `#[Access]`); there is no `migrations-permission/` any more (removed 2026-11-03). A new page or button needs no list update.

## Dependencies

Composer constraints describe the package contract. Before changing integration behavior, inspect the source and the actual call sites in this package; do not infer runtime usage solely from dependency declarations.
Development dependencies include nette/tester (^2.5), phpstan/phpstan-nette (^2.0), drago-ex/project-tools (^1.0).

## Essential Commands

```bash
composer tester                 # tester tests -s -C
composer phpstan                # phpstan analyse
```

## Tests

Nette Tester without a database: `PageRouter` with `PageSlugs` (matching only published slugs, links, cache cleaning). The repository is an anonymous subclass of `PageRepository` on a lazy Dibi connection, as in project-auth.

## Installation Hooks

Composer install and update hooks invoke Drago project setup/cleanup commands. Inspect those scripts and their effects before invoking Composer operations that trigger them; they may modify generated project files.

## Conventions & Rules

- Preserve backward compatibility unless the task explicitly permits a breaking change. Check callers and public signatures before changing behavior.
- Follow the existing PHP style: strict types, explicit parameter and return types, tabs for indentation, and single-quoted strings where practical. Match nearby code when a convention is unclear.
- Keep package-specific configuration, resources, and migrations in their established directories. Do not edit generated or consuming-project files without confirming they are part of this repository’s intended output.
- Add or update focused tests for behavioral changes when this package has a runnable test setup. Do not invent test commands; use only the scripts and configuration present here.
- Update README examples and Composer metadata when a public API or supported requirement changes.
- Menu: `Backend/Page/5.menu.neon` adds the item "Static pages" to project-backend-ui (user, 2026-11-04, reverses the 2026-10-27 rule that project-preset ships the menu neons): the sidebar parameter is unused without project-backend-ui, which hides items the user may not open.
