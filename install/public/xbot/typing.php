<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$chatId = $result->isType('callback_query') ? $result['callback_query']['message']['chat']['id'] : $result["message"]["chat"]["id"];
	if(!empty($chatId))
		$telegram->sendChatAction([
		  'chat_id' => $chatId,
		  'action' => 'typing'
		]);

sleep(0.5);