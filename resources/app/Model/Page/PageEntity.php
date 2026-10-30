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

	public const int ColumnTitleSize = 255,
		ColumnDescriptionSize = 255;

	public int $id;
	public string $slug;
	public string $title;

	/** For search engines (meta description). */
	public ?string $description = null;
	public ?string $content = null;
	public string $status;

	/** A published page with $inMenu is in the front menu, ordered by $menuPosition. */
	public int $inMenu;
	public int $menuPosition;
	public \DateTimeInterface $createdAt;
	public \DateTimeInterface $updatedAt;
}
