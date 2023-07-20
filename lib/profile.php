<?
namespace Oe\Telegrambot;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Profile
{
    public function getProfileMessage($userId)
    {
        global $botOptions;
        $keyboard = [
            [$botOptions->getTitle('GET_WISHLIST'), $botOptions->getTitle('GET_ORDERS')]
        ];

        if(!self::isAuth($userId)){
        	$keyboard = array_merge(
				$keyboard,
				[
					[$botOptions->getTitle('GET_AUTH'),$botOptions->getTitle('GET_REGISTER')],
					[$botOptions->getTitle('MAIN')]
				]
        	);
        }else{
        	$keyboard = array_merge(
				$keyboard,
				[
					[$botOptions->getTitle('GET_PERSONAL')],
					[$botOptions->getTitle('MAIN')]
				]
        	);
        }

        return new Msg($botOptions->getTitle('GET_ENTER_ACTION'), $keyboard);
    }

    public function getProfileData($userId, $register = false)
    {
    	global $botOptions;
    	$keyboard = [
    		[$botOptions->getTitle('GET_WISHLIST'), $botOptions->getTitle('GET_ORDERS')],
    		[$botOptions->getTitle('GET_PERSONAL')],
    		[$botOptions->getTitle('MAIN')]
    	];

    	$filter = ["ID" => $userId];
    	$select = ["SELECT"=>["ID", "LOGIN", "NAME", "LAST_NAME", "PASSWORD","UF_*"]];
    	$arUser = \CUser::GetList(($by="personal_country"), ($order="desc"), $filter, $select)->Fetch();
    	$pass = hex2bin($arUser['UF_TGBOT_HIDDEN_PASS']);

    	if($register){
    		Utils::setUserField('UF_TGBOT_ISAUTH', 'Y', $userId);
    		$msg = Loc::getMessage('USER_REGISTER_TEXT');
    		$msg .= Loc::getMessage('USER_ID').$arUser['ID'].PHP_EOL;
    		$msg .= Loc::getMessage('USER_FIO').$arUser['NAME'].' '.$arUser['LAST_NAME'].PHP_EOL;
    		$msg .= Loc::getMessage('USER_LOGIN').$arUser['LOGIN'].PHP_EOL;
    		$msg .= Loc::getMessage('USER_PASSWORD').$pass;
    	}else{
    		$msg = Loc::getMessage('USER_PROFILE_TEXT');
    		$msg .= Loc::getMessage('USER_ID').$arUser['ID'].PHP_EOL;
    		$msg .= Loc::getMessage('USER_FIO').$arUser['NAME'].' '.$arUser['LAST_NAME'].PHP_EOL;
    		$msg .= Loc::getMessage('USER_LOGIN').$arUser['LOGIN'].PHP_EOL;
    		$msg .= Loc::getMessage('USER_PASSWORD').$pass;
    	}

    	return new Msg($msg, $keyboard);
    }

    public function signInOne($userId)
    {
    	global $botOptions;
    	if(!$userId) return false;
    	$keyboard = [
			[[
				'text' => $botOptions->getTitle('BACK_BUTTON'),
				'auth'=>true,
				'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
					'case' => $botOptions->getCase('signIn'),
					'function'=> 'signInTwo',
					'uid' => $userId
				])
			]]
    	];
    	return new Msg(Loc::getMessage('USER_LOGIN_TEXT'), $keyboard);
    }

    public function signInTwo($login, $data)
    {
    	global $botOptions;
    	$arUser = \CUser::GetByLogin($login)->Fetch();
    	if(!empty($arUser)){
    		$msg = Loc::getMessage('USER_PASSWORD_TEXT');
	    	$keyboard = [
				[[
					'text' => $botOptions->getTitle('BACK_BUTTON'),
					'auth'=>true,
					'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
						'case' => $botOptions->getCase('signIn'),
						'function'=> 'signInThree',
						'login' => $login,
						'uid' => $data['uid']
					])
				]]
	    	];
    	}else{
    		$msg = Loc::getMessage('USER_LOGIN_ERROR');
    		$keyboard = [
				[[
					'text' => $botOptions->getTitle('BACK_BUTTON'),
					'auth'=>true,
					'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
						'case' => $botOptions->getCase('signIn'),
						'function'=> 'signInTwo',
						'uid' => $data['uid']
					])
				]]
    		];
    	}
    	return new Msg($msg, $keyboard);
    }

    public function signInThree($password, $data)
    {
    	global $botOptions, $USER;
    	if (!is_object($USER)) $USER = new \CUser;
    	$arAuthResult = $USER->Login($data['login'], $password);
    	if($arAuthResult['TYPE'] == 'ERROR'){
    		$msg = Loc::getMessage('USER_PASSWORD_ERROR');
    		$keyboard = [
				[[
					'text' => $botOptions->getTitle('BACK_BUTTON'),
					'auth'=>true,
					'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
						'case' => $botOptions->getCase('signIn'),
						'function'=> 'signInThree',
						'login' => $data['login'],
						'uid' => $data['uid']
					])
				]]
    		];
    	}else{
    		Utils::setUserField('UF_TGBOT_CHAT_ID', $data['chat_id'], $USER->GetID());
    		Utils::setUserField('UF_TGBOT_ISAUTH', 'Y', $USER->GetID());
    		Utils::setUserField('UF_TGBOT_HIDDEN_PASS', bin2hex($password), $USER->GetID());

    		$USER->Update($data['uid'], [
    			'UF_TGBOT_ISAUTH' => 0,
    			'UF_TGBOT_CHAT_ID' => 0,
    			'UF_TGBOT_HIDDEN_PASS' => 0
    		]);

    		$msg = Loc::getMessage('USER_USER_AUTH_SUCCESS');
    		$keyboard = [
				[$botOptions->getTitle('GET_WISHLIST'), $botOptions->getTitle('GET_ORDERS')],
    			[$botOptions->getTitle('GET_PERSONAL')],
    			[$botOptions->getTitle('MAIN')]
    		];
    	}

    	return new Msg($msg, $keyboard);
    }

    private function isAuth($userId)
    {
    	if(!$userId) return false;
    	$filter = ["ID" => $userId];
    	$select = ["SELECT"=>["ID","UF_*"]];
    	$arUser = \CUser::GetList(($by="personal_country"), ($order="desc"), $filter, $select)->Fetch();
    	return $arUser['UF_TGBOT_ISAUTH'];
    }


}