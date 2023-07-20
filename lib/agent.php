<?

namespace Oe\Telegrambot;

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\UserTable;
use \Oe\Telegrambot\Entity\PostingTable;
use \Oe\Telegrambot\Entity\PostingLogTable;
use \Telegram\Bot\Api;
use \Telegram\Bot\Exceptions\TelegramResponseException;

global $botOptions, $telegram;
$botOptions = new \Oe\Telegrambot\Options();
$token = $botOptions->getOption('main', 'bot_token');
if ($token) {
    require $botOptions->getApiPath();
    $telegram = new Api($token);
}

class Agent
{
    const MODULE_ID = 'oe.telegrambot';

    static function set($agentName = '', $interval = 120, $offsetTime = 0, $agentStrSearch = '')
    {
        $agentNameFull = '\\' . __CLASS__ . '::' . $agentName;

        if (strpos($agentNameFull, '(') > 0 && empty($agentStrSearch)) {
            $agentStrSearch = substr($agentNameFull, 0, strpos($agentNameFull, '(')) . '%';
        }

        if ($offsetTime === 0) {
            $offsetTime = $interval;
        }

        $res = \CAgent::GetList(["ID" => "DESC"], ["NAME" => $agentStrSearch]);

        $culture = \Bitrix\Main\Context::getCurrent()->getCulture();
        $phpDateTime = new \DateTime();
        $dateTime = \Bitrix\Main\Type\DateTime::createFromPhp($phpDateTime->modify('+' . ((int)$offsetTime) . ' second'));

        if ($obAgent = $res->GetNext()) {
            \CAgent::Update($obAgent['ID'], [
                'NAME' => $agentNameFull,
                'AGENT_INTERVAL' => $interval
            ]);
        } else {
            \CAgent::AddAgent($agentNameFull, self::MODULE_ID, "N", $interval, $dateTime->toString($culture), "Y", $dateTime->toString($culture), 30);
        }
    }

    static function isEnabledAgent($agentName = '')
    {
        $agentNameFull = '\\' . __CLASS__ . '::' . $agentName . '%';
        $res = \CAgent::GetList(["ID" => "DESC"], ["NAME" => $agentNameFull]);
        if ($obAgent = $res->GetNext()) {
            return true;
        }
        return false;
    }

    static function delete($agentName = '')
    {
        $agentNameFull = '\\' . __CLASS__ . '::' . $agentName . '%';
        $res = \CAgent::GetList(["ID" => "DESC"], ["NAME" => $agentNameFull]);
        if ($obAgent = $res->GetNext()) {
            \CAgent::Delete($obAgent['ID']);
        }
    }

    public static function SendPosting($limit = 10)
    {
        global $botOptions, $telegram;
        $error = [];
        $posting = PostingTable::getlist([
            'select' => ['*'],
            'filter' => ['STATUS' => 'S'],
            'order' => ['ID' => 'ASC'],
            'limit' => 1
        ])->fetch();

        if (!empty($posting)) {
            $updateField = [];
            $offset = Option::get('oe.telegrambot', "bot_posting_agent_offset_" . $posting['ID'], 0);
            $userFilter = array(
                'ACTIVE' => 'Y',
                '!UF_TGBOT_CHAT_ID' => false,
                '!UF_TGBOT_CHAT_ID' => '0'
            );
            $rsUsers = UserTable::getList([
                'filter' => $userFilter,
                'select' => ['ID', 'UF_TGBOT_CHAT_ID'],
                'limit' => $limit,
                'offset' => $offset,
                'order' => ['ID' => 'ASC']
            ]);
            $arUsers = $rsUsers->fetchAll();

            if (!empty($arUsers)) {
                $offset = ($limit + $offset);
                foreach ($arUsers as $arUser) {
                    $isSending = PostingLogTable::getlist([
                        'filter' => [
                            'POST_ID' => $posting['ID'],
                            'USER_ID' => $arUser['ID'],
                            'CHAT_ID' => $arUser["UF_TGBOT_CHAT_ID"],
                        ]
                    ])->fetch();
                    if (!$isSending) {
                        $fields = [
                            'POST_ID' => $posting['ID'],
                            'USER_ID' => $arUser['ID'],
                            'CHAT_ID' => $arUser["UF_TGBOT_CHAT_ID"],
                            'STATUS' => 'D'
                        ];

                        if ($fields['STATUS'] == 'D') {
                            if ($posting['FILE_ID']) {
                                $text = \strip_tags(html_entity_decode($posting['TEXT']), '<b><a>');
                                $file = \CFile::GetByID($posting['FILE_ID'])->Fetch();

                                if(explode('/', $file['CONTENT_TYPE'])[0] == 'image'){
                                    $picture = $_SERVER['DOCUMENT_ROOT'] . \CFile::GetPath($posting['FILE_ID']);
                                    try {
                                        $telegram->sendPhoto([
                                            'chat_id' => $arUser["UF_TGBOT_CHAT_ID"],
                                            'photo' => $picture,
                                            'caption' => $text,
                                            'parse_mode' => 'HTML'
                                        ]);
                                        $fields['STATUS'] = 'S';
                                    } catch (TelegramResponseException $e) {
                                        $error[] = $e->getResponseData();
                                        $fields['STATUS'] = 'F';
                                    }
                                }
                                if(explode('/', $file['CONTENT_TYPE'])[0] == 'video'){
                                    $video = $_SERVER['DOCUMENT_ROOT'] . \CFile::GetPath($posting['FILE_ID']);
                                    try {
                                        $telegram->sendVideo([
                                            'chat_id' => $arUser["UF_TGBOT_CHAT_ID"],
                                            'video' => $video,
                                            'caption' => $text,
                                            'parse_mode' => 'HTML'
                                        ]);
                                        $fields['STATUS'] = 'S';
                                    } catch (TelegramResponseException $e) {
                                        $error[] = $e->getResponseData();
                                        $fields['STATUS'] = 'F';
                                    }
                                }

                            } elseif (!empty($posting['TEXT'])) {
                                $text = \strip_tags(html_entity_decode($posting['TEXT']), '<b><a>');
                                try {
                                    $telegram->sendMessage([
                                        'chat_id' => $arUser["UF_TGBOT_CHAT_ID"],
                                        'text' => $text,
                                        'parse_mode' => 'HTML',
                                        'disable_web_page_preview' => false
                                    ]);
                                    $fields['STATUS'] = 'S';
                                } catch (TelegramResponseException $e) {
                                    $error[] = $e->getResponseData();
                                    $fields['STATUS'] = 'F';
                                }
                            }
                        }
                        //set to log
                        PostingLogTable::add($fields);
                    }
                }
                Option::set('oe.telegrambot', "bot_posting_agent_offset_" . $posting['ID'], $offset);
                $params = [
                    'filter' => [
                        'POST_ID' => $posting['ID'],
                        'STATUS' => 'S',
                    ],
                    'count_total' => true
                ];
                $updateField['COUNT_SEND_ALL'] = PostingLogTable::getlist($params)->getCount();
                PostingTable::update(
                    $posting['ID'],
                    $updateField
                );
                return ['result' => 'procces', 'errors' => $error];
            } else {
                $updateField['STATUS'] = 'Y';
                $updateField['DATE_SEND'] = new \Bitrix\Main\Type\Datetime;
                PostingTable::update(
                    $posting['ID'],
                    $updateField
                );
                return ['result' => 'finish', 'errors' => $error];
            }
        }
    }


}

?>