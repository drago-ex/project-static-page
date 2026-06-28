<?php

declare(strict_types=1);

namespace App\Model\Page;

use Drago\Database\Entity;


class PageEntity extends Entity
{
	public const string
		Table = 'static_page',
		PrimaryKey = 'id',
		ColumnSlug = 'slug',
		ColumnTitle = 'title',
		ColumnContent = 'content',
		ColumnStatus = 'status',
		ColumnCreatedAt = 'created_at',
		ColumnUpdatedAt = 'updated_at',
		StatusDraft = 'draft',
		StatusPublished = 'published';

	public int $id;
	public string $slug;
	public string $title;
	public ?string $content = null;
	public string $status;
	public string $created_at;
	public string $updated_at;
}
