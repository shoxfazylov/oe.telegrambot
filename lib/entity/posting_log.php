<?
namespace Oe\Telegrambot\Entity;

use Bitrix\Main\Entity;
use Bitrix\Main\Type;

class PostingLogTable extends Entity\DataManager
{
    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getTableName()
    {
        return 'b_oe_tgbot_posting_log';
    }

    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
            ),
            'POST_ID' => array(
                'data_type' => 'integer'
            ),
            'USER_ID' => array(
                'data_type' => 'integer'
            ),
            'CHAT_ID' => array(
                'data_type' => 'integer'
            ),
            'STATUS' => array(
                'data_type' => 'string',
                'required' => false,
                'default_value' => 'D'
            )
        );
    }

}
