<?
if($defSite == NULL)
{
    $dbSites = CSite::GetList($by = 'sort', $ord = 'asc', ['DEFAULT' => 'Y']);
    if($obSite = $dbSites->GetNext())
        $defSite = $obSite['LID'];

    COption::SetOptionString($mid, 'bot_site_id', $defSite);
}

if($defSite == NULL || empty($defSite)) $errorList[] = GetMessage('OE_TGBOT_COUNT_ZERO_SITE');