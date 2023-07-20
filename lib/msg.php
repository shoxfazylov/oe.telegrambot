<?
namespace Oe\Telegrambot;

class Msg
{
    protected $text = false;
    protected $keyboard = false;

    public function __construct($text = false, $keyboard = false, $isEncodedText = false)
    {
        if(SITE_CHARSET == 'windows-1251')
        {
            if(!$isEncodedText)
                $text = \Bitrix\Main\Text\Encoding::convertEncoding($text, 'windows-1251', 'UTF-8');

            $keyboard = \Bitrix\Main\Text\Encoding::convertEncoding($keyboard, 'windows-1251', 'UTF-8');
        }

        $this->setText($text);
        $this->setKeyboard($keyboard);
    }

    public function getText()
    {
        return $this->text;
    }

    public function getKeyboard()
    {
        return $this->keyboard;
    }

    public function setText($text)
    {
        return $this->text = $text;
    }

    public function setKeyboard($keyboard)
    {
        return $this->keyboard = $keyboard;
    }

}