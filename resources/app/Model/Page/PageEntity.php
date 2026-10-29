<?php declare(strict_types=1);

namespace App\Model\Page;

use Drago\Database\Entity;


class PageEntity extends Entity
{
	public const string Table = 'static_page',
		PrimaryKey = 'id',
		ColumnSlug = 'slug',
		ColumnTitle = 'title',
		ColumnDescription = 'description',
		ColumnContent = 'content',
		ColumnStatus = 'status',
		ColumnInMenu = 'in_menu',
		ColumnMenuPosition = 'menu_position',
		ColumnCreatedAt = 'created_at',
		ColumnUpdatedAt = 'updated_at',
		StatusDraft = 'draft',
		StatusPublished = 'published';

	public int $id;
	public string $slug;
	public string $title;

	/** For search engines (meta description). */
	public ?string $description = null;
	public ?string $content = null;
	public string $status;

	/** A published page with in_menu is in the front menu, ordered by menu_position. */
	public int $in_menu;
	public int $menu_position;
	public \DateTimeInterface $created_at;
	public \DateTimeInterface $updated_at;
}
