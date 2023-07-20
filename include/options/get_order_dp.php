<?
$arDelivery = \Bitrix\Sale\Delivery\Services\Manager::getActive();

foreach($arDelivery as $key=>$value)
{
    if(empty($value['PARENT_ID']))
        $delivery[$value["ID"]] = $value["NAME"];
}

if(count($delivery) == 0) $errorList[] = GetMessage('OE_TGBOT_COUNT_ZERO_DELIVERY');

$dbPaysystem = CSalePaySystem::GetList($arOrder = array("SORT"=>"ASC", "PSA_NAME"=>"ASC"), array("LID" => $defSite, "ACTIVE"=>"Y", "PERSON_TYPE_ID"=> $_REQUEST['bot_order_person_type']?:COption::GetOptionString($mid, 'bot_order_person_type')));

while($obPaysystem = $dbPaysystem->Fetch())
{
    $paysystem[$obPaysystem['ID']] = $obPaysystem['NAME'];
}