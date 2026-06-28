<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Page\Component\Page;

use Drago\Utils\ExtraArrayHash;


class PageValues extends ExtraArrayHash
{
	public const string
		Title = 'title',
		Slug = 'slug',
		Content = 'content',
		Status = 'status';

	public ?int $id = null;
	public string $title;
	public string $slug;
	public ?string $content = null;
	public string $status;
}
