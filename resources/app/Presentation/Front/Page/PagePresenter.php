<?php

declare(strict_types=1);

namespace App\Presentation\Front\Page;

use App\Model\Page\PageRepository;
use App\Presentation\BasePresenter;
use Drago\Attr\AttributeDetectionException;


/** @property-read PageTemplate $template */
final class PagePresenter extends BasePresenter
{
	public function __construct(
		private readonly PageRepository $pageRepository,
	) {
		parent::__construct();
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function renderDefault(): void
	{
		$this->template->pages = $this->pageRepository
			->getPublishedPages()
			->recordAll();
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function renderDetail(string $slug): void
	{
		$page = $this->pageRepository->getPublishedBySlug($slug);
		if ($page === null) {
			$this->error('Page not found');
		}

		$this->template->page = $page;
	}
}
