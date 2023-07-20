<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CMain $APPLICATION*/
/** @var array $arResult*/
/** @var array $arParams*/

global $APPLICATION;
$APPLICATION->IncludeComponent(
	"oe:posting.letter.list",
	"",
	array(
		'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
		'PATH_TO_USER_PROFILE' => $arResult['PATH_TO_USER_PROFILE'],
		'FILTER_ID' => 'oe_tgbot_posting',
		'GRID_ID' => 'oe_tgbot_posting',
		'PATH_TO_LIST' => $arResult['PATH_TO_LIST'],
		'PATH_TO_ADD' => $arResult['PATH_TO_ADD'],
		'PATH_TO_EDIT' => $arResult['PATH_TO_EDIT'],
		'CAN_EDIT' => 'Y'
	)
);