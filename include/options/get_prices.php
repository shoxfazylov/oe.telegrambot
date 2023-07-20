<?
$prices = [];
$rsPrices = CCatalogGroup::GetList();
while($arPrice = $rsPrices->Fetch())
{
    $prices[$arPrice['ID']] = '[' . $arPrice['XML_ID'] . '] ' . $arPrice['NAME_LANG'];
}
if(count($prices) == 0) $errorList[] = GetMessage('OE_TGBOT_COUNT_ZERO_CURRS');