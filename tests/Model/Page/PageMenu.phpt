<?php declare(strict_types=1);

/**
 * Test: App\Model\Page\PageMenu
 */

use App\Model\Page\PageMenu;
use App\Model\Page\PageRepository;
use Dibi\Connection;
use Nette\Caching\Storages\MemoryStorage;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


test('Menu pages are loaded once and again after clean()', function () {
	$repository = new class (new Connection(['driver' => 'mysqli', 'lazy' => true])) extends PageRepository {
		public int $queries = 0;

		/** @var array<string, string> */
		public array $pages = ['o-nas' => 'O nás', 'kontakt' => 'Kontakt'];


		public function getMenuPages(): array
		{
			$this->queries++;
			return $this->pages;
		}
	};

	$menu = new PageMenu($repository, new MemoryStorage);
	Assert::same(['o-nas' => 'O nás', 'kontakt' => 'Kontakt'], $menu->getItems());
	$menu->getItems();
	Assert::same(1, $repository->queries);

	$repository->pages = ['kontakt' => 'Kontakt'];
	$menu->clean();
	Assert::same(['kontakt' => 'Kontakt'], $menu->getItems());
	Assert::same(2, $repository->queries);
});
