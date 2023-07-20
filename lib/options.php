<?
namespace Oe\Telegrambot;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
use Spatie\Emoji\Emoji;

Loc::loadMessages(__FILE__);

class Options
{
    const MID = 'oe.telegrambot';

    private $options = [
        'main' =>
            [
                'bot_siteurl' => '',
                'bot_token' => '',
                'bot_replay_token' => '',
                'bot_start_text' => '',
                'bot_user_group' => '3,5',
                'bot_site_id' => 's1',
                'bot_help' => '',
                'bot_contacts' => '',
                'bot_news' => '',
                'bot_about' => '',
                'bot_delivery' => '',
                'bot_garanty' => '',
                'bot_redirect' => '',
                'bot_view_feedback' => ''
            ],
        'catalog' =>
            [
                'bot_catalog_iblock' => '',
                'bot_catalog_currency' => 'RUB',
                'bot_catalog_price' => 'BASE',
                'bot_catalog_sort_section' => '',
                'bot_catalog_by_section' => '',
                'bot_catalog_pagecount_section' => '9',
                'bot_catalog_twice_section' => 'N',
                'bot_catalog_count_section' => 'N',
                'bot_catalog_sort_element' => '',
                'bot_catalog_by_element' => '',
                'bot_catalog_pagecount_element' => '9',
                'bot_catalog_hidenotavail_element' => 'Y',
                'bot_catalog_show_only_goods' => 'N',
                'bot_catalog_element_descr' => 'DETAIL_TEXT',
                'bot_catalog_element_htmlsplit' => 'Y',
                'bot_catalog_element_photofields' => 'DETAIL_PICTURE',
                'bot_catalog_element_photoprop' => 'MORE_PHOTO',
                'bot_catalog_element_props' => '',
                'bot_catalog_iblock_offer_prop' => ''
            ],
        'order' => [
            'bot_order_person_type' => 1,
            'bot_order_props' => '',
            'bot_order_props_phone' => '',
            'bot_order_props_loc' => '',
            'bot_order_props_address' => '',
            'bot_order_yandex_apikey' => '',
        	'bot_order_min_price' => '',
        	'bot_order_min_price_text' => '',
            'bot_order_delivery' => '',
            'bot_order_paysystem' => ''
        ],
    	'push' => [
    		'bot_push_address' => '',
    		'bot_push_order_new' => '',
    		'bot_push_order_change_status' => ''
    	]
    ];
    private $actionBoard = false;
    private $cases = [
		'getMain' => 'getMain',
        'sectionList' => 'sectionList',
        'elementList' => 'elementList',
        'elementPage' => 'elementPage',
        'addBasket' => 'addBasket',
        'addToBasket' => 'addToBasket',
        'addToWishlist' => 'addToWishlist',
        'delWishlist' => 'delWishlist',
        'viewOfferProp' => 'viewOfferProp',
        'delBasket' => 'delBasket',
        'clearBasket' => 'clearBasket',
        'changeQty' => 'changeQty',
        'getBasketItem' => 'getBasketItem',
        'createOrder' => 'createOrder',
        'getOrderStatusList' => 'getOrderStatusList',
        'getOrderList' => 'getOrderList',
        'getOrder' => 'getOrder',
        'getProfile' => 'getProfile',
    	'getSearch' => 'getSearch',
    	'searchQuery' => 'searchQuery',
        'feedback' => 'feedback',
    	'signIn' => 'signIn'
    ];
    private $titles = [];

    public function __construct()
    {

        $tmpOptions = [];
        foreach ($this->options as $section => $options) {
            foreach ($options as $key => $value) {
                $tmpOptions[$section][$key] = Option::get($this::MID, $key, $value);
            }
        }
        $this->options = $tmpOptions;
    }

    public function allTitles()
    {
    	$this->titles = [
    			'MAIN' => Emoji::CHARACTER_HOUSE_BUILDING.' '.Loc::getMessage('GET_MAIN'),
    			'PROFILE' => Emoji::CHARACTER_BUST_IN_SILHOUETTE.' '.Loc::getMessage('GET_PROFILE'),
    			'GET_SEARCH' => Emoji::CHARACTER_LEFT_POINTING_MAGNIFYING_GLASS.' '.Loc::getMessage('GET_SEARCH'),
    			'GET_ABOUT' => Emoji::CHARACTER_BRIEFCASE.' '.Loc::getMessage('GET_ABOUT'),
    			'GET_WISHLIST' => Emoji::CHARACTER_GLOWING_STAR.' '.Loc::getMessage('GET_WISHLIST'),
    			'GET_ORDERS' => Emoji::CHARACTER_SHOPPING_BAGS.' '.Loc::getMessage('GET_ORDERS'),
    			'CATALOG_TITLE' => Loc::getMessage("CATALOG_TITLE"),
    			'BACK_SEPARATOR' => Loc::getMessage("BACK_SEPARATOR"),
    			'BACK_BUTTON' => Emoji::CHARACTER_LEFTWARDS_BLACK_ARROW.' '.Loc::getMessage("BACK_BUTTON"),
    			'ADD_TO_CART' => Emoji::CHARACTER_WHITE_HEAVY_CHECK_MARK.' '.Loc::getMessage("ADD_TO_CART"),
    			'NEXT_PAGE' => Loc::getMessage("NEXT_PAGE"),
    			'PREV_PAGE' => Loc::getMessage("PREV_PAGE"),
    			'GET_PHONE' => Loc::getMessage("GET_PHONE"),
    			'GET_START_MSG' => Option::get($this::MID, 'bot_start_text') ? Option::get($this::MID, 'bot_start_text') : Loc::getMessage("GET_START_MSG"),
    			'GET_DELIMITER_MSG' => Loc::getMessage("GET_DELIMITER_MSG"),
    			'GET_DELIMITER_MSG_N' => Loc::getMessage("GET_DELIMITER_MSG_N"),
    			'GET_ENTER_ACTION' => Loc::getMessage("GET_ENTER_ACTION"),
    			'ADD_ADDRESS' => Loc::getMessage("ADD_ADDRESS"),
    			'GET_FEEDBACK' => Emoji::CHARACTER_ENVELOPE.' '.Loc::getMessage('GET_FEEDBACK'),
    			'GET_CATALOG' => Emoji::CHARACTER_OPEN_BOOK.' '.Loc::getMessage('GET_CATALOG'),
    			'GET_CART' => Emoji::CHARACTER_INBOX_TRAY.' '.Loc::getMessage('GET_CART'),
    			'GET_NEWS' => Emoji::CHARACTER_PAGE_FACING_UP.' '.Loc::getMessage('GET_NEWS'),
    			'GET_CONTACTS' => Emoji::CHARACTER_TELEPHONE_RECEIVER.' '.Loc::getMessage('GET_CONTACTS'),
    			'GET_GARANTY' => Emoji::CHARACTER_CONSTRUCTION_WORKER.' '.Loc::getMessage('GET_GARANTY'),
    			'GET_DELIVERY' => Emoji::CHARACTER_DELIVERY_TRUCK.' '.Loc::getMessage('GET_DELIVERY'),
    			'GET_HELP' => Emoji::CHARACTER_BLACK_QUESTION_MARK_ORNAMENT.' '.Loc::getMessage('GET_HELP'),
    			'GET_FEEDBACK_PHOTO' => Emoji::CHARACTER_CAMERA_WITH_FLASH.' '.Loc::getMessage('GET_FEEDBACK_PHOTO'),
    			'GET_FEEDBACK_PHONE' => Emoji::CHARACTER_TELEPHONE_RECEIVER.' '.Loc::getMessage('GET_FEEDBACK_PHONE'),
    			'GET_FEEDBACK_LOCATION' => Emoji::CHARACTER_ROUND_PUSHPIN.' '.Loc::getMessage('GET_FEEDBACK_LOCATION'),
    			'REMOVE_ALL' => Emoji::CHARACTER_WASTEBASKET.' '.Loc::getMessage('REMOVE_ALL'),
    			'GET_SEARCH_TEXT' => Loc::getMessage('GET_SEARCH_TEXT'),
    			'GET_SEARCH_QUERY' => Loc::getMessage('GET_SEARCH_QUERY'),
    			'GET_SEARCH_NONE_TEXT' => Loc::getMessage('GET_SEARCH_NONE_TEXT'),
    			'GET_AUTH' => Emoji::CHARACTER_KEY.' '.Loc::getMessage('GET_AUTH'),
    			'GET_REGISTER' => Loc::getMessage('GET_REGISTER'),
				'TEXT_EMPTY' => Loc::getMessage('TEXT_EMPTY'),
    			'GET_PERSONAL' => Emoji::CHARACTER_BUST_IN_SILHOUETTE.' '.Loc::getMessage('GET_PERSONAL'),
    	];
    }
    public function allActions()
    {
    	$this->actionBoard = [
    			'getMain' => Emoji::CHARACTER_HOUSE_BUILDING.' '.Loc::getMessage('GET_MAIN'),
    			'getAbout' => Emoji::CHARACTER_BRIEFCASE.' '.Loc::getMessage('GET_ABOUT'),
    			'getDelivery' => Emoji::CHARACTER_DELIVERY_TRUCK.' '.Loc::getMessage('GET_DELIVERY'),
    			'getGaranty' => Emoji::CHARACTER_CONSTRUCTION_WORKER.' '.Loc::getMessage('GET_GARANTY'),
    			'getNews' => Emoji::CHARACTER_PAGE_FACING_UP.' '.Loc::getMessage('GET_NEWS'),
    			'getHelp' => Emoji::CHARACTER_BLACK_QUESTION_MARK_ORNAMENT.' '.Loc::getMessage('GET_HELP'),
    			'getContacts' => Emoji::CHARACTER_TELEPHONE_RECEIVER.' '.Loc::getMessage('GET_CONTACTS'),
    			'getWishlist' => Emoji::CHARACTER_GLOWING_STAR.' '.Loc::getMessage('GET_WISHLIST'),
    			'getCatalog' => Emoji::CHARACTER_OPEN_BOOK.' '.Loc::getMessage('GET_CATALOG'),
    			'getCart' => Emoji::CHARACTER_INBOX_TRAY.' '.Loc::getMessage('GET_CART'),
    			'getOrders' => Emoji::CHARACTER_SHOPPING_BAGS.' '.Loc::getMessage('GET_ORDERS'),
    			'getProfile' => Emoji::CHARACTER_BUST_IN_SILHOUETTE.' '.Loc::getMessage('GET_PROFILE'),
    			'feedback' => Emoji::CHARACTER_ENVELOPE.' '.Loc::getMessage('GET_FEEDBACK'),
    			'addAddress' => Loc::getMessage("ADD_ADDRESS"),
    			'getSearch' => Emoji::CHARACTER_LEFT_POINTING_MAGNIFYING_GLASS.' '.Loc::getMessage('GET_SEARCH'),
    			'searchQuery' => Loc::getMessage('GET_SEARCH_QUERY'),
    	];

    	if(SITE_CHARSET == 'windows-1251')
    		$this->actionBoard = \Bitrix\Main\Text\Encoding::convertEncoding($this->actionBoard, 'windows-1251', 'UTF-8');

    }

    public function getOption($sectionId = false, $optionId = false)
    {
        return isset($this->options[$sectionId][$optionId]) ? $this->options[$sectionId][$optionId] : false;
    }

    public function getCase($caseId)
    {
        return isset($this->cases[$caseId]) ? $this->cases[$caseId] : false;
    }

    public function getAction($action)
    {
    	self::allActions();
        return isset($this->actionBoard[$action]) ? $this->actionBoard[$action] : false;
    }

    public function getTitle($titleId)
    {
    	self::allTitles();
        return isset($this->titles[$titleId]) ? $this->titles[$titleId] : false;
    }

    public function getKeyboard()
    {
        $main = new \Oe\Telegrambot\Main();
        $msg = $main->getMainMessage();
        $keyboard = $msg->getKeyboard();
        return $keyboard;
    }

    public function getApiPath()
    {
        return __DIR__ . '/../include/botapi/vendor/autoload.php';
    }

}