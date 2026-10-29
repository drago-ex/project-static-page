<?php declare(strict_types=1);

namespace App\Presentation\Front\Page;

use App\Model\Page\PageSlugs;
use Drago\Form\Forms;
use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


/**
 * Pages at the top level, e.g. /about-us. Only a published slug matches, so other addresses go on to the
 * routers added after this one (8.conf.neon is loaded before the front router in 9.conf.neon).
 */
final class PageRouter
{
	use Nette\StaticClass;

	public static function create(PageSlugs $slugs): RouteList
	{
		$router = new RouteList;
		$router->withModule('Front')
			->addRoute('[<lang=cs cs|en>/]<slug ' . Forms::SlugPattern . '>', [
				'presenter' => 'Page',
				'action' => 'default',
				'slug' => [
					Route::FilterIn => fn(string $slug): ?string => $slugs->isPublished($slug) ? $slug : null,
				],
			]);

		return $router;
	}
}
