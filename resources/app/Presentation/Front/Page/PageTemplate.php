<?php

declare(strict_types=1);

namespace App\Presentation\Front\Page;

use App\Model\Page\PageEntity;
use App\Presentation\BaseTemplate;


class PageTemplate extends BaseTemplate
{
	public PageEntity $page;

	/** HTML of the Texy content, made safe by PageContent. */
	public string $content;

	/** Printed as <meta name="description"> by the front layout. */
	public ?string $description = null;
}
