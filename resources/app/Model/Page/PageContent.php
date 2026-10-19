<?php

declare(strict_types=1);

namespace App\Model\Page;

use Drago\Texy\CodeBlockHandler;
use Texy\Configurator;
use Texy\Texy;


/**
 * Converts the Texy markup of a page to HTML. Raw HTML in the markup is limited to safe inline tags and URLs to
 * safe schemes, so the output can be printed without escaping even if a page is not written by a trusted person.
 * Images and links stay allowed, as a page of the site needs them. Code blocks (/--code php) are highlighted
 * by the handler of drago-ex/texy-toolbar.
 */
class PageContent
{
	public function __construct(
		private readonly CodeBlockHandler $codeBlockHandler,
	) {
	}


	public function toHtml(?string $text): string
	{
		if ($text === null || trim($text) === '') {
			return '';
		}

		return $this->createTexy()->process($text);
	}


	private function createTexy(): Texy
	{
		$texy = new Texy;
		$texy->allowedTags = Configurator::$safeTags;
		$texy->allowedClasses = Texy::NONE;
		$texy->allowedStyles = Texy::NONE;
		$texy->urlSchemeFilters[Texy::FILTER_ANCHOR] = '#https?:|mailto:#A';
		$texy->urlSchemeFilters[Texy::FILTER_IMAGE] = '#https?:#A';
		$texy->addHandler('block', $this->codeBlockHandler);

		// The page title is the <h1>, so headings of the content start at <h2>.
		$texy->headingModule->top = 2;
		return $texy;
	}
}
