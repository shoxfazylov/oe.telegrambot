<?
namespace Oe\Telegrambot\Statical;

class Garanty
{
	public function getGarantyMessage()
	{
		global $botOptions;
		$keyboard = [
            [$botOptions->getTitle('GET_ABOUT'), $botOptions->getTitle('GET_GARANTY')],
		    [$botOptions->getTitle('GET_DELIVERY'), $botOptions->getTitle('GET_HELP')],
		    [$botOptions->getTitle('GET_NEWS'), $botOptions->getTitle('GET_CONTACTS')],
		    [$botOptions->getTitle('MAIN')],
        ];
		$msg = '<b>'.$botOptions->getTitle('GET_GARANTY').'</b>'.PHP_EOL.PHP_EOL;
		if(strlen($botOptions->getOption('main', 'bot_garanty'))){
			$msg .= $botOptions->getOption('main', 'bot_garanty');
		}else{
			$msg .= $botOptions->getTitle('TEXT_EMPTY');
		}
		return new \Oe\Telegrambot\Msg($msg, $keyboard);
	}
}