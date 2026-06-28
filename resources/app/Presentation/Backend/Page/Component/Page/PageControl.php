<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Page\Component\Page;

use App\Model\Page\PageEntity;
use App\Model\Page\PageRepository;
use App\Presentation\Backend\Page\Component\BaseControl;
use App\Presentation\Backend\Page\Component\Factory;
use Dibi\Exception;
use Dibi\Result;
use Drago\Application\UI\Alert;
use Drago\Attr\AttributeDetectionException;
use Drago\Datagrid\DataGrid;
use Drago\Datagrid\Exception\InvalidColumnException;
use Drago\Form\Autocomplete;
use Nette\Application\Attributes\Requires;
use Nette\Application\UI\Form;


class PageControl extends BaseControl
{
	public function __construct(
		public Factory $factory,
		private readonly PageRepository $pageRepository,
	) {
		parent::__construct($this->factory);
	}


	/** @throws AttributeDetectionException|InvalidColumnException */
	protected function createComponentDataGrid(): DataGrid
	{
		$grid = new DataGrid;
		$grid->setTranslator($this->translator);
		$grid->setDataSource($this->pageRepository->getPagesFluent())
			->setPrimaryKey(PageEntity::PrimaryKey);

		$grid->addColumnText(PageEntity::ColumnTitle, 'Title')
			->setFilterText();

		$grid->addColumnText(PageEntity::ColumnSlug, 'Slug')
			->setFilterText();

		$grid->addColumnText(PageEntity::ColumnStatus, 'Status')
			->setFilterSelect([
				PageEntity::StatusDraft => 'Draft',
				PageEntity::StatusPublished => 'Published',
			]);

		$grid->addColumnDate(PageEntity::ColumnUpdatedAt, 'Updated at');

		$user = $this->getPresenter()->getUser();

		if ($user->isAllowed('Backend:Page', 'pages-write')) {
			$grid->addAction(
				label: 'Edit',
				signal: 'edit!',
				class: 'ajax btn btn-xs btn-primary',
				callback: fn(int $id) => $this->handleEdit($id),
			);

			$grid->addAction(
				label: 'Delete',
				signal: 'delete!',
				class: 'ajax btn btn-xs btn-danger',
				callback: fn(int $id) => $this->handleDelete($id),
			);
		}

		return $grid;
	}


	public function render(): void
	{
		$template = $this->createRender();
		$template->setFile(__DIR__ . '/Page.latte');
		$template->render();
	}


	protected function createComponentPage(): Form
	{
		$form = $this->factory->create();
		$form->addTextInput(PageValues::Title, 'Title')
			->setRequired('Please enter title.')
			->setAutocomplete(Autocomplete::Off);

		$form->addTextInput(PageValues::Slug, 'Slug')
			->setRequired('Please enter slug.')
			->addRule($form::Pattern, 'Use lowercase letters, numbers and hyphens.', '[a-z0-9]+(?:-[a-z0-9]+)*')
			->setAutocomplete(Autocomplete::Off);

		$form->addTextAreaForm(PageValues::Content, 'Content')
			->setHtmlAttribute('rows', 14);

		$form->addSelect(PageValues::Status, 'Status', [
			PageEntity::StatusDraft => 'Draft',
			PageEntity::StatusPublished => 'Published',
		])
			->setRequired('Please select status.')
			->setDefaultValue(PageEntity::StatusDraft);

		$form->addHidden('id', $this->id)
			->addRule($form::Integer)
			->setNullable();

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = $this->success(...);
		return $form;
	}


	private function success(Form $form, PageValues $values): void
	{
		try {
			$message = (int) $values->id > 0 ? 'Update successful.' : 'Insert successful.';

			$this->pageRepository->save($values);
			$this->redrawFlashMessage($message, Alert::Success);

			$form->reset();
			$this->closeComponent();
			$this->redrawControl();
			$this['dataGrid']->redrawDataGrid();

		} catch (\Throwable $e) {
			$message = match ($e->getCode()) {
				1062 => 'This page already exists.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
			$this->redrawOffCanvas();
		}
	}


	/**
	 * Handles page edit.
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	#[Requires(ajax: true)]
	public function handleEdit(int $id): void
	{
		$item = $this->pageRepository->get($id)->record();
		if ($item === null) {
			$this->error();
		}

		$form = $this->getComponent('page');
		$form->setDefaults((array) $item);

		$sendControl = $this->getFormComponent($form, 'send');
		$sendControl?->setCaption('Edit page');

		$this->redrawOffCanvas();
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	protected function getResultRepository(int $id): Result|int|null
	{
		return $this->pageRepository
			->delete(PageEntity::PrimaryKey, $id)
			->execute();
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	protected function getItemRepository(int $id): string|null
	{
		return $this->pageRepository
			->get($id)
			->record()
			?->title;
	}
}
