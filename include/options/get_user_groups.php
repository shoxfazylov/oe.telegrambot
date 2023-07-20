<?
$z = CGroup::GetList(($v1=""), ($v2=""), array("ACTIVE"=>"Y", "ADMIN"=>"N", "ANONYMOUS"=>"N"));
while($zr = $z->Fetch())
{
    $groups[$zr["ID"]] = $zr["NAME"]." [".$zr["ID"]."]";
}

if(count($groups) == 0) $errorList[] = GetMessage('OE_TGBOT_COUNT_ZERO_USER_GROUPS');