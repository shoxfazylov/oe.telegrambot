<?
namespace Oe\Telegrambot;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use \Oe\Telegrambot\Options as Opt;
use Spatie\Emoji\Emoji;

Loader::includeModule("catalog");
Loader::includeModule("sale");

class CatalogUtils
{
    public static function getPrice($elementId = false, $offerid = false)
    {
        if(!$elementId) return false;
        global $botOptions;
        global $USER;
        $discountStr = '';
        $dbPrice = \CPrice::GetList([], ["PRODUCT_ID" => $offerid ? $offerid : $elementId, 'CATALOG_GROUP_ID'=>$botOptions->getOption('catalog', 'bot_catalog_price')], false, false, ["ID", "PRICE", "CURRENCY"]);
        if ($arPrice = $dbPrice->Fetch())
        {
        	$arDiscounts = \CCatalogDiscount::GetDiscountByPrice(
        			$arPrice["ID"],
        			$USER->GetUserGroupArray(),
        			"N",
        			$botOptions->getOption('main', 'bot_site_id')
        	);
        	$discountPrice = \CCatalogProduct::CountPriceWithDiscount(
        			$arPrice["PRICE"],
        			$arPrice["CURRENCY"],
        			$arDiscounts
        	);
        	$arPrice["DISCOUNT_PRICE"] = $discountPrice;
        }

        $priceStr = '';
        if(!empty($arPrice))
        {
        	$currency = $botOptions->getOption('catalog', 'bot_catalog_currency');
        	if($currency != $arPrice['CURRENCY']){
        		$priceStr = \CCurrencyLang::CurrencyFormat(
        			\CCurrencyRates::ConvertCurrency($arPrice['PRICE'], $arPrice['CURRENCY'], $currency),
        			$currency,
        			TRUE
        		);

        		if($arPrice['DISCOUNT_PRICE'] != $arPrice['PRICE']){
        			$discountStr = \CCurrencyLang::CurrencyFormat(
        				\CCurrencyRates::ConvertCurrency($arPrice['DISCOUNT_PRICE'], $arPrice['CURRENCY'], $currency),
        				$currency,
        				TRUE
        			);
        		}
        	}else{
        		$priceStr = \CCurrencyLang::CurrencyFormat(
        			$arPrice['PRICE'],
        			$arPrice['CURRENCY'],
        			TRUE
        		);

        		if($arPrice['DISCOUNT_PRICE'] != $arPrice['PRICE']){
        			$discountStr = \CCurrencyLang::CurrencyFormat(
        				$arPrice['DISCOUNT_PRICE'],
        				$arPrice['CURRENCY'],
        				TRUE
        			);
        		}
        	}
        }

        $priceStr = html_entity_decode($priceStr);
        $discountStr = html_entity_decode($discountStr);

        return ($discountStr)?($discountStr.GetMessage('IS_СROSSED', ['#TEXT#' => $priceStr])):($priceStr);
    }

    public static function getDeliveryPrice($arDelivery)
    {
        if(!$arDelivery['PRICE'] || !$arDelivery['CURRENCY']) return false;

        $priceStr = \CCurrencyLang::CurrencyFormat(
            $arDelivery['PRICE'],
            $arDelivery['CURRENCY'],
            TRUE
        );

        return html_entity_decode($priceStr);
    }

    public static function getOfferProperty($elementId = false, $offerId = false)
    {
        if(!$elementId) return false;

        global $botOptions;
        $element = \CCatalogProduct::GetByID($elementId);
        if($element['TYPE'] == \Bitrix\Catalog\ProductTable::TYPE_SKU){
            $value = $codes = array();
            $skuProps = explode(',', $botOptions->getOption('catalog', 'bot_catalog_iblock_offer_prop'));
            if(count($skuProps) > 0){
                foreach ($skuProps as &$code){
                    $code = explode('|', $code);
                    $codes[] = $code[0];
                }

                $arInfo = \CCatalogSKU::GetInfoByProductIBlock($botOptions->getOption('catalog', 'bot_catalog_iblock'));
                $rsOffers = \CIBlockElement::GetList(array(),array('IBLOCK_ID' => $arInfo['IBLOCK_ID'], 'PROPERTY_'.$arInfo['SKU_PROPERTY_ID'] => $elementId, 'CATALOG_AVAILABLE'=>'Y'));
                while ($obOffer = $rsOffers->GetNextElement())
                {
                    $el = $obOffer->GetFields();
                    $el["PROPERTIES"] = $obOffer->GetProperties();
                    foreach ($el["PROPERTIES"] as $arProp){
                        if(in_array($arProp['CODE'], $codes) && strlen($arProp['~VALUE'])>0 && $arProp['ID'] != $arInfo['SKU_PROPERTY_ID']){
                            $sid = !empty($arProp['USER_TYPE_SETTINGS']) ? $arProp['VALUE'] : $arProp['VALUE_ENUM_ID'];
                            $value[$arProp['ID']][$sid] = $arProp['NAME'];
                        }
                    }
                }

                foreach ($value as $id=>$val){
                    if(count($val)>1){
                        $name = array_shift($val);
                        $keyboard = array(
                            'text'=> trim($name),
                            'callback_data' => Utils::getCallBackStr([
                                'case' => 'viewOfferProp',
                                'id' => $elementId,
                                'offerid'=> $offerId,
                                'propid' => $id
                            ])
                        );
                        $propKeyboard[] = $keyboard;
                    }
                }
            }
        }

        return !empty($propKeyboard) ? array_chunk($propKeyboard, 2) : false;
    }

    public static function getElementPageOfferProperty($elementId = false)
    {

        return !empty($propKeyboard) ? array_chunk($propKeyboard, 2) : false;
    }

    public static function getSelectOffer($elementId = false)
    {
        if(!$elementId) return false;

        global $botOptions;
        $offerId = false;
        $product = \Bitrix\Catalog\ProductTable::getList([
            "select" => [
                "IBLOCK_ID" => "IBLOCK_ELEMENT.IBLOCK_ID",
                "*"
            ],
            "filter" => [
                "ID" => $elementId
            ]
        ])->fetch();

        if($product['TYPE'] == \Bitrix\Catalog\ProductTable::TYPE_SKU){
            $arInfo = \CCatalogSKU::GetInfoByProductIBlock($product['IBLOCK_ID']);
            $filter = ['IBLOCK_ID' => $arInfo['IBLOCK_ID'], 'PROPERTY_'.$arInfo['SKU_PROPERTY_ID'] => $elementId, 'CATALOG_AVAILABLE'=>'Y'];
            $rsOffers = \CIBlockElement::GetList(["ID"=>"ASC"], $filter, false, false, ['ID']);
            while ($obOffer = $rsOffers->fetch())
            {
                $offerId = $obOffer['ID'];
            }
        }
        return $offerId;
    }

    public static function addOrderProperty($code, $value, $order)
    {
        if (!strlen($code)) return false;

        if ($arProp = \CSaleOrderProps::GetList(array(), array('CODE' => $code))->Fetch())
        {
            return \CSaleOrderPropsValue::Add(array(
                'NAME' => $arProp['NAME'],
                'CODE' => $arProp['CODE'],
                'ORDER_PROPS_ID' => $arProp['ID'],
                'ORDER_ID' => $order,
                'VALUE' => $value,
            ));
        }
    }

    public static function getPayLink($orderId = 0)
    {
        if(!$orderId) return false;

        global $botOptions;
        global $USER;

        $redirectUrl = $botOptions->getOption('main', 'bot_redirect');
        //AddMessage2Log($redirectUrl);
        $siteUrl = $botOptions->getOption('main', 'bot_siteurl');
        $pathToComponent = $botOptions->getOption('order', 'bot_order_component') . '?ORDER_ID=' . $orderId;

        $login = $USER->GetLogin();
        $hash = $USER->GetParam("PASSWORD_HASH");
        $redirect = $siteUrl . $pathToComponent;

        return $siteUrl . "{$redirectUrl}?UID={$login}&AUTH={$hash}&REDIRECT={$redirect}";
    }

    public static function getMeasure($elementId = false)
    {
        if(!$elementId) return false;

        $dbMeasure = \CCatalogMeasureRatio::getList([],['PRODUCT_ID' => $elementId]);
        $obMeasure = $dbMeasure->GetNext();

        return $obMeasure ? $obMeasure['RATIO'] : 1;
    }

    public static function getCountItem($elementId)
    {
        if(!$elementId) return false;
        $ids = [$elementId];
        global $botOptions;
        $element = \CCatalogProduct::GetByID($elementId);
        if($element['TYPE'] == \Bitrix\Catalog\ProductTable::TYPE_SKU){
            $arInfo = \CCatalogSKU::GetInfoByProductIBlock($botOptions->getOption('catalog', 'bot_catalog_iblock'));
            $rsOffers = \CIBlockElement::GetList(array(),array('IBLOCK_ID' => $arInfo['IBLOCK_ID'], 'PROPERTY_'.$arInfo['SKU_PROPERTY_ID'] => $elementId));
            while ($obOffer = $rsOffers->GetNext())
            {
                $ids[] = $obOffer['ID'];
            }
        }

        $dbBasketItems = \CSaleBasket::GetList([],array(
            "FUSER_ID" => \CSaleBasket::GetBasketUserID(),
            "LID" => $botOptions->getOption('main', 'bot_site_id'),
            "ORDER_ID" => "NULL"
        ),false,false,array('PRODUCT_ID', 'QUANTITY'));

        $cnt = 0;
        while($obBasket = $dbBasketItems->Fetch())
            if(in_array($obBasket['PRODUCT_ID'], $ids))
                $cnt += $obBasket['QUANTITY'];

        if($cnt > 0)
            return " ({$cnt})";
    }

    public static function getExistWishlistItem($elementId = false, $userId = null)
    {
        if($elementId && $userId){
            $item = \Oe\Telegrambot\Entity\WishlistTable::getList([
                'select' => ['ID'],
                'filter' => ['PRODUCT_ID'=>$elementId, 'USER_ID'=>$userId]
            ])->fetchAll();
            if(!empty($item)){
                return GetMessage('IS_EXIST');
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public static function getSortArray($type = 'ELEMENT_LIST')
    {
        if(!$type) return false;

        global $botOptions;
        switch ($type)
        {
            case('ELEMENT_LIST'):
                return [$botOptions->getOption('catalog', 'bot_catalog_sort_element') => $botOptions->getOption('catalog', 'bot_catalog_by_element')];
                break;

            case('SECTION_LIST'):
                return [$botOptions->getOption('catalog', 'bot_catalog_sort_section') => $botOptions->getOption('catalog', 'bot_catalog_by_section')];
                break;

            default:
                return ['SORT' => 'asc'];

        }
    }

    public static function getElementName($obElement = [], $offerId = false)
    {
        if($offerId){
            $offer = \CIBlockElement::GetByID($offerId)->Fetch();
            $obElement["NAME"] = $offer["NAME"];
        }

        if(!$obElement["NAME"]) return false;

        return $obElement["NAME"];
    }

    public static function getElementPhoto($obElement = [], $offerId = false)
    {
        global $botOptions;
        $photoField = $botOptions->getOption('catalog', 'bot_catalog_element_photofields');

        switch ($photoField) {
            case 'DETAIL_PICTURE':
                $picId = $obElement["DETAIL_PICTURE"] ?: $obElement["PREVIEW_PICTURE"];
                if($offerId){
                    $offer = \CIBlockElement::GetByID($offerId)->Fetch();
                    $offerpicId = $offer["DETAIL_PICTURE"] ?: $offer["PREVIEW_PICTURE"];
                    $picId = $offerpicId ?: $picId;
                }
                break;
            case 'PREVIEW_PICTURE':
                $picId = $obElement["PREVIEW_PICTURE"] ?: $obElement["DETAIL_PICTURE"];
                if($offerId){
                    $offer = \CIBlockElement::GetByID($offerId)->Fetch();
                    $offerpicId = $offer["PREVIEW_PICTURE"] ?: $offer["DETAIL_PICTURE"];
                    $picId = $offerpicId ?: $picId;
                }
                break;
            default:
                $picId = false;
                break;
        }

        $link = false;
        if($picId) $link = \CFile::GetPath($picId);

        return $link ? $_SERVER["DOCUMENT_ROOT"].$link : $_SERVER["DOCUMENT_ROOT"].'/bitrix/themes/.default/icons/oe.telegrambot/no_photo.png';
    }

    public static function getElementDescr($obElement = [])
    {
        global $botOptions;

        $descriptionText = '';
        switch ($botOptions->getOption('catalog', 'bot_catalog_element_descr')) {
            case 'DETAIL_TEXT':
                $descriptionText = $obElement['DETAIL_TEXT']?:$obElement['PREVIEW_TEXT'];
                break;
            case 'PREVIEW_TEXT':
                $descriptionText = $obElement['PREVIEW_TEXT']?:$obElement['DETAIL_TEXT'];
                break;
            default:
                $descriptionText =  $obElement['DETAIL_TEXT'];
                break;
        }

        $clearText = strip_tags(html_entity_decode($descriptionText));

        $explodeText = explode(PHP_EOL, $clearText);
        $newExplode = [];
        foreach($explodeText as $text)
            if(trim($text) != '')
                $newExplode[] = trim($text);

        return implode(PHP_EOL . PHP_EOL, $newExplode);
    }

    public static function getElementProps($obElement = [], $arPropCode = [])
    {
        $arProps = $PROPERTY = $display = [];
        foreach($arPropCode as $prop)
        {
            $PROPERTY = $obElement['PROPERTIES'][$prop['CODE']];
            if((is_array($PROPERTY["VALUE"]) && count($PROPERTY["VALUE"])>0) ||
                (!is_array($PROPERTY["VALUE"]) && strlen($PROPERTY["VALUE"])>0))
            {
                $display = \CIBlockFormatProperties::GetDisplayValue($obElement, $PROPERTY);
                if(is_array($display['DISPLAY_VALUE'])){
                	$val = implode(', ', $display['DISPLAY_VALUE']);
                }else{
                	$val = $display['DISPLAY_VALUE'];
                }
                $arProps[] = GetMessage('IS_BOLD', ['#TEXT#' => $PROPERTY['NAME']]). strip_tags($val);
            }

        }

        return !empty($arProps) ? implode("\n", $arProps) : '';
    }

    public static function getElementOfferProps($elementId, $offerId)
    {
        if(!$offerId) return false;
        global $botOptions;

        $rsOffers = \CIBlockElement::GetList(array(),array('ID' => $offerId));
        $skuProps = explode(',', $botOptions->getOption('catalog', 'bot_catalog_iblock_offer_prop'));

        if(count($skuProps) > 0){
            foreach ($skuProps as &$code){
                $code = explode('|', $code);
                $codes[] = $code[0];
            }

            while ($obOffer = $rsOffers->GetNextElement())
            {
                $el = $obOffer->GetFields();
                $el["PROPERTIES"] = $obOffer->GetProperties();
                foreach ($el["PROPERTIES"] as $arProp){
                	if($arProp['PROPERTY_TYPE'] == 'L' || ($arProp['PROPERTY_TYPE'] == 'S' && !empty($arProp['USER_TYPE_SETTINGS']))){
	                    if(in_array($arProp['CODE'], $codes) && strlen($arProp['~VALUE'])>0){
	                        $arProp['VALUE'] = !empty($arProp['USER_TYPE_SETTINGS']) ? self::getValueByXmlId($arProp['USER_TYPE_SETTINGS']['TABLE_NAME'], $arProp['VALUE']) : $arProp['VALUE'];
	                        $arProps[] = GetMessage('IS_BOLD', ['#TEXT#' => $arProp['NAME']]). $arProp['VALUE'];
	                    }
                	}
                }
            }
        }

        return !empty($arProps) ? implode("\n", $arProps) : '';
    }

    public static function getValueByXmlId($tableName, $xmlId)
    {
        global $DB;
        $result = $DB->Query("SELECT distinct `UF_NAME` FROM `$tableName` WHERE `UF_XML_ID` = '$xmlId'")->Fetch();
        return $result['UF_NAME'];
    }

    public static function getElementNav($callBackCaseId = false, $callBackItemId = false, $arNav = [])
    {
        if(!$callBackCaseId || !$callBackItemId || !is_array($arNav)) return false;

        $pageNumber = $arNav['nElNum'];
        $sumPages = $arNav['sumEl'];
        $prevElId = $arNav['IDs'][0];
        $nextElId = isset($arNav['IDs'][2]) ? $arNav['IDs'][2] : $arNav['IDs'][1];
        $nextCallBackData =  Utils::getCallBackStr([
            'case' => $callBackCaseId,
            'id' => $nextElId == $callBackItemId ? $callBackItemId : $nextElId,
            'sumEl' => $sumPages,
            'nElNum' => $arNav['nElNum'] == $sumPages ? $sumPages : $arNav['nElNum'] + 1,
        ]);
        $prevCallBackData = Utils::getCallBackStr([
            'case' => $callBackCaseId,
            'id' => $prevElId == $callBackItemId ? $callBackItemId : $prevElId,
            'sumEl' => $sumPages,
            'nElNum' => $arNav['nElNum'] == 1 ? $arNav['nElNum'] : $arNav['nElNum'] - 1,
        ]);

        return self::_getNavString(
            $prevCallBackData,
            $nextCallBackData,
            $pageNumber,
            $sumPages
        );
    }

    public static function getPageNav($callBackCaseId = false, $callBackItemId = false, $currentPage = 1, $sumPages = false)
    {
        if(!$callBackCaseId || !$currentPage || !$sumPages) return [[[]]];

        $nextNumber = ($sumPages  == $currentPage) ? 1 : $currentPage + 1;
        $prevNumber = ($currentPage == 1) ? $sumPages : $currentPage - 1;

        $prevCallBackData = Utils::getCallBackStr([
            'case' => $callBackCaseId,
            'id' => $callBackItemId,
            'page' => $prevNumber
        ]);
        $nextCallBackData = Utils::getCallBackStr([
            'case' => $callBackCaseId,
            'id' => $callBackItemId,
            'page' => $nextNumber
        ]);

        return self::_getNavString(
            $prevCallBackData,
            $nextCallBackData,
            $currentPage,
            $sumPages
        );
    }

    private static function _getNavString($prevCallBackData, $nextCallBackData, $pageNumber, $sumPages)
    {
        return [[
            [
            	'text' => Emoji::CHARACTER_BLACK_LEFT_POINTING_DOUBLE_TRIANGLE,
                'callback_data' => $prevCallBackData
            ],
            [
                'text' => "{$pageNumber} / {$sumPages}",
                'callback_data' => $nextCallBackData
            ],
            [
            	'text' => Emoji::CHARACTER_BLACK_RIGHT_POINTING_DOUBLE_TRIANGLE,
                'callback_data' => $nextCallBackData
            ],
        ]];
    }


}