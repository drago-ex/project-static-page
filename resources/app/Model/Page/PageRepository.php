<?php

declare(strict_types=1);

namespace App\Model\Page;

use Dibi\Connection;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Database\Database;
use Drago\Database\ExtraFluent;


#[Table(PageEntity::Table, PageEntity::PrimaryKey, entity: PageEntity::class)]
class PageRepository
{
	/** @use Database<PageEntity> */
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
	 * Published pages of the front menu, ordered by their position: slug => title.
	 * @return array<string, string>
	 * @throws AttributeDetectionException
	 */
	public function getMenuPages(): array
	{
		/** @var array<string, string> */
		return $this->read('%n, %n', PageEntity::ColumnSlug, PageEntity::ColumnTitle)
			->where('%n = ?', PageEntity::ColumnStatus, PageEntity::StatusPublished)
			->where('%n = 1', PageEntity::ColumnInMenu)
			->orderBy(PageEntity::ColumnMenuPosition, 'ASC')
			->orderBy(PageEntity::ColumnTitle, 'ASC')
			->fetchPairs(PageEntity::ColumnSlug, PageEntity::ColumnTitle);
	}


	/**
	 * @return list<string>
	 * @throws AttributeDetectionException
	 */
	public function getPublishedSlugs(): array
	{
		/** @var list<string> */
		return $this->read(PageEntity::ColumnSlug)
			->where('%n = ?', PageEntity::ColumnStatus, PageEntity::StatusPublished)
			->fetchPairs(null, PageEntity::ColumnSlug);
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function getPublishedBySlug(string $slug): ?PageEntity
	{
		return $this->read('*')
			->where('%n = ?', PageEntity::ColumnSlug, $slug)
			->where('%n = ?', PageEntity::ColumnStatus, PageEntity::StatusPublished)
			->record();
	}
}
