<?

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Oe\Telegrambot\Entity\PostingTable;
use Oe\Telegrambot\Entity\PostingState;

use Bitrix\Sender\Internals\PrettyDate;
use Bitrix\Sender\UI\PageNavigation;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Bitrix\Main\Loader::includeModule('oe.telegrambot'))
{
	ShowError('Module `oe.telegrambot` not installed');
	die();
}

Loc::loadMessages(__FILE__);

class PostingLetterListComponent extends \CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function preparePost()
	{
		$ids = $this->request->get('ID');
		$action = $this->request->get('action_button_' . $this->arParams['GRID_ID']);
		switch ($action)
		{
			case 'delete':
				if (!is_array($ids))
				{
					$ids = array($ids);
				}

				foreach ($ids as $id)
				{
					PostingTable::removeById($id);
				}
				break;
		}
	}

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CAllMain*/
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_LETTER_LIST_COMP_TITLE'));
		}

		$this->arResult['ERRORS'] = array();
		$this->arResult['ROWS'] = array();
		$this->arResult['STATE_LIST'] = PostingState::getList();
		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';

		if ($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->preparePost();
		}


		// set ui filter
		$this->setUiFilter();
		$this->setUiFilterPresets();

		// set ui grid columns
		$this->setUiGridColumns();

		// export
		$isExportMode = false;

		// create nav
		$pageSizes = [];
		foreach ([5, 10, 20, 30, 50, 100] as $index)
		{
			$pageSizes[] = ['NAME' => $index, 'VALUE' => $index];
		}

		$gridOptions = new GridOptions($this->arParams['GRID_ID']);
		$navData = $gridOptions->getNavParams(['nPageSize' => 10]);
		$nav = new PageNavigation("page-sender-letters");
		$nav->allowAllRecords(true)
			->setPageSize($navData['nPageSize'])
			->setPageSizes($pageSizes)
			->initFromUri();

		// get rows
		$selectParameters = array(
			'filter' => $this->getDataFilter(),
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			'count_total' => true,
			'order' => $this->getGridOrder(),
			'select' => ['*']
		);

		$list = PostingTable::getList($selectParameters);
		foreach ($list as $item)
		{
			// format user name
			$this->setRowColumnUser($item);
			$item['STATUS_ID'] = $item['STATUS'];
			$item['STATE_NAME'] = $this->arResult['STATE_LIST'][$item['STATUS']];

			$item['URLS'] = array(
				'EDIT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_EDIT']),
			);
			$this->arResult['ROWS'][] = $item;
		}

		$this->arResult['TOTAL_ROWS_COUNT'] = $list->getCount();

		// set rec count to nav
		$nav->setRecordCount($list->getCount());
		$this->arResult['NAV_OBJECT'] = $nav;

		return true;
	}

	protected function formatDate(\Bitrix\Main\Type\DateTime $dateTime = null)
	{
		if (!$dateTime)
		{
			return '';
		}
		$dateTime = clone $dateTime;
		return PrettyDate::formatDateTime($dateTime);
	}

	protected function getDataFilter()
	{
		$filterOptions = new FilterOptions($this->arParams['FILTER_ID']);
		$requestFilter = $filterOptions->getFilter($this->arResult['FILTERS']);
		$searchString = $filterOptions->getSearchString();

		$filter = array('!ID' => NULL);
		if (isset($requestFilter['TITLE']) && $requestFilter['TITLE'])
		{
			$filter['TITLE'] = '%' . $requestFilter['TITLE'] . '%';
		}
		if (isset($requestFilter['USER_ID']) && $requestFilter['USER_ID'])
		{
			$filter['=USER_ID'] = $requestFilter['USER_ID'];
		}
		if (isset($requestFilter['STATE']) && $requestFilter['STATE'])
		{
			$filter['=STATUS'] = $requestFilter['STATE'];
		}
		if (isset($requestFilter['DATE_INSERT_from']) && $requestFilter['DATE_INSERT_from'])
		{
			$filter['>=DATE_INSERT'] = $requestFilter['DATE_INSERT_from'];
		}
		if (isset($requestFilter['DATE_INSERT_to']) && $requestFilter['DATE_INSERT_to'])
		{
			$filter['<=DATE_INSERT'] = $requestFilter['DATE_INSERT_to'];
		}
		if (isset($requestFilter['DATE_SENT_from']) && $requestFilter['DATE_SENT_from'])
		{
			$filter['>=DATE_SENT'] = $requestFilter['DATE_SENT_from'];
		}
		if (isset($requestFilter['DATE_SENT_to']) && $requestFilter['DATE_SENT_to'])
		{
			$filter['<=DATE_SENT'] = $requestFilter['DATE_SENT_to'];
		}

		return $filter;
	}

	protected function getGridOrder()
	{
		$defaultSort = array('ID' => 'DESC');

		$gridOptions = new GridOptions($this->arParams['GRID_ID']);
		$sorting = $gridOptions->getSorting(array('sort' => $defaultSort));

		$by = key($sorting['sort']);
		$order = mb_strtoupper(current($sorting['sort'])) === 'ASC' ? 'ASC' : 'DESC';

		$list = array();
		foreach ($this->getUiGridColumns() as $column)
		{
			if (!isset($column['sort']) || !$column['sort'])
			{
				continue;
			}

			$list[] = $column['sort'];
		}

		if (!in_array($by, $list))
		{
			return $defaultSort;
		}

		return array($by => $order);
	}

	protected function setUiGridColumns()
	{
		$this->arResult['COLUMNS'] = $this->getUiGridColumns();
	}

	protected function getUiGridColumns()
	{
		$list = array(
			array(
				"id" => "ID",
				"name" => "ID",
				"sort" => "ID",
				"default" => false
			),
			array(
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_DATE_INSERT2'),
				"sort" => "DATE_INSERT",
				"default" => false
			),
			array(
				"id" => "TITLE",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_TITLE'),
				"sort" => "TITLE",
				"default" => true
			),
			array(
				"id" => "USER",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_USER'),
				"sort" => "USER_ID",
				"default" => false,
			),
			array(
				"id" => "ACTIONS",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_ACTIONS'),
				"sort" => "ID",
				"default" => true
			),
			array(
				"id" => "STATUS",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_STATUS'),
				"sort" => "STATUS",
				"default" => true
			)
		);

		return $list;
	}

	protected function setUiFilter()
	{

		$this->arResult['FILTERS'] = [
			array(
				"id" => "TITLE",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_TITLE'),
				"default" => true
			),
			array(
				"id" => "STATE",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_STATE'),
				"type" => "list",
				"default" => true,
				'params' => array('multiple' => 'Y'),
				"items" => $this->arResult['STATE_LIST']
			),
			array(
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_DATE_INSERT2'),
				"type" => "date",
				"default" => true,
			),
		];
	}

	protected function getUiFilterPresets()
	{
		return array(
			'filter_letters_my' => array(
				'name' => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_PRESET_MY'),
				'fields' => array(
					'CREATED_BY' => $GLOBALS['USER']->GetID(),
				)
			),
			'filter_letters_all' => array(
				'name' => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_PRESET_ALL'),
				'fields' => array()
			),
		);
	}

	protected function setUiFilterPresets()
	{
		$this->arResult['FILTER_PRESETS'] = $this->getUiFilterPresets();
	}

	protected function setRowColumnUser(array &$data)
	{
		$data['USER'] = '';
		$data['USER_PATH'] = '';
		if (!$data['USER_ID'])
		{
			return;
		}

		$data['USER_PATH'] = str_replace('#id#', $data['USER_ID'], $this->arParams['PATH_TO_USER_PROFILE']);
		$userData = Bitrix\Main\UserTable::getRow([
				'select' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'TITLE'],
				'filter' => ['=ID'=> $data['USER_ID']]
		]);
		if(is_array($userData))
		{
			$data['USER'] = \CUser::FormatName(
				$this->arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => $userData['LOGIN'],
					'NAME' => $userData['NAME'],
					'LAST_NAME' => $userData['LAST_NAME'],
					'SECOND_NAME' => $userData['SECOND_NAME']
				),
				true, false
			);
		}
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		$this->prepareResult();
		$this->includeComponentTemplate();
	}
}