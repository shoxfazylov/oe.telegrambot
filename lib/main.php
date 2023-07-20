<?
namespace Oe\Telegrambot;

class Main
{
    public function getMainMessage()
    {
        global $botOptions;
        $keyboard = [
			[
                [
                    'text' => $botOptions->getTitle('GET_SEARCH'),
                    'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
                        'case' => $botOptions->getCase('getSearch')
                    ])
			    ],
                [
                    'text' => $botOptions->getTitle('GET_FEEDBACK'),
                    'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
                        'case' => $botOptions->getCase('feedback'),
                        'type' => 'photo'
                    ])
                ]
            ],
            [$botOptions->getTitle('GET_CATALOG'), $botOptions->getTitle('GET_CART')],
            [$botOptions->getTitle('PROFILE'), $botOptions->getTitle('GET_ABOUT')],
        ];

        return new \Oe\Telegrambot\Msg($botOptions->getTitle('GET_ENTER_ACTION'), $keyboard);
    }
}