<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Page;

use App\Presentation\Backend\BackendPresenter;
use App\Presentation\Backend\Page\Component\Page\PageControl;
use Exception;
use Throwable;


class PagePresenter extends BackendPresenter
{
	public function __construct(
		private readonly PageControl $pageControl,
	) {
		parent::__construct();
	}


	/** @throws Throwable|Exception */
	protected function createComponentPages(): PageControl
	{
		$control = $this->pageControl;
		$control->translator = $this->getTranslator();
		return $control;
	}
}
