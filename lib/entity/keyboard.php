<?
namespace Oe\Telegrambot\Entity;

use Bitrix\Main\Entity;
use Bitrix\Main\Type;

class KeyboardTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_oe_tgbot_keyboard';
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
			'KEYBOARD' => array(
				'data_type' => 'string',
				'required' => true
			)
		);
	}

	public static function set($userId = null, $keyboard = [])
	{
        if(!$userId) return false;
		$p = self::getList([
			'select' => ['ID'],
			'filter' => ['USER_ID'=>$userId]
		])->fetch();

		if($p['ID']){
			$keyboard = bin2hex(serialize($keyboard['keyboard']));
			return self::update($p['ID'], ['KEYBOARD'=>$keyboard, 'TIMESTAMP_CHANGE'=>new Type\Datetime]);
		}else{
			$keyboard = bin2hex(serialize($keyboard['keyboard']));
			return self::add(['USER_ID'=>$userId,'KEYBOARD'=>$keyboard]);
		}
	}

	public static function get($userId = NULL)
	{
		$p = self::getList([
				'select' => ['KEYBOARD'],
				'filter' => ['USER_ID'=>$userId]
		])->fetch();
		return unserialize(hex2bin($p['KEYBOARD']));
	}

}
