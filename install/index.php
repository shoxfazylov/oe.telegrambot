<?
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\ModuleManager;
Loc::loadMessages(__FILE__);

Class oe_telegrambot extends CModule
{
    const MODULE_ID = 'oe.telegrambot';
    var $MODULE_ID = 'oe.telegrambot';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $strError = '';

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__)."/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("oe.telegrambot_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("oe.telegrambot_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("oe.telegrambot_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("oe.telegrambot_PARTNER_URI");
    }

    function InstallDB()
    {
        global $DB, $APPLICATION;
        $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/db/'.strtolower($DB->type).'/install.sql');
        return true;
    }

    function UnInstallDB()
    {
        global $DB, $APPLICATION;
        $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/db/'.strtolower($DB->type).'/uninstall.sql');
        return true;
    }

    function InstallEvents()
    {
        RegisterModuleDependences('main', 'OnBuildGlobalMenu', $this->MODULE_ID, 'OeTgBotEvents', 'OnBuildGlobalMenuHandler');
        if(class_exists('\Bitrix\Main\EventManager')){
        	$eventManager = \Bitrix\Main\EventManager::getInstance();
        	$eventManager->registerEventHandler('sale', 'OnSaleOrderSaved', $this->MODULE_ID, 'OeTgBotEvents', 'OnSaleOrderSaved');
        }
        return true;
    }

    function UnInstallEvents()
    {
        UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', $this->MODULE_ID, 'OeTgBotEvents', 'OnBuildGlobalMenuHandler');
        UnRegisterModuleDependences('sale', 'OnSaleOrderSaved', $this->MODULE_ID, 'OeTgBotEvents', 'OnSaleOrderSaved');

        return true;
    }

    function InstallUserFields()
    {
        $this->addChatField();
        $this->addIsAuth();
        $this->addHiddenPass();
        return true;
    }

    function InstallFiles($arParams = array())
    {
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/admin'))
        {
            if ($dir = opendir($p))
            {
                while (false !== $item = readdir($dir))
                {
                    if ($item == '..' || $item == '.' || $item == 'menu.php')
                        continue;
                    file_put_contents($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$item,
                                      '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.$this->MODULE_ID.'/admin/'.$item.'");?'.'>');
                }
                closedir($dir);
            }
        }

        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/components'))
        {
            if ($dir = opendir($p))
            {
                while (false !== $item = readdir($dir))
                {
                    if ($item == '..' || $item == '.')
                        continue;
                    CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.$item, $ReWrite = True, $Recursive = True);
                }
                closedir($dir);
            }
        }

        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/themes/",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/",
            true,
            true
        );
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/public/xbot",
            $_SERVER["DOCUMENT_ROOT"]."/xbot",
            true,
            true
        );

        return true;
    }

    function UnInstallFiles()
    {
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/admin'))
        {
            if ($dir = opendir($p))
            {
                while (false !== $item = readdir($dir))
                {
                    if ($item == '..' || $item == '.')
                        continue;
                    unlink($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$item);
                }
                closedir($dir);
            }
        }
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/components'))
        {
            if ($dir = opendir($p))
            {
                while (false !== $item = readdir($dir))
                {
                    if ($item == '..' || $item == '.' || !is_dir($p0 = $p.'/'.$item))
                        continue;

                    $dir0 = opendir($p0);
                    while (false !== $item0 = readdir($dir0))
                    {
                        if ($item0 == '..' || $item0 == '.')
                            continue;
                        DeleteDirFilesEx('/bitrix/components/'.$item.'/'.$item0);
                    }
                    closedir($dir0);
                }
                closedir($dir);
            }
        }

        DeleteDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/themes/.default/icons/".$this->MODULE_ID,
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default/icons/".$this->MODULE_ID
        );

        DeleteDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/public/xbot",
            $_SERVER["DOCUMENT_ROOT"]."/xbot"
        );

        return true;
    }

    function DoInstall()
    {
        global $APPLICATION;
        $this->InstallFiles();
        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallUserFields();
        ModuleManager::registerModule($this->MODULE_ID);
    }

    function DoUninstall()
    {
        global $APPLICATION;
        ModuleManager::unRegisterModule($this->MODULE_ID);
        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();
    }


    function addChatField()
    {
    	$rsData = \CUserTypeEntity::GetList(array($by=>$order), array('ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_TGBOT_CHAT_ID'));
    	if(!$rsData->GetNext())
    	{
    		$oUserTypeEntity = new \CUserTypeEntity();
    		$nameField = ['ru' => 'UF_TGBOT_CHAT_ID', 'en' => 'UF_TGBOT_CHAT_ID'];
    		$aUserFields = [
    				'ENTITY_ID' => 'USER',
    				'FIELD_NAME' => 'UF_TGBOT_CHAT_ID',
    				'USER_TYPE_ID' => 'string',
    				'MULTIPLE' => 'N',
    				'XML_ID' => 'UF_TGBOT_CHAT_ID',
    				'SORT' => 500,
    				'MANDATORY' => 'N',
    				'SHOW_FILTER' => 'N',
    				'SHOW_IN_LIST' => '',
    				'EDIT_IN_LIST' => 'N',
    				'IS_SEARCHABLE' => 'N',
    				'EDIT_FORM_LABEL'   => $nameField,
    				'LIST_COLUMN_LABEL' => $nameField,
    				'LIST_FILTER_LABEL' => $nameField
    		];
    		$iUserFieldId = $oUserTypeEntity->Add($aUserFields);
    	}
    }

    function addIsAuth()
    {
    	$rsData = \CUserTypeEntity::GetList(array($by=>$order), array('ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_TGBOT_ISAUTH'));
    	if(!$rsData->GetNext())
    	{
    		$oUserTypeEntity = new \CUserTypeEntity();
    		$nameField = ['ru' => 'UF_TGBOT_ISAUTH', 'en' => 'UF_TGBOT_ISAUTH'];
    		$aUserFields = [
    				'ENTITY_ID' => 'USER',
    				'FIELD_NAME' => 'UF_TGBOT_ISAUTH',
    				'USER_TYPE_ID' => 'boolean',
    				'MULTIPLE' => 'N',
    				'XML_ID' => 'UF_TGBOT_ISAUTH',
    				'SORT' => 500,
    				'MANDATORY' => 'N',
    				'SHOW_FILTER' => 'N',
    				'SHOW_IN_LIST' => 'N',
    				'EDIT_IN_LIST' => 'N',
    				'IS_SEARCHABLE' => 'N',
    				'EDIT_FORM_LABEL'   => $nameField,
    				'LIST_COLUMN_LABEL' => $nameField,
    				'LIST_FILTER_LABEL' => $nameField
    		];

    		$iUserFieldId = $oUserTypeEntity->Add($aUserFields);
    	}
    }

    function addHiddenPass()
    {
    	$rsData = \CUserTypeEntity::GetList(array($by=>$order), array('ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_TGBOT_HIDDEN_PASS'));
    	if(!$rsData->GetNext())
    	{
    		$oUserTypeEntity = new \CUserTypeEntity();
    		$nameField = ['ru' => 'UF_TGBOT_HIDDEN_PASS', 'en' => 'UF_TGBOT_HIDDEN_PASS'];
    		$aUserFields = [
    				'ENTITY_ID' => 'USER',
    				'FIELD_NAME' => 'UF_TGBOT_HIDDEN_PASS',
    				'USER_TYPE_ID' => 'string',
    				'MULTIPLE' => 'N',
    				'XML_ID' => 'UF_TGBOT_HIDDEN_PASS',
    				'SORT' => 500,
    				'MANDATORY' => 'N',
    				'SHOW_FILTER' => 'N',
    				'SHOW_IN_LIST' => 'N',
    				'EDIT_IN_LIST' => 'N',
    				'IS_SEARCHABLE' => 'N',
    				'EDIT_FORM_LABEL'   => $nameField,
    				'LIST_COLUMN_LABEL' => $nameField,
    				'LIST_FILTER_LABEL' => $nameField
    		];

    		$iUserFieldId = $oUserTypeEntity->Add($aUserFields);
    	}
    }
}
?>
