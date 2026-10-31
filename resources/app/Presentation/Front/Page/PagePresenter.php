<?php declare(strict_types=1);

namespace App\Presentation\Front\Page;

use App\Model\Page\PageRepository;
use App\Presentation\BasePresenter;
use Dibi\Exception;
use Drago\Texy\TexyFormatter;


/**
 * A published page by its slug, at the top-level address given by PageRouter.
 * @property-read PageTemplate $template
 */
final class PagePresenter extends BasePresenter
{
	public function __construct(
		private readonly PageRepository $pageRepository,
		private readonly TexyFormatter $texyFormatter,
	) {
		parent::__construct();
	}


	/** @throws Exception */
	public function renderDefault(string $slug): void
	{
		$page = $this->pageRepository->getPublishedBySlug($slug);
		if ($page === null) {
			$this->error('Page not found');
		}

		$this->template->page = $page;
		$this->template->content = $this->texyFormatter->toHtml($page->content);
		$this->template->description = $page->description;
	}
}
