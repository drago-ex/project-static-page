<?php declare(strict_types=1);

/**
 * Test: App\Presentation\Front\Page\PageRouter and App\Model\Page\PageSlugs
 */

use App\Model\Page\PageRepository;
use App\Model\Page\PageSlugs;
use App\Presentation\Front\Page\PageRouter;
use Dibi\Connection;
use Nette\Caching\Storages\MemoryStorage;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';


/** Repository with the given published slugs, which counts how often the database would be asked. */
function createRepository(array $slugs): PageRepository
{
	return new class (new Connection(['driver' => 'mysqli', 'lazy' => true]), $slugs) extends PageRepository {
		public int $queries = 0;


		public function __construct(
			Connection $connection,
			/** @var list<string> */
			public array $slugs,
		) {
			parent::__construct($connection);
		}


		public function getPublishedSlugs(): array
		{
			$this->queries++;
			return $this->slugs;
		}
	};
}


/** @return array<string, mixed>|null */
function matchPage(PageSlugs $slugs, string $path): ?array
{
	$request = new Request(new UrlScript('https://example.com' . $path, '/'));
	return PageRouter::create($slugs)->match($request);
}


test('A published slug at the top level leads to the page', function () {
	$params = matchPage(new PageSlugs(createRepository(['o-nas']), new MemoryStorage), '/o-nas');
	Assert::same('Front:Page', $params['presenter'] ?? null);
	Assert::same('o-nas', $params['slug'] ?? null);
	Assert::same('cs', $params['lang'] ?? null);
});


test('The language prefix is kept', function () {
	$params = matchPage(new PageSlugs(createRepository(['o-nas']), new MemoryStorage), '/en/o-nas');
	Assert::same('en', $params['lang'] ?? null);
});


test('Other addresses go on to the next routers', function () {
	$slugs = new PageSlugs(createRepository(['o-nas']), new MemoryStorage);
	Assert::null(matchPage($slugs, '/koncept'));
	Assert::null(matchPage($slugs, '/O-nas'));
	Assert::null(matchPage($slugs, '/o-nas/detail'));
});


test('The router links to a published page', function () {
	$request = new Request(new UrlScript('https://example.com/', '/'));
	$router = PageRouter::create(new PageSlugs(createRepository(['o-nas']), new MemoryStorage));
	$params = ['presenter' => 'Front:Page', 'action' => 'default', 'slug' => 'o-nas', 'lang' => 'cs'];
	Assert::same('https://example.com/o-nas', $router->constructUrl($params, $request->getUrl()));
});


test('Slugs are loaded once and again after clean()', function () {
	$repository = createRepository(['o-nas']);
	$slugs = new PageSlugs($repository, new MemoryStorage);

	Assert::true($slugs->isPublished('o-nas'));
	Assert::false($slugs->isPublished('sluzby'));
	Assert::same(1, $repository->queries);

	$repository->slugs = ['o-nas', 'sluzby'];
	$slugs->clean();
	Assert::true($slugs->isPublished('sluzby'));
	Assert::same(2, $repository->queries);
});
