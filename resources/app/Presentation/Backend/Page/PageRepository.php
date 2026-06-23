<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Page;

use Dibi\Connection;
use Drago\Attr\Table;
use Drago\Database\Database;


#[Table(PageEntity::Table, PageEntity::PrimaryKey, entity: PageEntity::class)]
class PageRepository
{
	/** @phpstan-use Database<PageEntity> */
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}
}
