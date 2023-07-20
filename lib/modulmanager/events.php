<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Telegram\Bot\Api;


IncludeModuleLangFile(__FILE__);
Loader::IncludeModule("sale");

global $botOptions, $telegram;
$botOptions = new \Oe\Telegrambot\Options();
$token = $botOptions->getOption('main', 'bot_token');
if($token){
	require $botOptions->getApiPath();
	$telegram = new Api($token);
}

class OeTgBotEvents
{

	public function OnSaleOrderSaved(\Bitrix\Main\Event $event){
		/** @var Order $order */
		$order = $event->getParameter("ENTITY");
		$isNew = $event->getParameter("IS_NEW");
		$userId = $order->getUserId();
		$orderId = $order->getId();
		global $botOptions, $telegram;
		$currency = $botOptions->getOption('catalog', 'bot_catalog_currency');
		if ($isNew)
		{
			if($botOptions->getOption('push', 'bot_push_order_new') == 'Y' && strlen($botOptions->getOption('push', 'bot_push_address'))){
				$propsValues = Loc::getMessage('OE_TELEGRAMBOT_SALE_ORDER_NEW',[
					"#ID#"	=>	$order->getId(),
					"#DATE_INSERT#"	=>	$order->getField("DATE_INSERT")
				]) . PHP_EOL;

				$propertyCollection = $order->getPropertyCollection();
				$propsValues .= Loc::getMessage('OE_TELEGRAMBOT_SALE_ORDER_USER',['#USER#'=>$propertyCollection->getPayerName()->getValue()]) . PHP_EOL;
				$propsValues .= Loc::getMessage('OE_TELEGRAMBOT_SALE_ORDER_PHONE',['#PHONE#'=>$propertyCollection->getPhone()->getValue()]) . PHP_EOL;
				$propsValues .= Loc::getMessage('OE_TELEGRAMBOT_SALE_ORDER_ADDRESS',['#ADDRESS#'=>$propertyCollection->getAddress()->getValue()]) . PHP_EOL;

				$shipmentCollection = $order->getShipmentCollection();
				$shipment = reset(end($shipmentCollection));
				if($shipment->getDeliveryName())
					$propsValues .= Loc::getMessage('ORDER_DELIVERY_FIELD', ['#DELIVERY#' => $shipment->getDeliveryName()]) . PHP_EOL;

				$paymentCollection = $order->getPaymentCollection();
				$payment = reset(end($paymentCollection));
				if($payment->getPaymentSystemName())
					$propsValues .= Loc::getMessage('ORDER_PAYSYSTEM_FIELD', ['#PAYSYS#' => $payment->getPaymentSystemName()]) . PHP_EOL;

				$propsValues .=	!$order->isPaid() ? Loc::getMessage('PAY_FALSE').PHP_EOL : Loc::getMessage('PAY_TRUE').PHP_EOL;
				$propsValues .= Loc::getMessage('ORDER_BASKET_ITEMS'). PHP_EOL . PHP_EOL;
				$basket = $order->getBasket();
				foreach ($basket as $basketItem) {
					$propsValues .= $basketItem->getField('NAME') . ' - ' . $basketItem->getQuantity() .$basketItem->getField('MEASURE_NAME'). PHP_EOL;
				}
				$propsValues .= PHP_EOL;
				$propsValues .= Loc::getMessage('ORDER_BASKET_BASE_PRICE',['#BASE_PRICE#'=>\Oe\Telegrambot\Utils::priceFormat($basket->getBasePrice(), $order->getField("CURRENCY"), $currency)]). PHP_EOL;
				$propsValues .= Loc::getMessage('ORDER_BASKET_PRICE',['#PRICE#'=>\Oe\Telegrambot\Utils::priceFormat($basket->getPrice(), $order->getField("CURRENCY"), $currency)]). PHP_EOL;
				$propsValues .= Loc::getMessage('ORDER_DELIVERY_PRICE',['#DELIVERY_PRICE#'=>\Oe\Telegrambot\Utils::priceFormat($order->getField("PRICE_DELIVERY"), $order->getField("CURRENCY"), $currency)]). PHP_EOL;
				$propsValues .= Loc::getMessage('ORDER_TOTAL_PRICE',['#TOTAL_PRICE#'=>\Oe\Telegrambot\Utils::priceFormat($order->getField("PRICE"), $order->getField("CURRENCY"), $currency)]). PHP_EOL;


				$bot = $telegram->getMe();
				$push = $botOptions->getOption('push', 'bot_push_address');

				try {
				    $result = $telegram->getChatMember(['chat_id' => $push,'user_id' => $bot['id']]);
				    if($result['status'] == 'administrator'){
				        $telegram->sendMessage([
				            'chat_id' => $push,
				            'text' => $propsValues,
				            'parse_mode' => 'HTML'
				        ]);
				    }
				} catch (Exception $e) {}

			}
		}else{
			if($botOptions->getOption('push', 'bot_push_order_change_status') == 'Y' && !$order->isCanceled()){
				$status = \CSaleStatus::GetByID($order->getField('STATUS_ID'));
				$filter = ["ID" => $userId];
				$select = ["SELECT"=>["ID","UF_*"]];
				$arUser = \CUser::GetList(($by="id"), ($order="desc"), $filter, $select)->Fetch();
				if($arUser['UF_TGBOT_CHAT_ID']){
					$telegram->sendMessage([
						'chat_id' => $arUser['UF_TGBOT_CHAT_ID'],
						'text' => Loc::getMessage('OE_TELEGRAMBOT_SALE_ORDER_STATUS', ['#ID#' => $orderId, '#STATUS#'=>$status['NAME']]),
						'parse_mode' => 'HTML'
					]);
				}
			}
		}
	}

	public function OnBuildGlobalMenuHandler(&$arGlobalMenu){
		if (!isset($arGlobalMenu['global_menu_oe'])) {
			$arGlobalMenu['global_menu_oe'] = [
				'menu_id'   => 'oe',
				'text'      => 'Open Engine',
				'title'     => 'Open Engine',
				'sort'      => 1000,
				'items_id'  => 'global_menu_oe_items',
				"icon"      => "",
				"page_icon" => "",
			];
		}

		$aMenu = [
			"parent_menu" => "global_menu_oe",
			"section" => 'oe.telegrambot',
			"sort" => 1,
			"text" => Loc::getMessage("OE_TELEGRAMBOT_MENU_MAIN"),
			"title" => Loc::getMessage("OE_TELEGRAMBOT_MENU_MAIN"),
			"icon" => "oe_telegrambot_menu_icon",
			"items_id" => "menu_oe.telegrambot",
			"items" => [
				[
					"text" => Loc::getMessage("OE_TELEGRAMBOT_SETTINGS"),
					"title" => Loc::getMessage("OE_TELEGRAMBOT_SETTINGS"),
					"url" =>  "oe_telegrambot_settings.php?lang=" . LANGUAGE_ID
				],
				[
					"text" => 'Рассылка',
					"title" => 'Рассылка в телеграме',
					"url" =>  "oe_telegrambot_posting.php?lang=" . LANGUAGE_ID
				]
			]
		];

		$arGlobalMenu['global_menu_oe']['items']['oe.telegrambot'] = $aMenu;

	}
}
?>