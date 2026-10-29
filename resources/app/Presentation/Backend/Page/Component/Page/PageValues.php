<?php declare(strict_types=1);

namespace App\Presentation\Backend\Page\Component\Page;

use Drago\Utils\ExtraArrayHash;


class PageValues extends ExtraArrayHash
{
	public const string Title = 'title',
		Slug = 'slug',
		Description = 'description',
		Content = 'content',
		Status = 'status',
		InMenu = 'in_menu',
		MenuPosition = 'menu_position';

	public ?int $id = null;
	public string $title;
	public string $slug;
	public ?string $description = null;
	public ?string $content = null;
	public string $status;
	public bool $in_menu = false;
	public int $menu_position = 0;
}
