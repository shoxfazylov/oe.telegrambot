<?
define("ADMIN_MODULE_NAME", "oe.telegrambot");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;


if(!Loader::includeModule("oe.telegrambot"))
{
	ShowError(Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));
}

$APPLICATION->SetAdditionalCSS('/bitrix/css/main/grid/webform-button.css');

$senderPathPrefix = '/bitrix/admin/oe_telegrambot_';
$senderAdminPaths = [
	'LETTER_LIST' => $senderPathPrefix . 'posting.php?lang=' . LANGUAGE_ID,
	'LETTER_EDIT' => $senderPathPrefix . 'posting.php?edit&ID=#id#&lang=' . LANGUAGE_ID,
	'LETTER_ADD' => $senderPathPrefix . 'posting.php?edit&ID=0&lang=' . LANGUAGE_ID,
];

?>
<script type="text/javascript">
	if (BX('adm-workarea'))
	{
		BX.removeClass(BX('adm-workarea'), 'adm-workarea');
		BX.addClass(BX('adm-workarea'), 'adm-workarea-sender');
	}
	if (!BX.message.SITE_ID)
	{
		BX.message['SITE_ID'] = '';
	}
</script>
