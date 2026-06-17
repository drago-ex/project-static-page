<?php

declare(strict_types = 1);

namespace App\UI\Backend\Page;


class PageEntity
{
	public const string
		Table = 'static_page',
		PrimaryKey = 'id',
		ColumnSlug = 'slug',
		ColumnTitle = 'title',
		ColumnContent = 'content',
		ColumnStatus = 'status',
		ColumnCreateAt = 'created_at',
		ColumnUpdateAt = 'updated_at';

	public int $id;
	public string $slug;
	public string $title;
	public string $content;
	public string $status;
	public string $created_at;
	public string $updated_at;
}
