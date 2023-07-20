<?
namespace Oe\Telegrambot;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Spatie\Emoji\Emoji;
use \Oe\Telegrambot\Options as Opt;

Loader::includeModule("catalog");
Loader::includeModule("sale");
Loader::includeModule("iblock");

class Basket
{
    private $opt = false;
    private $currency = 'RUB';
    public function __construct()
    {
        global $botOptions;
        $this->opt = $botOptions;
        $this->currency = $this->opt->getOption('catalog', 'bot_catalog_currency');
    }

    public function getBasket($basketItem = false, $arNav = false, $userid = false)
    {
        $basketItem = (int)$basketItem > 0 ? (int)$basketItem : false;
        global $USER;
        $uid = $userid ? $userid : $USER->GetID();
        $arFUser = \CSaleUser::GetList(array('USER_ID' => $uid));
        $fUser = $arFUser['ID'];

        $basketClass = new \CSaleBasket();
        $dbBasketItems = $basketClass->GetList(array("ID" => "DESC"), array("FUSER_ID" => $fUser, "LID" => $this->opt->getOption('main', 'bot_site_id'), "ORDER_ID" => "NULL"), false, false, array());
        if($dbBasketItems->nSelectedCount > 0){

            $keyboard = [[
                [
                    'text' => $this->opt->getTitle('MAIN'),
                    'callback_data' => Utils::getCallBackStr([
                        'case' => $this->opt->getCase('getMain')
                    ])
                ],
                [
                	'text' => Emoji::CHARACTER_WASTEBASKET.' '.GetMessage('BASKET_CLEAR'),
                    'callback_data' => Utils::getCallBackStr([
                        'case' => $this->opt->getCase('clearBasket')
                    ])
                ]
            ]];

            $sum = 0;
            $arAllBasket = [];
            $arBasketText = [
                'title' => GetMessage('BASKET'),
            ];
            while ($arItem = $dbBasketItems->Fetch())
            {
            	$bcurrency = $arItem['CURRENCY'];
            	$priceStr = Utils::priceFormat(
            		$arItem['PRICE'],
            		$arItem['CURRENCY'],
            		$this->currency
            	);

            	$totalPriceStr = Utils::priceFormat(
            		$arItem['PRICE']*$arItem['QUANTITY'],
            		$arItem['CURRENCY'],
            		$this->currency
            	);

                $obElement = \CIBlockElement::GetByID($arItem['PRODUCT_ID'])->Fetch();
                $arAllBasket[]['ID'] = $arItem['ID'];

                $sum += $arItem['PRICE'] * $arItem['QUANTITY'];
                $product['NAME'] = GetMessage('IS_BOLD', ['#TEXT#' => $obElement['NAME']]);
                $product['PRICE'] = $priceStr.' <b>x</b> '.$arItem['QUANTITY'].' = '.$totalPriceStr;
                $arBasketText['product'.$arItem['PRODUCT_ID']] = implode($this->opt->getTitle('GET_DELIMITER_MSG_N'), $product);

                array_push($keyboard,
                    [
                        [
                        	'text' => Emoji::CHARACTER_CROSS_MARK.' '.trim($obElement['NAME']),
                            'callback_data' => Utils::getCallBackStr([
                                'case' => $this->opt->getCase('delBasket'),
                                'id'=>$arItem['ID']
                            ])
                        ]
                    ]
                );
            }
            array_push($keyboard, [
            	Utils::getKeyboardRowButton(Emoji::CHARACTER_WHITE_HEAVY_CHECK_MARK.' '.GetMessage('BASKET_CREATE_ORDER'), Utils::getCallBackStr([
                    'case' => $this->opt->getCase('createOrder'),
                    'type' => 'phone'
                ])),
            ]);

            $sumStr = Utils::priceFormat(
            		$sum,
            	$bcurrency,
            	$this->currency
            );

            $arBasketText['sum'] = GetMessage('BASKET_TOTAL').$sumStr;
            $text = implode($this->opt->getTitle('GET_DELIMITER_MSG'), $arBasketText);
            return new Msg($text, $keyboard);

        }else{
            $main = new \Oe\Telegrambot\Main();
            $msg = $main->getMainMessage();
            $keyboard = $msg->getKeyboard();
            return new Msg(GetMessage('BASKET_IS_EMPTY'), $keyboard);
        }
    }

    public function addBasket($elementId = false, $measure = 1, $offerId = false)
    {
        if($offerId){
            Add2BasketByProductID($offerId, $measure);
        }else{
            Add2BasketByProductID($elementId, $measure);
        }
    }

    public function addToBasket($elementId = false, $measure = 1, $offerId = false)
    {
        $basketMenu = array();
        for ($i = 1; $i <= 9; $i++) {
            $basketMenu[] =  array(
                'text' => $i,
                'callback_data' => Utils::getCallBackStr([
                    'case' => $this->opt->getCase('addBasket'),
                    'id' => $elementId,
                    'nElNum' => $arNav['nElNum'],
                    'sumEl' => $arNav['sumEl'],
                    'offerid'=> $offerId,
                    'measure' => $i
                ])
            );
        }
        $bottomMenu = [[[
            'text' => $this->opt->getTitle('BACK_BUTTON'),
            'callback_data' => Utils::getCallBackStr([
                'case' => $this->opt->getCase('elementPage'),
                'id' => $elementId,
                'offerid'=> $offerId
            ])
        ],
            [
                'text'=>$this->opt->getTitle('GET_CART')
            ]]];
        $basketMenu = array_chunk($basketMenu, 3);
        $basketMenu = array_merge($basketMenu, $bottomMenu);
        return new Msg(GetMessage('BASKET_ENTER_MEASURE'), $basketMenu);
    }

    public function delBasket($basketItemId)
    {
        $basketClass = new \CSaleBasket();
        $basketClass->Delete($basketItemId);
    }

    public function clearBasket()
    {
        $basketClass = new \CSaleBasket();
        $basketClass->DeleteAll(\CSaleBasket::GetBasketUserID());
        $main = new \Oe\Telegrambot\Main();
        $msg = $main->getMainMessage();
        $keyboard = $msg->getKeyboard();
        return new Msg(GetMessage('BASKET_IS_CLEAR'), $keyboard);
    }
}