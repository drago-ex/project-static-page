<?php

declare(strict_types=1);

namespace App\UI\Backend\Page;

use Drago\Application\UI\Factory;
use Nette\Application\UI\Form;


readonly class PageFactory
{
	public function __construct(
		private Factory $factory
	) {
	}


	public function create(): Form
	{
		$form = $this->factory->create();
		$form->addText('title', 'Title')
			->setRequired('Title is required.');

		$form->addText('slug', 'Slug')
			->setRequired('Slug is required.')
			->setOption('description', 'Example: about-us');

		$form->addTextArea('content', 'Content')
			->setHtmlAttribute('rows', 20);

		$form->addSelect('status', 'Status', [
			'draft' => 'Draft',
			'published' => 'Published',
		])
			->setDefaultValue('draft');

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = $this->success(...);
		return $form;
	}


	private function success(Form $form): void
	{

	}
}
