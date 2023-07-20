<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$msg = new \Oe\Telegrambot\Msg($botOptions->getTitle('GET_START_MSG'));

switch($result["message"]["text"])
{
    case ($botOptions->getAction('getCatalog')):
        $catalog = new \Oe\Telegrambot\Catalog();
        $msg = $catalog->getSection();
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getAction('getCart')):
        $basket = new \Oe\Telegrambot\Basket();
        $msg = $basket->getBasket(false, false, $user->getId());
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getAction('getWishlist')):
        $wishlist = new \Oe\Telegrambot\Entity\WishlistTable();
        $msg = $wishlist->getWishlistList($user->getId());
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getAction('getOrders')):
        $order = new \Oe\Telegrambot\Order();
        $msg = $order->getOrderStatusList();
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getAction('getMain')):
        $main = new \Oe\Telegrambot\Main();
        $msg = $main->getMainMessage();
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getAction('getProfile')):
        $main = new \Oe\Telegrambot\Profile();
        $msg = $main->getProfileMessage($user->getId());
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getTitle('GET_REGISTER')):
    	$main = new \Oe\Telegrambot\Profile();
    	$msg = $main->getProfileData($user->getId(), true);
    	$keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
    	break;

    case ($botOptions->getTitle('GET_PERSONAL')):
    	$main = new \Oe\Telegrambot\Profile();
    	$msg = $main->getProfileData($user->getId(), false);
    	$keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
    	break;

    case ($botOptions->getTitle('GET_AUTH')):
    	$main = new \Oe\Telegrambot\Profile();
    	$msg = $main->signInOne($user->getId());
    	$keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
    	break;

    case ($botOptions->getAction('getAbout')):
        $about = new \Oe\Telegrambot\Statical\About();
        $msg = $about->getAboutMessage();
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getAction('getNews')):
        $news = new \Oe\Telegrambot\Statical\News();
        $msg = $news->getNewsMessage();
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getAction('getHelp')):
        $help = new \Oe\Telegrambot\Statical\Help();
        $msg = $help->getHelpMessage();
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getAction('getGaranty')):
        $garanty = new \Oe\Telegrambot\Statical\Garanty();
        $msg = $garanty->getGarantyMessage();
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getAction('getDelivery')):
        $delivery = new \Oe\Telegrambot\Statical\Delivery();
        $msg = $delivery->getDeliveryMessage();
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getAction('getContacts')):
        $contacts = new \Oe\Telegrambot\Statical\Contacts();
        $msg = $contacts->getContactsMessage();
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;
}

$text = $msg->getText();
$keyboard = $keyboard ? $keyboard : $telegram->replyKeyboardMarkup(['keyboard' => $botOptions->getKeyboard(), 'resize_keyboard' => true,'one_time_keyboard' => false]);
\Oe\Telegrambot\Entity\KeyboardTable::set($user->getId(), $keyboard);
if(!empty($text))
    $telegram->sendMessage([
        'chat_id' => $result["message"]["chat"]["id"],
        'text' => $text,
        'reply_markup' => $keyboard,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => false
    ]);