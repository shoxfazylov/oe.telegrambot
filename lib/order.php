<?
namespace Oe\Telegrambot;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use \Oe\Telegrambot\Entity\OrderTable;
use Spatie\Emoji\Emoji;

Loader::includeModule("sale");
Loader::includeModule("currency");

class Order
{
    protected $opt = false;
    protected $orderFields = false;
    protected $currency = 'RUB';

    public function __construct()
    {
        global $botOptions;
        $this->opt = $botOptions;
        $this->currency = $this->opt->getOption('catalog', 'bot_catalog_currency');
    }

    public function createOrder($userid = false)
    {
        // ������� ��������� �����
        $products = [];
		$arFUser = \CSaleUser::GetList(array('USER_ID' => $userid));
		$fUser = $arFUser['ID'];
		$siteId = $this->opt->getOption('main', 'bot_site_id');
		$basket = \Bitrix\Sale\Basket::loadItemsForFUser($fUser, $siteId);
		$min = $this->opt->getOption('order', 'bot_order_min_price');
		$bCurrency = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
		if($bCurrency != $this->currency){
			$total = \CCurrencyRates::ConvertCurrency($basket->getPrice(), $bCurrency, $this->currency);
		}else{
			$total = $basket->getPrice();
		}

		if($total >= $min){
			foreach ($basket as $item){
				$products[] = $item->getProductId();
			}
			$order = OrderTable::add([
					'USER_ID'=> $userid,
					'PRODUCTS'=> serialize($products),
			]);
			return $this->getContact($order->getId());
		}else{
			$sum = html_entity_decode(Utils::priceFormat($min, $this->currency));
			return new Msg(str_replace('#PRICE#', $sum, $this->opt->getOption('order', 'bot_order_min_price_text')), []);
		}
    }

    public function getContact($orderId)
    {
        $keyboard = [
            [[
                'text' => $this->opt->getTitle('GET_FEEDBACK_PHONE'),
                'request_contact' => true,
                'callback_data' => Utils::getCallBackStr([
                    'case' => $this->opt->getCase('createOrder'),
                    'order_id'=>$orderId,
                    'type'=>'location'
                ])
            ]]
        ];

        return new Msg(Emoji::CHARACTER_BLACK_TELEPHONE.' '.GetMessage('ORDER_GET_CONTACT'), $keyboard);
    }

    public function setContact($value, $orderId)
    {
        OrderTable::update($orderId, ['PHONE'=>$value]);
    }

    public function getLocation($orderId = false)
    {
        $keyboard = [
            [[
                'text' => $this->opt->getTitle('GET_FEEDBACK_LOCATION'),
                'request_location' => true,
                'callback_data' => Utils::getCallBackStr([
                    'case' => $this->opt->getCase('createOrder'),
                    'order_id'=>$orderId,
                    'type'=>'paysystem'
                ])
            ]]
        ];

        return new Msg(Emoji::CHARACTER_ROUND_PUSHPIN.' '.GetMessage('ORDER_GET_LOCATION'), $keyboard);
    }

    public function setLocation($value, $orderId)
    {
        if(is_object($value)){
            $value = $this->_getLocation($value);
        }

        OrderTable::update($orderId, ['LOCATION'=>$value]);

    }

    public function getPaysystem($orderId)
    {
        $text = GetMessage('ORDER_GET_PAYSYSTEM');
        $optFields = $this->opt->getOption('order', 'bot_order_paysystem');
        if($optFields == NULL) return new Msg('ORDER_ERROR_PAYSYS');;
        $optFields = explode(',', $optFields);

        $paySystemResult = \Bitrix\Sale\PaySystem\Manager::getList([
            'filter'  => [
                'ACTIVE' => 'Y',
                '=ID'=>$optFields
            ]
        ]);
        $keyboard = [];
        while ($paySystem = $paySystemResult->fetch()) {
            $keyboard[] = [
                'text' => trim($paySystem['NAME']),
                'callback_data' => Utils::getCallBackStr([
                    'case' => $this->opt->getCase('createOrder'),
                    'paysytem_id' => $paySystem['ID'],
                    'order_id'=>$orderId,
                    'type' => 'delivery'
                ]),
            ];
        }

        $keyboard = array_chunk($keyboard, 2);
        return new Msg($text, $keyboard);
    }

    public function setPaysystem($value = false, $orderId)
    {
        OrderTable::update($orderId, ['PAYSYSTEM_ID'=>$value]);
    }

    public function getDelivery($orderId)
    {
        $text = GetMessage('ORDER_GET_DELIVERY');
        $optFields = $this->opt->getOption('order', 'bot_order_delivery');
        if($optFields == NULL) return new Msg('ORDER_ERROR_DELIVERY');;
        $optFields = explode(',', $optFields);

        $deliveryResult = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
        $keyboard = [];
        foreach ($deliveryResult as $delivery) {
            if(in_array($delivery['ID'], $optFields)){
                $keyboard[] = [
                    'text' => trim($delivery['NAME']),
                    'callback_data' => Utils::getCallBackStr([
                        'case' => $this->opt->getCase('createOrder'),
                        'delivery_id' => $delivery['ID'],
                        'order_id'=>$orderId,
                        'type' => 'checkorder'
                    ]),
                ];
            }
        }

        $keyboard = array_chunk($keyboard, 2);
        return new Msg($text, $keyboard);
    }

    public function setDelivery($value = false, $orderId)
    {
        OrderTable::update($orderId, ['DELIVERY_ID'=>$value]);
    }

    public function checkOrder($orderId)
    {
    	$tOrder = OrderTable::getList([
    		'filter' => ['ID'=>$orderId]
    	])->fetch();

    	$arFUser = \CSaleUser::GetList(array('USER_ID' => $tOrder['USER_ID']));
    	$fUser = $arFUser['ID'];
    	$siteId = $this->opt->getOption('main', 'bot_site_id');
    	$person = $this->opt->getOption('order', 'bot_order_person_type');

    	// ������ ����� �����
    	$order = \Bitrix\Sale\order::create($siteId, $tOrder['USER_ID']);

    	$order->setPersonTypeId($person);
    	$order->setField('USER_DESCRIPTION', GetMessage('ORDER_COMMENT'));
    	$propertyCollection = $order->getPropertyCollection();

    	// ������ ������� � ��������
    	$basket = \Bitrix\Sale\Basket::loadItemsForFUser($fUser, $siteId)->getOrderableItems();
    	$order->setBasket($basket);

    	// ������ ��������
    	$shipmentCollection = $order->getShipmentCollection();
    	$shipment = $shipmentCollection->createItem();
    	$service = \Bitrix\Sale\Delivery\Services\Manager::getById($tOrder['DELIVERY_ID']);
    	$shipment->setFields(array(
    			'DELIVERY_ID' => $service['ID'],
    			'DELIVERY_NAME' => $service['NAME'],
    	));
    	$shipmentItemCollection = $shipment->getShipmentItemCollection();
    	foreach ($order->getBasket() as $item)
    	{
    		$shipmentItem = $shipmentItemCollection->createItem($item);
    		$shipmentItem->setQuantity($item->getQuantity());
    	}

    	// ������ ������
    	$paymentCollection = $order->getPaymentCollection();
    	$payment = $paymentCollection->createItem();
    	$paySystemService = \Bitrix\Sale\PaySystem\Manager::getObjectById($tOrder['PAYSYSTEM_ID']);
    	$payment->setFields(array(
    			'PAY_SYSTEM_ID' => $paySystemService->getField("PAY_SYSTEM_ID"),
    			'PAY_SYSTEM_NAME' => $paySystemService->getField("NAME"),
    	));

    	// �������
    	$contact = $this->opt->getOption('order', 'bot_order_props_phone');
    	$phoneProperty = $this->_getPropertyByCode($propertyCollection, $contact);
    	$phoneProperty->setValue($tOrder['PHONE']);

    	// �����
    	$address = $this->opt->getOption('order', 'bot_order_props_address');
    	$addressProperty = $this->_getPropertyByCode($propertyCollection, $address);
    	$addressProperty->setValue($tOrder['LOCATION']);

    	// ��������� �����
    	$order->doFinalAction(true);
    	$result = $order->save();


    	if(!$result->isSuccess())
    	{
    		return new Msg(GetMessage('ERROR_ORDER_CREATE'), []);
    	}
    	else
    	{
    		OrderTable::update($orderId, ['ORDER_ID'=>$order->GetId()]);
    		$orderId = $order->GetId();
            $text = $this->_getOrderFieldsText($order->GetId());

            $keyboard = [
                [[
                	'text' => Emoji::CHARACTER_CROSS_MARK.' '.GetMessage('ORDER_CANCEL'),
                    'callback_data' => Utils::getCallBackStr([
                        'case' => $this->opt->getCase('createOrder'),
                        'order_id'=>$orderId,
                        'type' => 'cancel'
                    ])
                ],
                    [
                    	'text' => Emoji::CHARACTER_WHITE_HEAVY_CHECK_MARK.' '.GetMessage('ORDER_APPLY'),
                        'callback_data' => Utils::getCallBackStr([
                            'case' => $this->opt->getCase('createOrder'),
                            'order_id'=>$orderId,
                            'type' => 'apply',
                        ])
                    ]
                ]
            ];

            return new Msg($text, $keyboard);
        }
    }

    public function cancelOrder($orderId)
    {
        if($orderId) $order = \Bitrix\Sale\order::load($orderId);

        $keyboard = [
            [[
                'text' => GetMessage('ORDER_CANCEL_1'),
                'callback_data' => Utils::getCallBackStr([
                    'case' => $this->opt->getCase('createOrder'),
                    'order_id'=>$orderId,
                    'type' => 'cancelconfirm'
                ])
            ]],
            [[
                'text' => GetMessage('ORDER_CANCEL_2'),
                'callback_data' => Utils::getCallBackStr([
                    'case' => $this->opt->getCase('createOrder'),
                    'order_id'=>$orderId,
                    'type' => 'cancelconfirm'
                ])
            ]],
            [[
                'text' => GetMessage('ORDER_CANCEL_3'),
                'callback_data' => Utils::getCallBackStr([
                    'case' => $this->opt->getCase('createOrder'),
                    'order_id'=>$orderId,
                    'type' => 'cancelconfirm'
                ])
            ]],
            [[
                'text' => GetMessage('ORDER_CANCEL_4'),
                'callback_data' => Utils::getCallBackStr([
                    'case' => $this->opt->getCase('createOrder'),
                    'order_id'=>$orderId,
                    'type' => 'cancelconfirm'
                ])
            ]],
        ];

        return new Msg(GetMessage('ORDER_CANCEL_TEXT', ['#ID#' => $order->GetId()]), $keyboard);
    }

    public function cancelConfirmOrder($text, $orderId)
    {
        if($orderId) $order = \Bitrix\Sale\order::load($orderId);
        $order->setField('CANCELED', 'Y');
        $order->setField('REASON_CANCELED', $text);
        $order->save();

        $main = new \Oe\Telegrambot\Main();
        $msg = $main->getMainMessage();
        $keyboard = $msg->getKeyboard();

        return new Msg(GetMessage('ORDER_CANCEL_CONFIRM'), $keyboard);
    }

    public function saveOrder($orderId)
    {
        if($orderId) $order = \Bitrix\Sale\order::load($orderId);
        $order->doFinalAction(true);
        $button = $this->_getOrderPayButton($order);
        $keyboard = [];
        if(strlen($button) > 0){
            $keyboard[] = [['text' => GetMessage('PAY_ORDER'), 'url' => $_SERVER['SERVER_NAME'].'/xbot/payment.php?id='.$order->getId()]];
        }
        return new Msg(GetMessage('ORDER_SUCCESS', ['#ID#' => $order->GetId()]), $keyboard);
    }



    /*FUNCTIONS*/
    private function _getPropertyByCode($propertyCollection, $code)  {
        foreach ($propertyCollection as $property)
        {
            if($property->getField('CODE') == $code)
                return $property;
        }
    }

    private function _getLocation($arrLoc = [])
    {
    	$lon = $arrLoc['longitude'] ? : false;
    	$lat = $arrLoc['latitude'] ? : false;

    	if(!$lon || !$lat) return false;
    	$yandexApiKey = $this->opt->getOption('order', 'bot_order_yandex_apikey') ?:'b6c0073a-807a-436c-9088-fbe8e1c75091'; //bonus

    	$url = "https://geocode-maps.yandex.ru/1.x/?apikey={$yandexApiKey}&geocode={$lon},{$lat}";
    	libxml_use_internal_errors(true);
    	$location = @simplexml_load_file($url);

    	if($location !== FALSE)
    	{
    		$location = Utils::xml2array($location);
    		$foundPoints = $location['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'];

    		if($foundPoints > 0)
    		{
    			$featureMember = Utils::xml2array($location['GeoObjectCollection']['featureMember'][0]);
    			$address = $featureMember['GeoObject']['metaDataProperty']['GeocoderMetaData']['text'];
    			return $address;
    		}
    		else
    		{
    			return implode(",", $arrLoc);
    		}
    	}else{
    		foreach(libxml_get_errors() as $error) {
    			return $url;
    		}
    		return implode(",", $arrLoc);
    	}
    }

    public function _getOrderFieldsText($orderId = false)
    {
        if($orderId)
        {
            $order = \Bitrix\Sale\order::load($orderId);
            if($order->getId() > 0)
            {
                $propsValues = GetMessage('ORDER_CHECK_FIELDS') . PHP_EOL;
                $propertyCollection = $order->getPropertyCollection();
                $props = [$this->opt->getOption('order', 'bot_order_props_address'), $this->opt->getOption('order', 'bot_order_props_phone')];

                foreach ($propertyCollection as $property) {
                	$arProp = $property->getProperty();
                	if(in_array($arProp['CODE'], $props)){
                		if(!empty($property->getValue()) || $property->getValue() !== NULL){
                			$propsValues .= GetMessage('ORDER_PROP_FIELD', ['#NAME#'=>$property->getName(), '#VALUE#'=>$property->getValue()]). PHP_EOL;
                		}
                	}
                }

                $shipmentCollection = $order->getShipmentCollection();
                $shipment = reset(end($shipmentCollection));
                if($shipment->getDeliveryName())
                    $propsValues .= GetMessage('ORDER_DELIVERY_FIELD', ['#DELIVERY#' => $shipment->getDeliveryName()]) . PHP_EOL;

                $paymentCollection = $order->getPaymentCollection();
                $payment = reset(end($paymentCollection));
                if($payment->getPaymentSystemName())
                    $propsValues .= GetMessage('ORDER_PAYSYSTEM_FIELD', ['#PAYSYS#' => $payment->getPaymentSystemName()]) . PHP_EOL . PHP_EOL;

                $propsValues .= GetMessage('ORDER_BASKET_ITEMS'). PHP_EOL;
                $basket = $order->getBasket();
                foreach ($basket as $basketItem) {
                    $propsValues .= $basketItem->getField('NAME') . ' - ' . $basketItem->getQuantity() .$basketItem->getField('MEASURE_NAME'). PHP_EOL;
                }
                $propsValues .= PHP_EOL;
                $propsValues .= GetMessage('ORDER_BASKET_BASE_PRICE',['#BASE_PRICE#'=>Utils::priceFormat($basket->getBasePrice(), $order->getField("CURRENCY"), $this->currency)]). PHP_EOL;
                $propsValues .= GetMessage('ORDER_BASKET_PRICE',['#PRICE#'=>Utils::priceFormat($basket->getPrice(), $order->getField("CURRENCY"), $this->currency)]). PHP_EOL;
                $propsValues .= GetMessage('ORDER_DELIVERY_PRICE',['#DELIVERY_PRICE#'=>Utils::priceFormat($order->getField("PRICE_DELIVERY"), $order->getField("CURRENCY"), $this->currency)]). PHP_EOL;
                $propsValues .= GetMessage('ORDER_TOTAL_PRICE',['#TOTAL_PRICE#'=>Utils::priceFormat($order->getField("PRICE"), $order->getField("CURRENCY"), $this->currency)]). PHP_EOL;
            }

            if(!empty($propsValues))
                return $propsValues;
        }
    }

    public function getOrderStatusList()
    {
        $dbStatus = \CSaleStatus::GetList(['SORT' => 'ASC'], ['LID' => 'ru']);
        while($obStatus = $dbStatus->GetNext())
        {
            $keyboard[] = [['text' => trim($obStatus['NAME']), 'callback_data' => Utils::getCallBackStr(['case' => $this->opt->getCase('getOrderList'), 'id' => $obStatus['ID']])]];
        }

        $bottom = [[
            [
                'text' => $this->opt->getTitle('BACK_BUTTON'),
                'callback_data' => Utils::getCallBackStr([
                    'case' => $this->opt->getCase('getProfile')
                ])
            ],
            [
                'text' => $this->opt->getTitle('MAIN'),
                'callback_data' => Utils::getCallBackStr([
                    'case' => $this->opt->getCase('getMain')
                ])
            ]
        ]];
        $keyboard = array_merge($keyboard, $bottom);
        $text = GetMessage('CHOOSE_STATUS');
        return new Msg($text, $keyboard);
    }

    public function getOrderList($status = 'N')
    {
        global $USER;
        $uid = $USER->GetID();

        $status = \CSaleStatus::GetByID($status);
        $text = GetMessage('ORDER_LIST_TEXT', ['#STATUS_NAME#' => $status['NAME']]);
        $dbOrders = \CSaleOrder::GetList(["DATE_INSERT" => "DESC"], ['USER_ID' => $uid, 'STATUS_ID' => $status['ID'], 'CANCELED'=>'N']);

        while($obOrders = $dbOrders->GetNext())
        {
        	$priceStr = Utils::priceFormat(
        			$obOrders['PRICE'],
        			$obOrders['CURRENCY'],
        			$this->currency
        	);
            $keyboard[] = [['text' => trim(GetMessage('ORDER_ITEM', ['#ID#'=>$obOrders['ID'], '#SUM#' => $priceStr])), 'callback_data' => Utils::getCallBackStr(['case' => $this->opt->getCase('getOrder'), 'id' => $obOrders['ID']])]];
        }

        $keyboard[] = [['text' => $this->opt->getTitle('BACK_BUTTON'), 'callback_data' => Utils::getCallBackStr(['case' => $this->opt->getCase('getOrderStatusList')])]];

        return new Msg($text, $keyboard);
    }

    public function getOrder($orderId = 0)
    {
        global $USER;
        $uid = $USER->GetID();
        //$fUser = \CSaleBasket::GetBasketUserID();
        $siteId = $this->opt->getOption('main', 'bot_site_id');

        $order = \Bitrix\Sale\Order::load($orderId);
        if($order)
        {
            $status = \CSaleStatus::GetByID($order->getField("STATUS_ID"));
            $priceStr = Utils::priceFormat(
            	$order->getPrice(),
            	$order->getCurrency(),
            	$this->currency
            );
            $deliveryPriceStr = Utils::priceFormat(
            	$order->getDeliveryPrice(),
            	$order->getCurrency(),
            	$this->currency
            );

            $arDeliv = \Bitrix\Sale\Delivery\Services\Manager::getById($order->getField("DELIVERY_ID"));
            $arPaySys = \CSalePaySystem::GetByID($order->getField("PAY_SYSTEM_ID"), $order->getPersonTypeId());

            $items = '';
            $dbBasketItems = \CSaleBasket::GetList(array("ID" => "DESC"), array("LID" => $siteId, "ORDER_ID" => $orderId), false, false, array());

            while ($arItem = $dbBasketItems->Fetch())
            {
            	$priceItem = Utils::priceFormat(
            		$arItem['PRICE'] * $arItem['QUANTITY'],
            		$arItem['CURRENCY'],
            		$this->currency
            	);
                $items .= $arItem['NAME'] . ' - ' . $priceItem . PHP_EOL;
            }

            $text = GetMessage('FULL_ORDER', [
                '#ID#' => $order->getId(),
                '#DATE_INSERT#' => $order->getField("DATE_INSERT"),
                '#STATUS#' => $status['NAME'],
            	'#SUM#' => $priceStr,
            	'#DELIVERY_SUM#' => $deliveryPriceStr,
                '#DELIVERY#' => $arDeliv['NAME'],
                '#PAYSYSTEM#' => $arPaySys["NAME"],
                '#PAY_STATUS#' => !$order->isPaid() ? GetMessage('PAY_FALSE') : GetMessage('PAY_TRUE'),
                '#ITEMS#' => PHP_EOL . $items
            ]);
            $keyboard = [];
            $button = $this->_getOrderPayButton($order);
            if(strlen($button) > 0){
                $keyboard[] = [['text' => GetMessage('PAY_ORDER'), 'url' => $_SERVER['SERVER_NAME'].'/xbot/payment.php?id='.$order->getId()]];
            }
        }

        return new Msg($text, $keyboard);
    }

    public function _getOrderPayButton(\Bitrix\Sale\order $order)
    {
        $currentPayment = $order->getPaymentCollection()->current();
        $service = \Bitrix\Sale\PaySystem\Manager::getObjectById($currentPayment->getPaymentSystemId());
        $initResult = $service->initiatePay($currentPayment, null, \Bitrix\Sale\PaySystem\BaseServiceHandler::STRING);
        if ($initResult->isSuccess()){
            $button = $initResult->getTemplate();
        }else{
            $button = implode('\n', $initResult->getErrorMessages());
        }

        return $button;
    }




}