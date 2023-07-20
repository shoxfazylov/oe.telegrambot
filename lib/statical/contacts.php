<?
namespace Oe\Telegrambot\Statical;

class Contacts
{
	public function getContactsMessage()
	{
		global $botOptions;
		$keyboard = [
            [$botOptions->getTitle('GET_ABOUT'), $botOptions->getTitle('GET_GARANTY')],
		    [$botOptions->getTitle('GET_DELIVERY'), $botOptions->getTitle('GET_HELP')],
		    [$botOptions->getTitle('GET_NEWS'), $botOptions->getTitle('GET_CONTACTS')],
		    [$botOptions->getTitle('MAIN')],
        ];
		$msg = '<b>'.$botOptions->getTitle('GET_CONTACTS').'</b>'.PHP_EOL.PHP_EOL;
		if(strlen($botOptions->getOption('main', 'bot_contacts'))){
			$msg .= $botOptions->getOption('main', 'bot_contacts');
		}else{
			$msg .= $botOptions->getTitle('TEXT_EMPTY');
		}
		return new \Oe\Telegrambot\Msg($msg, $keyboard);
	}
}