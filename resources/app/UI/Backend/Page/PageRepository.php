<?php

declare(strict_types = 1);

namespace App\UI\Backend\Page;

use Dibi\Connection;
use Drago\Attr\Table;
use Drago\Database\Database;


#[Table(PageEntity::Table, PageEntity::PrimaryKey, class: PageEntity::class)]
class PageRepository
{
	/** @phpstan-use Database<PageEntity> */
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}
}
