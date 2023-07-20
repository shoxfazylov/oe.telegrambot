<?
namespace Oe\Telegrambot\Entity;

use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Type;

Loader::includeModule("iblock");
class WishlistTable extends Entity\DataManager
{
    private $opt = false;

    public function __construct()
    {
        global $botOptions;
        $this->opt = $botOptions;
    }

    public static function getFilePath()
    {
    	return __FILE__;
    }

    public static function getTableName()
    {
    	return 'b_oe_tgbot_wishlist';
    }

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'TIMESTAMP_CHANGE' => array(
				'data_type' => 'datetime',
                'default_value' => new Type\Datetime
			),
			'USER_ID' => array(
                'data_type' => 'integer',
				'required' => true
			),
			'PRODUCT_ID' => array(
				'data_type' => 'integer',
				'required' => true
			)
		);
	}

	public function getWishlistList($userId = null)
	{
	    if(!$userId) return false;

        $items = self::getList([
            'select' => ['ID','PRODUCT_ID'],
            'filter' => ['USER_ID'=>$userId]
        ])->fetchAll();

        if(!empty($items)){

            $sort = \Oe\Telegrambot\CatalogUtils::getSortArray();
            $arFilter = [
                'IBLOCK_ID' => $this->opt->getOption('catalog', 'bot_catalog_iblock'),
                'ACTIVE' => 'Y'
            ];

            foreach ($items as $item) $arFilter['ID'][] = $item['PRODUCT_ID'];

            if($this->opt->getOption('catalog', 'bot_catalog_hidenotavail_element') == 'Y')
          		$arFilter['CATALOG_AVAILABLE'] = 'Y';


            $arSelect = ['NAME', 'ID'];
            $dbElement = \CIblockElement::GetList($sort, $arFilter, false, false, $arSelect);
            $elementListMenu = [];
            while($obElement = $dbElement->GetNext())
            {
                $offerId = \Oe\Telegrambot\CatalogUtils::getSelectOffer($obElement['ID']);
                $elementListMenu[] = [
                    'text' => TruncateText(strip_tags(html_entity_decode($obElement['NAME'])),50),
                    'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
                        'case' => $this->opt->getCase('elementPage'),
                        'id' => $obElement['ID'],
                        'offerid' => $offerId
                    ])
                ];
            }

            $elementListMenu = array_chunk($elementListMenu, 2);
            array_push($elementListMenu, array(
                [
                    'text'=>$this->opt->getTitle('BACK_BUTTON'),
                    'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
                        'case' => $this->opt->getCase('getProfile')
                    ])
                ],
                ['text'=>$this->opt->getTitle('MAIN')]
            ));

            $remove = array([[
                'text' => $this->opt->getTitle('REMOVE_ALL'),
                'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
                    'case' => $this->opt->getCase('delWishlist')
                ])
            ]]);
            $elementListMenu = array_merge($remove, $elementListMenu);
            return new \Oe\Telegrambot\Msg(GetMessage('WISHLIST'), $elementListMenu);
        }else{
            $profile = new \Oe\Telegrambot\Profile();
            $msg = $profile->getProfileMessage($userId);
            $keyboard = $msg->getKeyboard();
            return new \Oe\Telegrambot\Msg(GetMessage('WISHLIST_IS_EMPTY'), $keyboard);
        }
	}

    public function addToWishlist($elementId = false, $userId = null)
    {
        if($elementId && $userId){
            $item = self::getList([
                'select' => ['ID'],
                'filter' => ['PRODUCT_ID'=>$elementId, 'USER_ID'=>$userId]
            ])->fetchAll();
            if(empty($item)){
                self::add([
                    'PRODUCT_ID'=>$elementId,
                    'USER_ID'=>$userId
                ]);
            }
        }
    }

	public function delWishlist($userId = null)
	{
        $w = self::getList([
            'select' => ['ID'],
            'filter' => ['USER_ID'=>$userId]
        ]);

        while ($result = $w->fetch()){
            self::delete($result['ID']);
        }

        $profile = new \Oe\Telegrambot\Profile();
        $msg = $profile->getProfileMessage($userId);
        $keyboard = $msg->getKeyboard();
        return new \Oe\Telegrambot\Msg(GetMessage('WISHLIST_CLEAR'), $keyboard);
	}
}