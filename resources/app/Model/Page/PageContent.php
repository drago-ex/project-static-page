<?php

declare(strict_types=1);

namespace App\Model\Page;

use Drago\Highlighter\Highlighter;
use Texy\Configurator;
use Texy\HandlerInvocation;
use Texy\Helpers;
use Texy\HtmlElement;
use Texy\Modifier;
use Texy\Texy;


/**
 * Converts the Texy markup of a page to HTML. Raw HTML in the markup is limited to safe inline tags and URLs to
 * safe schemes, so the output can be printed without escaping even if a page is not written by a trusted person.
 * Images and links stay allowed, as a page of the site needs them. Code blocks (/--code php) are highlighted
 * by drago-ex/highlighter.
 */
class PageContent
{
	public function __construct(
		private readonly Highlighter $highlighter = new Highlighter,
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
		$texy->addHandler('block', $this->highlightCode(...));

		// The page title is the <h1>, so headings of the content start at <h2>.
		$texy->headingModule->top = 2;
		return $texy;
	}


	/** The highlighter escapes the code itself, other blocks and unknown languages are left to Texy. */
	private function highlightCode(
		HandlerInvocation $invocation,
		string $blocktype,
		string $content,
		?string $lang,
		Modifier $modifier,
	): HtmlElement|string|null
	{
		if ($blocktype !== 'block/code' || $lang === null || !$this->highlighter->isSupported($lang)) {
			return $invocation->proceed();
		}

		$code = Helpers::outdent($content);
		if ($code === '') {
			return "\n";
		}

		$texy = $invocation->getTexy();
		$pre = new HtmlElement('pre');
		$modifier->decorate($texy, $pre);
		$pre->attrs['class'] = 'bg-body-tertiary p-3 rounded';
		$pre->create('code', $texy->protect($this->highlighter->highlight($code, $lang), $texy::CONTENT_BLOCK));
		return $pre;
	}
}
