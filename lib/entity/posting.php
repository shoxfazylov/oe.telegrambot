<?
namespace Oe\Telegrambot\Entity;

use Bitrix\Main\Entity;
use Bitrix\Main\Entity\Validator\Length;
use Bitrix\Main\Type;

class PostingTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
    {
    	return 'b_oe_tgbot_posting';
    }

    public static function getMap()
    {
    	return array(
    			'ID' => array(
    					'data_type' => 'integer',
    					'primary' => true,
    					'autocomplete' => true,
    			),
    			'DATE_INSERT' => array(
    					'data_type' => 'datetime',
    					'default_value' => new Type\Datetime
    			),
    			'DATE_SEND' => array(
    					'data_type' => 'datetime',
    					'required' => false
    			),
    			'USER_ID' => array(
    					'data_type' => 'integer',
    					'required' => true
    			),
    			'COUNT_SEND_ALL' => array(
    					'data_type' => 'integer',
    					'required' => true,
    					'default_value' => 0
    			),
    			'STATUS' => array(
    					'data_type' => 'string',
    					'required' => false,
    					'default_value' => 'D'
    			),
    			'TITLE' => array(
    					'data_type' => 'string',
    					'required' => false
    			),
    			'TEXT' => array(
    					'data_type' => 'text',
    					'required' => false,
    					'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
    					'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
                        'validation' => [__CLASS__, 'validateLength'],
    			),
    			'FILE_ID' => array(
    					'data_type' => 'integer',
    					'required' => false
    			)
    	);
    }

    public static function validateLength()
    {
        return [
            new Length(null, 1024),
        ];
    }

}
