<?php

declare(strict_types=1);

namespace App\UI\Backend\Page;

use Nette\Application\UI\Presenter;


class PagePresenter extends Presenter
{
	public function __construct(
		protected PageFactory $pageFactory,
	) {
		parent::__construct();
	}
}
