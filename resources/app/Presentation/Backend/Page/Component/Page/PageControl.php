<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Page\Component\Page;

use App\Model\Page\PageEntity;
use App\Model\Page\PageMenu;
use App\Model\Page\PageRepository;
use App\Model\Page\PageSlugs;
use App\Presentation\Backend\Page\Component\BaseControl;
use App\Presentation\Backend\Page\Component\Factory;
use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Attr\AttributeDetectionException;
use Drago\Datagrid\DataGrid;
use Drago\Datagrid\Exception\InvalidColumnException;
use Drago\Form\Autocomplete;
use Drago\Texy\TexyToolbar;
use Nette\Application\Attributes\Requires;
use Nette\Application\InvalidPresenterException;
use Nette\Application\IPresenterFactory;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl as BaseInput;
use Nette\Http\IRequest;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Nette\Routing\Router;
use Tracy\Debugger;
use Tracy\ILogger;


class PageControl extends BaseControl
{
	public function __construct(
		public Factory $factory,
		private readonly PageRepository $pageRepository,
		private readonly PageSlugs $pageSlugs,
		private readonly PageMenu $pageMenu,
		private readonly Router $router,
		private readonly IPresenterFactory $presenterFactory,
		private readonly IRequest $httpRequest,
		private readonly TexyToolbar $texyToolbar,
	) {
		parent::__construct($this->factory);
	}


	public function render(): void
	{
		$template = $this->createRender();
		$template->setFile(__DIR__ . '/Page.latte');
		$template->render();
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


	/** Returns the HTML of the content for the preview of the Texy toolbar. */
	#[Requires(methods: 'POST', ajax: true)]
	public function handlePreview(): void
	{
		$this->getPresenter()->sendJson($this->texyToolbar->getPreview());
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

		// A click on the row opens the form, the Edit button is then hidden by the grid.
		$grid->setRowClickAction('edit');
		$grid->addActionEdit(fn(int $id) => $this->handleEdit($id));
		$grid->addActionDelete(fn(int $id) => $this->handleDelete($id));

		return $grid;
	}


	protected function createComponentPage(): Form
	{
		$form = $this->factory->create();
		$form->addTextInput(PageValues::Title, 'Title')
			->setAutocomplete(Autocomplete::Off)
			->setRequired('Please enter title.');

		$form->addSlugInput(PageValues::Slug, 'Slug')
			->setRequired('Please enter slug.');

		$form->addTextInput(PageValues::Description, 'Description for search engines')
			->setAutocomplete(Autocomplete::Off)
			->setNullable()
			->addRule($form::MaxLength, 'The description can have at most %d characters.', 255);

		$form->addTextAreaInput(PageValues::Content, 'Content (Texy)')
			->setHtmlAttribute('data-texy-toolbar', $this->texyToolbar->getOptions($this->link('preview!')));

		$form->addSelect(PageValues::Status, 'Status', [
			PageEntity::StatusDraft => 'Draft',
			PageEntity::StatusPublished => 'Published',
		])
			->setDefaultValue(PageEntity::StatusDraft)
			->setRequired('Please select status.');

		$form->addCheckbox(PageValues::InMenu, 'Show in the menu');

		$form->addIntegerInput(PageValues::MenuPosition, 'Position in the menu')
			->setDefaultValue(0)
			->setRequired('Please enter the position in the menu.');

		$form->addHidden('id')
			->setNullable()
			->addRule($form::Integer);

		$form->addSubmit('send', 'Send');
		$form->onValidate[] = $this->validatePageForm(...);
		$form->onSuccess[] = $this->processPageForm(...);
		// errors found on the server (e.g. a reserved slug) are shown in the open offcanvas
		$form->onError[] = fn() => $this->redrawOffCanvas();
		return $form;
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	protected function deleteItem(int $id): void
	{
		$this->pageRepository
			->delete(PageEntity::PrimaryKey, $id)
			->execute();

		$this->pageSlugs->clean();
		$this->pageMenu->clean();
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	protected function getItemTitle(int $id): ?string
	{
		return $this->pageRepository
			->get($id)
			->record()
			?->title;
	}


	/** Pages live at the top level (/slug), so a slug must not take the address of another presenter. */
	private function validatePageForm(Form $form, PageValues $values): void
	{
		$input = $form->getComponent(PageValues::Slug);
		if ($input instanceof BaseInput && $this->isReservedSlug($values->slug)) {
			$input->addError('This address is already used by another part of the site.');
		}
	}


	/** True when /slug already leads to an existing presenter, e.g. /admin, /sign or /en. */
	private function isReservedSlug(string $slug): bool
	{
		$url = $this->httpRequest->getUrl();
		$params = $this->router->match(new Request(new UrlScript($url->getBaseUrl() . $slug, $url->getBasePath())));
		$presenter = $params['presenter'] ?? null;
		if (!is_string($presenter) || $presenter === 'Front:Page') {
			return false;
		}

		try {
			$this->presenterFactory->getPresenterClass($presenter);
			return true;
		} catch (InvalidPresenterException) {
			return false;
		}
	}


	private function processPageForm(Form $form, PageValues $values): void
	{
		try {
			$message = (int) $values->id > 0 ? 'Update successful.' : 'Insert successful.';

			$this->pageRepository->save($values);
			$this->pageSlugs->clean();
			$this->pageMenu->clean();
			$this->addFlashMessage($message, Alert::Success);
			$this->addRedraw($this->snippetMessage);

			$form->reset();
			$this->closeComponent();
			$this['dataGrid']->redrawDataGrid();

		} catch (\Throwable $e) {
			if ($e->getCode() !== 1062) {
				// unexpected: the user sees a general message, the log the cause
				Debugger::log($e, ILogger::EXCEPTION);
			}

			$message = match ($e->getCode()) {
				1062 => 'This page already exists.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
			$this->redrawOffCanvas();
		}
	}
}
