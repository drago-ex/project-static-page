<?php

declare(strict_types=1);

namespace App\Model\Page;

use Dibi\Connection;
use Dibi\Row;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Database\Database;
use Drago\Database\ExtraFluent;


#[Table(PageEntity::Table, PageEntity::PrimaryKey, entity: PageEntity::class)]
class PageRepository
{
	/** @phpstan-use Database<PageEntity> */
	use Database;

	public function __construct(
		protected readonly Connection $connection,
	) {
	}


	/**
	 * @return ExtraFluent<PageEntity>
	 * @throws AttributeDetectionException
	 */
	public function getPagesFluent(): ExtraFluent
	{
		return $this->read('*')
			->orderBy(PageEntity::PrimaryKey, 'DESC');
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function getPublishedBySlug(string $slug): array|Row
	{
		return $this->read('*')
			->where('%n = ?', PageEntity::ColumnSlug, $slug)
			->where('%n = ?', PageEntity::ColumnStatus, PageEntity::StatusPublished)
			->fetch();
	}
}
