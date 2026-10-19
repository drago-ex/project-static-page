<?php

declare(strict_types=1);

namespace App\Model\Page;

use Drago\Attr\AttributeDetectionException;
use Nette\Caching\Cache;
use Nette\Caching\Storage;


/**
 * Slugs of the published pages, cached for the router, which asks on every request.
 * The administration cleans the cache when a page is saved or deleted.
 */
class PageSlugs
{
	private Cache $cache;


	public function __construct(
		private readonly PageRepository $pageRepository,
		Storage $storage,
	) {
		$this->cache = new Cache($storage, self::class);
	}


	/** @throws AttributeDetectionException */
	public function isPublished(string $slug): bool
	{
		/** @var array<string, true> $slugs */
		$slugs = $this->cache->load('published', fn(): array => array_fill_keys($this->pageRepository->getPublishedSlugs(), true));
		return isset($slugs[$slug]);
	}


	public function clean(): void
	{
		$this->cache->remove('published');
	}
}
