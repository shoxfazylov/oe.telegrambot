<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
if($request->get("id")){
    $APPLICATION->SetTitle("Order Payed №".$request->get("id"));
    \Bitrix\Main\Loader::includeModule('sale');
    \Bitrix\Main\Loader::includeModule('oe.telegrambot');
    $order = \Bitrix\Sale\Order::load($request->get("id"));
    $button = \Oe\Telegrambot\Order::_getOrderPayButton($order);
    echo $button;
}