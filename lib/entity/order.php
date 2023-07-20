<?
namespace Oe\Telegrambot\Entity;

use Bitrix\Main\Entity;
use Bitrix\Main\Type;

class OrderTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
    {
    	return 'b_oe_tgbot_order';
    }

    public static function getMap()
    {
    	return array(
    			'ID' => array(
    					'data_type' => 'integer',
    					'primary' => true,
    					'autocomplete' => true,
    			),
    			'TIMESTAMP_CHANGE' => array(
    					'data_type' => 'datetime',
    					'default_value' => new Type\Datetime
    			),
    			'USER_ID' => array(
    					'data_type' => 'integer',
    					'required' => true
    			),
    			'ORDER_ID' => array(
    					'data_type' => 'integer',
    					'required' => false
    			),
    			'PRODUCTS' => array(
    					'data_type' => 'string',
    					'required' => false
    			),
    			'PHONE' => array(
    					'data_type' => 'string',
    					'required' => false
    			),
    			'LOCATION' => array(
    					'data_type' => 'string',
    					'required' => false
    			),
    			'PAYSYSTEM_ID' => array(
    					'data_type' => 'integer',
    					'required' => false
    			),
    			'DELIVERY_ID' => array(
    					'data_type' => 'integer',
    					'required' => false
    			),
    	);
    }

}
