<?
require __DIR__ . '/botapi/vendor/autoload.php';
use Telegram\Bot\Api;

$token = trim($_REQUEST['bot_token']?: COption::GetOptionString($mid, 'bot_token'));
if($token)
{
    $telegram = new Api($token);
    //$response = $telegram->removeWebhook();
    $webHookInfo = GetMessage('OE_TGBOT_TOKEN_IS_EMPTY');

    $siteUrl = $_REQUEST['bot_siteurl']?:COption::GetOptionString($mid, 'bot_siteurl');
    $webhookUrl = '/xbot/hook.php';//$_REQUEST['bot_webhook_url']?:COption::GetOptionString($mid, 'bot_webhook_url');
    $maxConnections = $_REQUEST['bot_max_users']?:COption::GetOptionString($mid, 'bot_max_users');
    if($siteUrl && $webhookUrl)
    {
        $webhook = "{$siteUrl}{$webhookUrl}?token={$token}";
        try {
            $response = $telegram->setWebhook(['url' => $webhook, 'max_connections' => $maxConnections?:40]);
            $webHookInfo = GetMessage('OE_TGBOT_TOKEN_IS_SET', ['#URL#' => $webhook]);
        } catch (Exception $e) {
            $webHookInfo = GetMessage('OE_TGBOT_TOKEN_IS_EMPTY');
        }

    }

}
else
{
    $webHookInfo = GetMessage('OE_TGBOT_TOKEN_IS_EMPTY');
}