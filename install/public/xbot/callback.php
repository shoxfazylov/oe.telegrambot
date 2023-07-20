<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$msg = new \Oe\Telegrambot\Msg();
$callbackStr = explode(';', $iskeyword['callback_data']);
$isInlineKeyboard = false;
foreach($callbackStr as $callBackOption)
{
    $tmp = explode(':', $callBackOption);
    $data[$tmp[0]] = $tmp[1];
}

switch ($data['case'])
{
	case ($botOptions->getCase('getMain')):
		$main = new \Oe\Telegrambot\Main();
        $msg = $main->getMainMessage();
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;
	case ($botOptions->getCase('getSearch')):
		$search = new \Oe\Telegrambot\Search();
		$msg = $search->getSearchButton();
		$keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
		break;
	case ($botOptions->getCase('searchQuery')):
		if($result["message"]["text"] == $botOptions->getTitle('BACK_BUTTON')){
			$main = new \Oe\Telegrambot\Main();
        	$msg = $main->getMainMessage();
		}else{
			$search = new \Oe\Telegrambot\Search();
			$msg = $search->find($result["message"]["text"]);
		}
		$keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
		break;
	case ($botOptions->getCase('signIn')):
		if($result["message"]["text"] == $botOptions->getTitle('BACK_BUTTON')){
			$profile = new \Oe\Telegrambot\Profile();
			$msg = $profile->getProfileMessage($user->getId());
		}else{
			$data['chat_id'] = $result['message']['from']['id'];
			$profile = new \Oe\Telegrambot\Profile();
			$msg = $profile->{$data['function']}($result["message"]["text"], $data);
		}
		$keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
		break;
    /*CATALOG*/
    case ($botOptions->getCase('sectionList')):
        $catalog = new \Oe\Telegrambot\Catalog();
        $msg = $catalog->getSection($data['id'], $data['page']);
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getCase('elementList')):
        $catalog = new \Oe\Telegrambot\Catalog();
        $msg = $catalog->getElementList($data['id'], $data['page']);
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getCase('elementPage')):
        $catalog = new \Oe\Telegrambot\Catalog();
        $msg = $catalog->getElement($data['id'], [
            'nElNum' => $data['nElNum'],
            'sumEl' => $data['sumEl'],
            'offerid' => $data['offerid'],
            'uid'=>$user->getId()
        ]);
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getCase('viewOfferProp')):
        $catalog = new \Oe\Telegrambot\Catalog();
        $msg = $catalog->getOfferProp($data['id'], $data['offerid'], $data['propid']);
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    /*BASKET*/
    case ($botOptions->getCase('getBasketItem')):
        $basket = new \Oe\Telegrambot\Basket();
        $msg = $basket->getBasket($data['id'], [
            'nElNum' => $data['nElNum'],
            'sumEl' => $data['sumEl']
        ]);
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;
    case ($botOptions->getCase('addBasket')):
        $basket = new \Oe\Telegrambot\Basket();
        $basket->addBasket($data['id'], $data['measure'], $data['offerid']);
        $catalog = new \Oe\Telegrambot\Catalog();
        $msg = $catalog->getElement($data['id'], [
            'nElNum' => $data['nElNum'],
            'sumEl' => $data['sumEl'],
            'offerid'=>$data['offerid'],
            'uid'=>$user->getId()
        ]);
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getCase('addToBasket')):
        $basket = new \Oe\Telegrambot\Basket();
        $msg = $basket->addToBasket($data['id'], $data['measure'], $data['offerid']);
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getCase('delBasket')):
        $basket = new \Oe\Telegrambot\Basket();
        $basket->delBasket($data['id']);
        $msg = $basket->getBasket();
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getCase('clearBasket')):
        $basket = new \Oe\Telegrambot\Basket();
        $msg = $basket->clearBasket();
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    /*WISHLIST*/
    case ($botOptions->getCase('addToWishlist')):
        $wishlist = new \Oe\Telegrambot\Entity\WishlistTable();
        $wishlist->addToWishlist($data['id'], $user->getId());
        $catalog = new \Oe\Telegrambot\Catalog();
        $msg = $catalog->getElement($data['id'], [
            'nElNum' => $data['nElNum'],
            'sumEl' => $data['sumEl'],
            'offerid'=>$data['offerid'],
            'uid'=>$user->getId()
        ]);
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getCase('delWishlist')):
        $wishlist = new \Oe\Telegrambot\Entity\WishlistTable();
        $msg = $wishlist->delWishlist($user->getId());
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    /*ORDER*/
    case ($botOptions->getCase('createOrder')):

        $order = new \Oe\Telegrambot\Order();
        switch ($data['type']) {
            case 'phone':
            	$msg = $order->createOrder($user->getId());
                //$msg = $order->getContact($orderId);
                $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
                break;

            case 'location':
                if (!empty($result["message"]["contact"])) {
                    $phone = $result["message"]["contact"]['phone_number'];
                }else{
                    $phone = $result["message"]["text"];
                }
                $order->setContact($phone, $data['order_id']);
                $msg = $order->getLocation($data['order_id']);
                $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
                break;

            case 'paysystem':
                if (!empty($result["message"]["location"])) {
                    $location = $result["message"]["location"];
                }else{
                    $location = $result["message"]["text"];
                }
                $order->setLocation($location, $data['order_id']);
                $msg = $order->getPaysystem($data['order_id']);
                $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
                break;

            case 'delivery':
                $order->setPaysystem($data['paysytem_id'], $data['order_id']);
                $msg = $order->getDelivery($data['order_id']);
                $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
                break;

            case 'checkorder':
                $order->setDelivery($data['delivery_id'], $data['order_id']);
                $msg = $order->checkOrder($data['order_id']);
                $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
                break;

            case 'cancel':
                $msg = $order->cancelOrder($data['order_id']);
                $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
                break;

            case 'cancelconfirm':
                $msg = $order->cancelConfirmOrder($result["message"]["text"], $data['order_id']);
                $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
                break;

            case 'apply':
                $msg = $order->saveOrder($data['order_id']);
                $keyboard = $telegram->replyKeyboardMarkup(['inline_keyboard' => $msg->getKeyboard(), 'one_time_keyboard' => false]);
                $isInlineKeyboard = true;
                break;
        }

        break;

    /*ORDERLIST*/
    case ($botOptions->getCase('getOrderStatusList')):
        $order = new \Oe\Telegrambot\Order();
        $msg = $order->getOrderStatusList();
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getCase('getOrderList')):
        $order = new \Oe\Telegrambot\Order();
        $msg = $order->getOrderList($data['id']);
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;

    case ($botOptions->getCase('getOrder')):
        $order = new \Oe\Telegrambot\Order();
        $msg = $order->getOrder($data['id']);
        $keyboard = $telegram->replyKeyboardMarkup(['inline_keyboard' => $msg->getKeyboard(), 'one_time_keyboard' => false]);
        $isInlineKeyboard = true;
        break;

    case ($botOptions->getCase('getProfile')):
        $main = new \Oe\Telegrambot\Profile();
        $msg = $main->getProfileMessage($user->getId());
        $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
        break;
}

$text = $msg->getText();
$keyboard = $keyboard ? $keyboard : $telegram->replyKeyboardMarkup(['keyboard' => $botOptions->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);
if(!$isInlineKeyboard){
    \Oe\Telegrambot\Entity\KeyboardTable::set($user->getId(), $keyboard);
}
if(!empty($text)){
    if(isset($text['photo'])){
        $telegram->sendPhoto([
            'chat_id' => $result["message"]["chat"]["id"],
            'photo' => $text['photo'],
            'caption' => $text['caption'],
            'reply_markup' => $keyboard,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => false
        ]);
    }else{
        $telegram->sendMessage([
            'chat_id' => $result["message"]["chat"]["id"],
            'text' => $text,
            'reply_markup' => $keyboard,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => false
        ]);
    }

}
if($isInlineKeyboard && $data['type'] == 'apply'){
    $main = new \Oe\Telegrambot\Main();
    $msg = $main->getMainMessage();
    $keyboard = $telegram->replyKeyboardMarkup(['keyboard' => $msg->getKeyboard(),'resize_keyboard' => true,'one_time_keyboard' => false]);

    $telegram->sendMessage([
        'chat_id' => $result["message"]["chat"]["id"],
        'text' => $msg->getText(),
        'reply_markup' => $keyboard,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => false
    ]);
}
