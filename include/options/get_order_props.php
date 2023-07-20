<?
$dbPersonType = CSalePersonType::GetList(["SORT" => "ASC"], ["LID"=>$defSite]);
$personType = [];
$personTypeId = $_REQUEST['bot_order_person_type']?:COption::GetOptionString($mid, 'bot_order_person_type');
while($obPersonType = $dbPersonType->Fetch())
{
    if($personTypeId == NULL)
    {
        COption::SetOptionString($mid, 'bot_order_person_type', $obPersonType['ID']);
        $personTypeId = $obPersonType['ID'];
    }

    $personType[$obPersonType['ID']] = $obPersonType['NAME'];
}

if(count($personType) == 0) $errorList[] = GetMessage('OE_TGBOT_COUNT_ZERO_PERSON_TYPES');

$dbOrdProps = CSaleOrderProps::GetList(["SORT" => "asc"], ["ACTIVE"=>"Y","PERSON_TYPE_ID" => $personTypeId, 'LID' => $defSite, "UTIL" => "N"], false, false, []);

$orderProps[''] = GetMessage('OE_TGBOT_NOT');
$locProps[''] = GetMessage('OE_TGBOT_NOT');
while($obOrdProp = $dbOrdProps->Fetch())
{
    $ordProps[$obOrdProp['ID']] = '[' . $obOrdProp['CODE'] . '] ' .$obOrdProp['NAME'];

    if($obOrdProp['TYPE'] == 'TEXT' || $obOrdProp['TYPE'] == 'TEXTAREA')
        $orderProps[$obOrdProp['CODE']] = '[' . $obOrdProp['CODE'] . '] ' .$obOrdProp['NAME'];
    if($obOrdProp['IS_LOCATION'] == 'Y')
        $locProps[$obOrdProp['CODE']] = '[' . $obOrdProp['CODE'] . '] ' .$obOrdProp['NAME'];

}

if(count($ordProps) == 0) $errorList[] = GetMessage('OE_TGBOT_COUNT_ZERO_ORDER_PROPS');