<?
$dbIblocks = CCatalog::GetList([],['SITE_ID' => $defSite, 'ACTIVE'=>'Y']);
while($obIblock = $dbIblocks->Fetch())
{
    $iblocks[$obIblock['ID']] = '[' . $obIblock['ID'] . '] ' .$obIblock['NAME'];
}

if(count($iblocks) == 0) $errorList[] = GetMessage('OE_TGBOT_COUNT_ZERO_IBLOCKS');