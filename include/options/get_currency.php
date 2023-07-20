<?
$currDb = CCurrency::GetList(($by="name"), ($order="asc"), 'ru');
while($obCurr = $currDb->Fetch())
{
    $currencies[$obCurr['CURRENCY']] = '[' . $obCurr['CURRENCY'] . '] ' . $obCurr['FULL_NAME'];
}
if(count($currencies) == 0) $errorList[] = GetMessage('OE_TGBOT_COUNT_ZERO_CURRS');