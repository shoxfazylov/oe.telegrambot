<?
namespace Oe\Telegrambot;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Spatie\Emoji\Emoji;
use \Oe\Telegrambot\Options as Opt;

Loader::includeModule("iblock");

class Feedback
{
    private $opt = false;

    public function __construct()
    {
        global $botOptions;
        $this->opt = $botOptions;
    }

    public function createFeedback($userid = false)
    {
        $element = new \CIBlockElement;
        $PRODUCT_ID = $element->Add([
            "CREATED_BY" => $userid?:1,
            "IBLOCK_ID" => 20,
            "NAME" => date('d-m-Y H:i:s'),
        ]);
        return $PRODUCT_ID;
    }

    public function saveFeedback($feedbackId = false)
    {
        global $botOptions;
        $main = new \Oe\Telegrambot\Main();
        $msg = $main->getMainMessage();
        $keyboard = $msg->getKeyboard();
        return new Msg(GetMessage('SUCCESS'), $keyboard);
    }
    public function getPhoto($userId = false, $feedbackId = false)
    {
        $keyboard = [
            [[
                'text' => $this->opt->getTitle('GET_FEEDBACK_PHOTO'),
                'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
                    'case' => 'feedback',
                    'feedback_id'=>$feedbackId,
                    'type'=>'comment'
                ]),
                'request_photo'=>true,
                'feedback'=>true
            ]]
        ];

        return new Msg(GetMessage('GET_PHOTO'), $keyboard);
    }
    public function setPhoto($value, $userid, $feedbackId)
    {
        $field = [];
        if($value['type'] == 'text'){
            $field['PREVIEW_TEXT'] = $value['data'];
        }
        if($value['type'] == 'file'){
            $field['PREVIEW_PICTURE'] = \CFile::MakeFileArray($value['data']);
        }

        $element = new \CIBlockElement;
        $element->Update($feedbackId, $field);
        return true;
    }

    public function getComment($userId = false, $feedbackId = false)
    {
        $keyboard = [
            [[
                'text' => 'Добавить комментарий',
                'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
                    'case' => 'feedback',
                    'feedback_id'=>$feedbackId,
                    'type'=>'phone'
                ]),
                'request_contact' => true,
                'feedback'=>true,
            ]]
        ];

        return new Msg('Добавить комментарий', $keyboard);
    }

    public function setComment($value, $userid, $feedbackId)
    {

        $feedback = \CIBlockElement::GetByID($feedbackId)->Fetch();
        if(strlen($feedback['PREVIEW_TEXT'])){
            $field['PREVIEW_TEXT'] = $feedback['PREVIEW_TEXT'] . PHP_EOL . $value;
        }else{
            $field['PREVIEW_TEXT'] = $value;
        }

        $element = new \CIBlockElement;
        $element->Update($feedbackId, $field);
        return true;
    }

    public function getContact($userId = false, $feedbackId = false)
    {
        $keyboard = [
            [[
                'text' => $this->opt->getTitle('GET_FEEDBACK_PHONE'),
                'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
                    'case' => 'feedback',
                    'feedback_id'=>$feedbackId,
                    'type'=>'apply'
                ]),
                'request_contact' => true,
                'feedback'=>true,
            ]]
        ];

        return new Msg(GetMessage('GET_PHONE'), $keyboard);
    }

    public function setContact($value, $userid, $feedbackId)
    {
        $element = new \CIBlockElement;
        $element->Update($feedbackId, [
            "NAME" => $value,
        ]);
        return true;
    }
}