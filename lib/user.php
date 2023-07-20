<?
namespace Oe\Telegrambot;

class User
{
    protected $id, $login;

    public function __construct($chatUser = [])
    {
        global $USER;
        global $botOptions;
        $arUser = self::getUserbyChatId($chatUser['id']);
        if($arUser){
            if(!$USER->IsAuthorized()) $USER->Authorize($arUser['ID'], true);
            $this->id = $arUser['ID'];
            $this->login = $arUser['LOGIN'];
        }else{
            $pass = self::getPass();
            $arFields = [
                "TITLE"             => strlen($chatUser['username'])>0 ? '@'.$chatUser['username'] : '',
                "NAME"              => $chatUser['first_name'],
                "LAST_NAME"         => $chatUser['last_name'],
                "EMAIL"             => $chatUser['id'] . "@t.me",
                "LOGIN"             => $chatUser['id'] . "@t.me",
                "ACTIVE"            => "Y",
                "GROUP_ID"          => explode(',', $botOptions->getOption('main', 'bot_user_group')),
                "PASSWORD"          => $pass,
                "CONFIRM_PASSWORD"  => $pass,
            ];

            $ID = $USER->Add($arFields);
            if (intval($ID) > 0){
                $USER->Authorize($ID);
                self::updateChatId($chatUser['id'], $ID);
                Utils::setUserField('UF_TGBOT_HIDDEN_PASS', bin2hex($pass), $ID);
                $this->id = $ID;
                $this->login = $arFields['LOGIN'];
            }else{
                return false;
            }
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getUserbyChatId($chatId = NULL)
    {
    	if(!$chatId) return false;

    	$rsUser = \CUser::GetList(($by="ID"), ($order="ASC"),
            array(
                'UF_TGBOT_CHAT_ID' => $chatId
            )
        );
    	if($arUser = $rsUser->Fetch()) return $arUser;

    	return false;
    }

    public static function getPass($number = 10)
    {
        $arr = ['a','b','c','d','e','f',
            'g','h','i','j','k','l',
            'm','n','o','p','r','s',
            't','u','v','x','y','z',
            'A','B','C','D','E','F',
            'G','H','I','J','K','L',
            'M','N','O','P','R','S',
            'T','U','V','X','Y','Z',
            '1','2','3','4','5','6',
            '7','8','9','0','(',')',
        	'!','?','&','%','@','$'
        ];

        $pass = "";
        for($i = 0; $i < $number; $i++)
        {
            $index = rand(0, count($arr) - 1);
            $pass .= $arr[$index];
        }
        return $pass;
    }

    public function updateChatId($chatId, $userId)
    {
        Utils::setUserField('UF_TGBOT_CHAT_ID', $chatId, $userId);
    }
}