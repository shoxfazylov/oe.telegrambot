<?php
namespace Oe\Telegrambot\Entity;

use Bitrix\Main\Application;
use Bitrix\Main\InvalidOperationException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class PostingState
 */
class PostingState
{
	const NEWISH = 'D';
	const SENDING = 'S';
	const PAUSED = 'P';
	const SENT = 'Y';
	const STOPPED = 'X';

	/**
	 * Get current state name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return self::getStateName($code);
	}

	protected static function getStateName($code)
	{
		return Loc::getMessage('SENDER_DISPATCH_STATE1_' . $code) ?: Loc::getMessage('SENDER_DISPATCH_STATE_' . $code);
	}

	/**
	 * Get states.
	 *
	 * @return array
	 */
	public static function getList()
	{
		$class = new \ReflectionClass(__CLASS__);
		$constants = $class->getConstants();

		$list = array();
		foreach ($constants as $id => $value)
		{
			$list[$value] = self::getStateName($value);
		}

		return $list;
	}
}