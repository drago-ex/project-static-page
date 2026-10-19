# Agent Guide

Use this file as package-specific guidance when working in this repository. Keep changes aligned with the existing public API, project conventions, and supported PHP range.

## Package Overview
- **Package**: `drago-ex/project-static-page`
- **Type**: drago-tools-resource
- **Purpose**: Static page administration for Drago Project.
- **PHP**: >=8.3 <9
- **Runtime dependencies**: nette/application (^3.2), nette/di (^3.2), nette/caching (^3.3), drago-ex/application (^3.0), drago-ex/component (^2.0), drago-ex/database (^3.0), drago-ex/datagrid (^1.0), drago-ex/form (^2.0), drago-ex/migration (^2.3), drago-ex/project-auth (^2.0), drago-ex/project-backend (^2.0), drago-ex/translator (^4.0), drago-ex/utils (^2.0), texy/texy (^3.2), drago-ex/texy-toolbar (dev-main)
- **License**: MIT

## Repository Layout
- `permission.php`: basic access rules for `drago-ex/permission`, declared in `extra.drago-permission` and generated into the project by `create-permission --packages`.
- `migrations/`: database migrations.
- `resources/`: application resources contributed to the consuming Drago project.
- `.github/`: CI workflows; keep their PHP versions and commands consistent with Composer and the package scripts.
- `composer.json`: package metadata, dependency constraints, autoloading, and available Composer scripts.

## Key Parts
- `static_page` (migrations `001_page.sql`, `002_page_description.sql`; default `migration-priority`): slug (unique), title, description (meta description, set as `$description` for the front layout), content, status draft/published. The backend form fields are listed in `Component/Page/Page.latte`, so a new form field must be added there too. Backend texts are translated in `Backend/Page/Translate/cs.neon` (the package owns its translations). `PageRepository` reads published pages for the front.
- Backend `Backend:Page` (administrators) manages pages with the datagrid; front `Front:Page` (guests allowed on the whole resource, the `Authorization` trait checks `{action}-read`) shows a published page by slug at the top level (`/o-nas`); there is no page listing, drafts give 404.
- `Front/Page/PageRouter` (added in `Front/Page/8.conf.neon`, loaded before the front router in `9.conf.neon`) matches `[<lang>/]<slug>` only for published slugs, checked by `Model/Page/PageSlugs` (cached; the administration calls `clean()` after save and delete). `PageControl::isReservedSlug()` asks the router and the presenter factory whether `/slug` already leads to another presenter and refuses such a slug.
- The package ships no `PermissionProvider` classes and no config registering them: they are generated from `permission.php`, and the project (`project-permission` or the `drago-ex/permission` config) registers them.
- Page content is Texy markup converted by `Model/Page/PageContent` (safe HTML tags, http(s)/mailto links, http(s) images, headings from `<h2>`), so the template prints it with `|noescape` safely. Code blocks are highlighted by `Drago\Texy\CodeBlockHandler` from drago-ex/texy-toolbar, autowired into `PageContent`; its `TexyExtension` is registered as `texy` in `Front/Page/8.conf.neon`.

## Dependencies

Composer constraints describe the package contract. Before changing integration behavior, inspect the source and the actual call sites in this package; do not infer runtime usage solely from dependency declarations.
Development dependencies include nette/tester (^2.5), phpstan/phpstan-nette (^2.0), drago-ex/project-tools (^2.0).

## Essential Commands

```bash
composer tester                 # tester tests -s -C
composer phpstan                # phpstan analyse
```

## Tests

Nette Tester without a database: `PageContent` (safe output, headings, highlighted code) and `PageRouter` with `PageSlugs` (matching only published slugs, links, cache cleaning). The repository is an anonymous subclass of `PageRepository` on a lazy Dibi connection, as in project-auth.

## Installation Hooks

Composer install and update hooks invoke Drago project setup/cleanup commands. Inspect those scripts and their effects before invoking Composer operations that trigger them; they may modify generated project files.

## Conventions & Rules

- Preserve backward compatibility unless the task explicitly permits a breaking change. Check callers and public signatures before changing behavior.
- Follow the existing PHP style: strict types, explicit parameter and return types, tabs for indentation, and single-quoted strings where practical. Match nearby code when a convention is unclear.
- Keep package-specific configuration, resources, and migrations in their established directories. Do not edit generated or consuming-project files without confirming they are part of this repository’s intended output.
- Add or update focused tests for behavioral changes when this package has a runnable test setup. Do not invent test commands; use only the scripts and configuration present here.
- Update README examples and Composer metadata when a public API or supported requirement changes.
