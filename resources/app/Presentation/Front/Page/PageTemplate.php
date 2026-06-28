<?php

declare(strict_types=1);

namespace App\Presentation\Front\Page;

use App\Model\Page\PageEntity;
use App\Presentation\BaseTemplate;


class PageTemplate extends BaseTemplate
{
	/** @var PageEntity[] */
	public array $pages = [];
	public PageEntity $page;
}
