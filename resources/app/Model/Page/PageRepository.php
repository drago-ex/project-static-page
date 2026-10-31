<?php declare(strict_types=1);

namespace App\Model\Page;

use Dibi\Connection;
use Dibi\Exception;
use Drago\Database\ExtraFluent;
use Drago\Database\Table;


class PageRepository
{
	/** @var Table<PageEntity> */
	public Table $table;


	public function __construct(Connection $db)
	{
		$this->table = new Table($db, PageEntity::Table, PageEntity::PrimaryKey, PageEntity::class);
	}


	/** @return ExtraFluent<PageEntity> */
	public function getPagesFluent(): ExtraFluent
	{
		return $this->table->read('*')
			->orderBy(PageEntity::PrimaryKey, 'DESC');
	}


	/**
	 * Published pages of the front menu, ordered by their position: slug => title.
	 * @return array<int|string, string> (an all-digit slug becomes an int key)
	 */
	public function getMenuPages(): array
	{
		/** @var array<int|string, string> */
		return $this->table->read('%n, %n', PageEntity::ColumnSlug, PageEntity::ColumnTitle)
			->where('%n = ?', PageEntity::ColumnStatus, PageEntity::StatusPublished)
			->where('%n = 1', PageEntity::ColumnInMenu)
			->orderBy(PageEntity::ColumnMenuPosition, 'ASC')
			->orderBy(PageEntity::ColumnTitle, 'ASC')
			->fetchPairs(PageEntity::ColumnSlug, PageEntity::ColumnTitle);
	}


	/** @return list<string> */
	public function getPublishedSlugs(): array
	{
		/** @var list<string> */
		return $this->table->read(PageEntity::ColumnSlug)
			->where('%n = ?', PageEntity::ColumnStatus, PageEntity::StatusPublished)
			->fetchPairs(null, PageEntity::ColumnSlug);
	}


	/** @throws Exception */
	public function getPublishedBySlug(string $slug): ?PageEntity
	{
		return $this->table->read('*')
			->where('%n = ?', PageEntity::ColumnSlug, $slug)
			->where('%n = ?', PageEntity::ColumnStatus, PageEntity::StatusPublished)
			->record();
	}
}
