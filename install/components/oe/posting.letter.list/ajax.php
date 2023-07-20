<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
Bitrix\Main\Loader::includeModule('oe.telegrambot');

use \Oe\Telegrambot\Entity\PostingTable;
use \Oe\Telegrambot\Agent;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if($request->get('action') && $request->get('id')){
	$remove = false;
	$updateFields = [];
	switch ($request->get('action')){
		case 'start':
			$result = Agent::SendPosting();
			die(json_encode($result));
			break;
		case 'remove':
			$remove = true;
			break;
		case 'send':
			// get rows
			$selectParameters = array(
				'filter' => ['STATUS'=>'S'],
				'count_total' => true
			);
			$list = PostingTable::getList($selectParameters)->getCount();
			if($list > 0){
				die(json_encode(['result'=>'exist']));
			}else{
				$updateFields['STATUS'] = 'S';
			}

			break;
		case 'pause':
			$updateFields['STATUS'] = 'P';
			break;
		case 'stop':
			$updateFields['STATUS'] = 'X';
			break;
		case 'resume':
			$updateFields['STATUS'] = 'S';
			break;
		case 'finish':
			$updateFields['STATUS'] = 'Y';
			break;
	}
	if($remove){
		PostingTable::delete(intval($request->get('id')));
	}
	PostingTable::update(
		intval($request->get('id')),
		$updateFields
	);
	echo json_encode(['succes'=>true]);
}
