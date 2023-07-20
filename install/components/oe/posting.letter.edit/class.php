<?

use Bitrix\Fileman;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Address;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use Oe\Telegrambot\Entity\PostingTable;
use Oe\Telegrambot\Entity\PostingState;
use Bitrix\Sender\Internals\PostFiles;

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

class PostingLetterEditComponent extends \CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	/** @var PostingTable $letter Letter. */
	protected $letter;

	protected function checkRequiredParams()
	{
		return $this->errors->count() == 0;
	}

	protected function initParams()
	{
		$this->arParams['ID'] = isset($this->arParams['ID']) ? (int) $this->arParams['ID'] : 0;
		$this->arParams['ID'] = $this->arParams['ID'] ? $this->arParams['ID'] : (int) $this->request->get('ID');
		$this->arParams['IS_OUTSIDE'] = isset($this->arParams['IS_OUTSIDE']) ? (bool) $this->arParams['IS_OUTSIDE'] : $this->request->get('isOutside') === 'Y';

		if (!isset($this->arParams['IFRAME']))
		{
			$this->arParams['IFRAME'] = $this->request->get('IFRAME');
		}

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;

		$this->arParams['CAN_EDIT'] = 'Y';
		$this->arParams['CAN_VIEW'] = 'Y';
	}



	protected function preparePost()
	{
		global $USER;
		if($this->request->get('ID')>0){
			$data = [
				'TITLE' => $this->request->get('TITLE'),
				'USER_ID' => $USER->GetID(),
				'TEXT' => $this->request->get('TEXT'),
				'FILE_ID' => $this->request->get('FILE')
			];

			if(empty($data['TITLE'])){
				$error[] = 'Не заполнено обязательное поле «Название»';
			}

			if(empty($data['TEXT']) && empty($data['FILE_ID'])){
				$error[] = 'Для генерации выпусков рассылки, обязательно нужно заполнить поля «Картинка для рассылки» или «Текст рассылки»';
			}

			if(empty($error) && !empty($data['FILE_ID'])){
				$arFile = \Bitrix\Main\UI\FileInput::prepareFile($data['FILE_ID']);
				if (isset($arFile['tmp_name']) && !file_exists($arFile['tmp_name'])) {
					$tmpFilesDir = \CTempFile::GetAbsoluteRoot();
					$arFile['tmp_name'] = $tmpFilesDir . $arFile['tmp_name'];
				}
				if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/oe.telegrambot')) {
					mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/oe.telegrambot');
				}
				$saveFileId = \CFile::SaveFile($arFile, 'oe.telegrambot');
				$data['FILE_ID'] = intval($saveFileId);
			}

			if(empty($error)){
				PostingTable::update(
					intval($this->request->get('ID')),
					$data
				);
				LocalRedirect($this->arParams['PATH_TO_LIST']);
			}else{
				foreach ($error as $err)
				{
					ShowError($err);
				}
			}
		}else{
			$data = [
				'TITLE' => $this->request->get('TITLE'),
				'USER_ID' => $USER->GetID(),
				'STATUS' => 'D',
				'TEXT' => $this->request->get('TEXT'),
				'FILE_ID' => $this->request->get('FILE')
			];

			if(empty($data['TITLE'])){
				$error[] = 'Не заполнено обязательное поле «Название»';
			}

			if(empty($data['TEXT']) && empty($data['FILE_ID'])){
				$error[] = 'Для генерации выпусков рассылки, обязательно нужно заполнить поля «Картинка для рассылки» или «Текст рассылки»';
			}

			if(empty($error) && !empty($data['FILE_ID'])){
				$arFile = \Bitrix\Main\UI\FileInput::prepareFile($data['FILE_ID']);
				if (isset($arFile['tmp_name']) && !file_exists($arFile['tmp_name'])) {
					$tmpFilesDir = \CTempFile::GetAbsoluteRoot();
					$arFile['tmp_name'] = $tmpFilesDir . $arFile['tmp_name'];
				}
				if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/oe.telegrambot')) {
					mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/oe.telegrambot');
				}
				$saveFileId = \CFile::SaveFile($arFile, 'oe.telegrambot');
				$data['FILE_ID'] = intval($saveFileId);
			}

			if(empty($error)){
				PostingTable::add($data);
				LocalRedirect($this->arParams['PATH_TO_LIST']);
			}else{
				foreach ($error as $err)
				{
					ShowError($err);
				}
			}
		}


	}

	protected function prepareResult()
	{
		// Process POST
		if ($this->request->isPost() && check_bitrix_sessid())
		{
			$this->preparePost();
		}
		$this->arResult = [];
		if((int) $this->request->get('ID') > 0){
			$element = PostingTable::getList([
					'select'=>['*'],
					'filter'=>['ID'=>(int) $this->request->get('ID')]
			])->fetch();
			$this->arResult['ITEM'] = $element;
		}

		$this->arResult['SUBMIT_FORM_URL'] = Context::getCurrent()->getRequest()->getRequestUri();
		return true;
	}


	public function executeComponent()
	{
		$this->prepareResult();
		$this->includeComponentTemplate();
	}
}