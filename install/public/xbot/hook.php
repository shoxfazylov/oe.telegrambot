<?php
require $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php";

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Telegram\Bot\Api;

if (!Loader::includeModule("oe.telegrambot")) die();
global $botOptions, $iskeyword, $user;

$botOptions = new \Oe\Telegrambot\Options();
$token = $botOptions->getOption('main', 'bot_token');
if (!$token) die();
require $botOptions->getApiPath();
$telegram = new Api($token);
$result = $telegram->getWebhookUpdates();
$user = new \Oe\Telegrambot\User($result['message']['from']);
$keyboards = \Oe\Telegrambot\Entity\KeyboardTable::get($user->getId());

if (!empty($keyboards)) {
    foreach ($keyboards as $rsBoard) {
        foreach ($rsBoard as $board) {
            if ($board == $result["message"]["text"]) {
                $iskeyword = $board;
            }elseif($board['text'] == $result["message"]["text"]){
                $iskeyword = $board;
            }elseif(empty($iskeyword) && (isset($board["request_contact"]) || isset($board["request_location"]) || isset($board["search"]) || isset($board["auth"]))){
            	$iskeyword = $board;
            }
        }
    }
}

require __DIR__.'/typing.php';
if (isset($iskeyword['callback_data'])) {
    require __DIR__.'/callback.php';
} else {
    require __DIR__.'/action.php';
}