<?php declare(strict_types=1);

namespace App\Model\Page;

use Drago\Attr\AttributeDetectionException;
use Nette\Caching\Cache;
use Nette\Caching\Storage;


/**
 * Pages of the front menu (published, with in_menu), cached because the layout shows them on every page.
 * The administration cleans the cache when a page is saved or deleted.
 */
class PageMenu
{
	private Cache $cache;


	public function __construct(
		private readonly PageRepository $pageRepository,
		Storage $storage,
	) {
		$this->cache = new Cache($storage, self::class);
	}


	/**
	 * @return array<int|string, string> slug => title (PHP makes an all-digit slug an int key)
	 * @throws AttributeDetectionException
	 */
	public function getItems(): array
	{
		/** @var array<int|string, string> */
		return $this->cache->load('items', fn(): array => $this->pageRepository->getMenuPages());
	}


	public function clean(): void
	{
		$this->cache->remove('items');
	}
}
