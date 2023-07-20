<?
namespace Oe\Telegrambot;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Spatie\Emoji\Emoji;

Loader::includeModule("iblock");

class Catalog
{
    private $opt = false;

    public function __construct()
    {
        global $botOptions;
        $this->opt = $botOptions;
    }

    /*PROTECTED FUNCTIONS*/

    protected function _getSectionField($sectionId = false, $field = false)
    {
        if(!$sectionId||!$field) return false;

        $arFilter = [
            'IBLOCK_ID' => $this->opt->getOption('catalog', 'bot_catalog_iblock'),
            'ID' => $sectionId,
            'ACTIVE' => 'Y'
        ];

        $dbSection = \CIblockSection::GetList(array(), $arFilter, false, array($field), false);
        if($obSection = $dbSection->GetNext())
            return $obSection[$field] ? $obSection[$field] : false;
        else
            return false;
    }

    protected function _getSectionPath($sectionId = false)
    {
        if((int)$sectionId == 0)
            return $this->opt->getTitle('CATALOG_TITLE');

        $depth = $this->_getSectionField($sectionId, 'DEPTH_LEVEL');
        $arTree = [];
        for($i = 1; $i <= $depth; $i++)
        {
            $arTree[] = $this->_getSectionField($sectionId, 'NAME');
            $sectionId = $this->_getSectionField($sectionId, 'IBLOCK_SECTION_ID');
        }
        $arTree[] = $this->opt->getTitle('CATALOG_TITLE');
        $arTree = array_reverse($arTree);

        return is_array($arTree) ? implode($this->opt->getTitle('BACK_SEPARATOR'), $arTree) : false;
    }

    protected function _getBackSectionButton($sectionId = false, $getParenSection = true, $parentPage = 1)
    {
        if($getParenSection)
            $sectionId = $this->_getSectionField($sectionId, 'IBLOCK_SECTION_ID');
        return [[[
            'text' => $this->opt->getTitle('BACK_BUTTON'),
            'callback_data' => Utils::getCallBackStr([
                'case' => $this->opt->getCase('sectionList'),
                'id' => $sectionId,
                'page' => $parentPage?:1
            ])
        ],
            [
                'text' => $this->opt->getTitle('MAIN'),
                'callback_data' => Utils::getCallBackStr([
                    'case' => $this->opt->getCase('getMain')
                ])]
        ]];
    }

    protected function _getElementMenu($elementId = false, $sectionId = false, $arNav = [])
    {
        $arElMenu = [[
            [
                'text' => $this->opt->getTitle('ADD_TO_CART') . CatalogUtils::getCountItem($elementId),
                'callback_data' => Utils::getCallBackStr([
                    'case' => $this->opt->getCase('addToBasket'),
                    'id' => $elementId,
                    'nElNum' => $arNav['nElNum'],
                    'sumEl' => $arNav['sumEl'],
                    'offerid'=>$arNav['offerid'],
                    'measure' => CatalogUtils::getMeasure($elementId)
                ])
            ],
            [
                'text' => $this->opt->getTitle('GET_CART')
            ]
        ],
            [
                [
                    'text' => $this->opt->getTitle('GET_WISHLIST'). CatalogUtils::getExistWishlistItem($elementId, $arNav['uid']),
                    'callback_data' => Utils::getCallBackStr([
                        'case' => $this->opt->getCase('addToWishlist'),
                        'id' => $elementId,
                        'nElNum' => $arNav['nElNum'],
                        'sumEl' => $arNav['sumEl'],
                        'offerid'=>$arNav['offerid'],
                        'measure' => CatalogUtils::getMeasure($elementId)
                    ])
                ]
            ]];

        if($sectionId)
        {
            $offerMenu = CatalogUtils::getOfferProperty($elementId, $arNav['offerid']);
            if(!empty($offerMenu))
                $arElMenu = array_merge($offerMenu, $arElMenu);
        }

        return $arElMenu;
    }

    protected function _getElementNavIDs($elementId = false, $sectionId = false)
    {
        $sort = CatalogUtils::getSortArray();
        $arFilter = [
            'IBLOCK_ID' => $this->opt->getOption('catalog', 'bot_catalog_iblock'),
            'SECTION_ID' => $sectionId,
            'ACTIVE' => 'Y'
        ];
        $arNavParams = [
            'nElementID' => $elementId,
            'nPageSize' => 1
        ];
        $arSelect = ['ID'];
        $dbElement = \CIblockElement::GetList($sort, $arFilter, false, $arNavParams, $arSelect);
        $navIds = [];
        while($obElement = $dbElement->GetNext())
            $navIds[] = $obElement['ID'];

        return is_array($navIds) ? $navIds : false;
    }


    //public catalog functions
    public function getSection($sectionId = false, $currentPage = 1)
    {

        $pageCount = $this->opt->getOption('catalog', 'bot_catalog_pagecount_section');
        $sort = CatalogUtils::getSortArray('SECTION_LIST');
        $arFilter = [
            'IBLOCK_ID' => $this->opt->getOption('catalog', 'bot_catalog_iblock'),
            'ACTIVE' => 'Y',
            'CNT_ACTIVE' => 'Y'
        ];
        $cnt = $this->opt->getOption('catalog', 'bot_catalog_count_section') == 'Y' ? true : false;
        $arFields = ['ID', 'NAME'];
        if(!$sectionId)
            $arFilter['DEPTH_LEVEL'] = 1;
        else
            $arFilter['SECTION_ID'] = $sectionId;

        $arNavStartParams = [
            'iNumPage' => $currentPage,
            'nPageSize' => $pageCount
        ];

        $dbSection = \CIblockSection::GetList($sort, $arFilter, true, $arFields, $arNavStartParams);
        $sectionMenu = [];

        while($obSection = $dbSection->GetNext())
        {
            if($obSection['ELEMENT_CNT'] == 0) continue;

            if($cnt) $obSection['NAME'] .= ' (' . $obSection['ELEMENT_CNT'] . ')';
            $sectionMenu[] = [
                'text' => trim($obSection['NAME']),
                'callback_data' => Utils::getCallBackStr([
                    'case' => $this->opt->getCase('sectionList'),
                    'id' => $obSection['ID'],
                    'page' => 1
                ])
            ];
        }

        $chunk = $this->opt->getOption('catalog', 'bot_catalog_twice_section') == 'Y' ? 2 : 1;
        $sectionMenu = array_chunk($sectionMenu, $chunk);

        /*paginationQuery*/
        $dbSumSection = \CIblockSection::GetList($sort, $arFilter, false, [], false);
        $sumElements = $dbSumSection->SelectedRowsCount();
        if($sumElements > $pageCount)
        {
            $sumPages = (int)ceil($sumElements / $pageCount);
            $sectionMenu = array_merge($sectionMenu, CatalogUtils::getPageNav($this->opt->getCase('sectionList'), $sectionId, $currentPage, $sumPages));
        }

        if(empty($sectionMenu) && $sectionId)
            return $this->getElementList($sectionId, 1, $currentPage);

        if($sectionId)
            $sectionMenu = array_merge($sectionMenu, $this->_getBackSectionButton($sectionId));

        $text =  $this->_getSectionPath($sectionId);
        if(!$sectionId){
            array_push($sectionMenu, array(['text'=>$this->opt->getTitle('MAIN')]));
        }
        return new Msg($text, $sectionMenu);
    }


    public function getElementList($sectionId = false, $startNavPage = 1)
    {
        $pageCount = $this->opt->getOption('catalog', 'bot_catalog_pagecount_element');
        $sort = CatalogUtils::getSortArray();
        $arFilter = [
            'IBLOCK_ID' => $this->opt->getOption('catalog', 'bot_catalog_iblock'),
            'ACTIVE' => 'Y'
        ];

        if($sectionId)
            $arFilter['SECTION_ID'] = $sectionId;

        if($this->opt->getOption('catalog', 'bot_catalog_hidenotavail_element') == 'Y')
            $arFilter['CATALOG_AVAILABLE'] = 'Y';

        $arNavStart = [
            'nPageSize' => $pageCount,
            'iNumPage' => $startNavPage
        ];

        $arSelect = ['NAME', 'ID'];

        /*paginationQuery*/
        $dbSumElement = \CIblockElement::GetList($sort, $arFilter, false, false, $arSelect);
        $sumElements = $dbSumElement->SelectedRowsCount();
        $cnt = ($startNavPage - 1) * $pageCount + 1;

        $dbElement = \CIblockElement::GetList($sort, $arFilter, false, $arNavStart, $arSelect);
        $elementListMenu = [];
        while($obElement = $dbElement->GetNext())
        {
            $offerId = CatalogUtils::getSelectOffer($obElement['ID']);

            $elementListMenu[] = [
                'text' => TruncateText(strip_tags(html_entity_decode($obElement['NAME'])),50),
                'callback_data' => Utils::getCallBackStr([
                    'case' => $this->opt->getCase('elementPage'),
                    'id' => $obElement['ID'],
                    'offerid' => $offerId,
                    'nElNum' => $cnt,
                    'sumEl' => $sumElements
                ])
            ];

            $cnt++;
        }

        $elementListMenu = array_chunk($elementListMenu, 2);

        if($sumElements > $pageCount)
        {
            $sumPages = floor($sumElements / $pageCount);
            if(floor($sumElements / $pageCount) != ($sumElements / $pageCount))
                $sumPages++;
            $elementListMenu = array_merge($elementListMenu, CatalogUtils::getPageNav($this->opt->getCase('elementList'), $sectionId, $startNavPage, $sumPages));
        }

        if($sectionId)
            $elementListMenu = array_merge($elementListMenu, $this->_getBackSectionButton($sectionId));

        $text =  $this->_getSectionPath($sectionId);

        return new Msg($text, $elementListMenu);
    }

    public function getElement($elementId = false, $params = [])
    {
        //get list params
        $sort = CatalogUtils::getSortArray();
        $arFilter = [
            'IBLOCK_ID' => $this->opt->getOption('catalog', 'bot_catalog_iblock'),
            'ID' => $elementId,
            'ACTIVE' => 'Y'
        ];
        $arSelect = ['NAME', 'ID', 'DETAIL_PICTURE', 'PREVIEW_PICTURE', 'IBLOCK_SECTION_ID', 'DETAIL_TEXT', 'PREVIEW_TEXT'];

        //properties
        $arPropCode = explode(',', $this->opt->getOption('catalog', 'bot_catalog_element_props'));
        $arSelectProps = [];
        if(count($arPropCode) > 0){
            foreach ($arPropCode as &$value)
            {
                $value = explode('|', $value);
                $value = ['CODE' => $value[0], 'NAME' => $value[1]];
                $arSelectProps[] = 'PROPERTY_' . $value['CODE'];
            }
        }

        //finall select param
        $arSelect = array_merge($arSelect, $arSelectProps);
        $dbElement = \CIblockElement::GetList($sort, $arFilter, false, false, []);
        if($ob = $dbElement->GetNextElement())
        {
            $obElement = $ob->GetFields();
            $obElement["PROPERTIES"] = $ob->GetProperties();
            $photo = CatalogUtils::getElementPhoto($obElement, $params['offerid']);

            $arElementText = [
            	'name'  => Emoji::CHARACTER_SHOPPING_BAGS.' '.GetMessage('ELEMENT_NAME', ['#NAME#' => CatalogUtils::getElementName($obElement, $params['offerid'])]),
                'price' => GetMessage('ELEMENT_PRICE').CatalogUtils::getPrice($elementId, $params['offerid']),
                'offer_prop' => CatalogUtils::getElementOfferProps($elementId, $params['offerid']),
                'props' => (count($arPropCode) > 0) ? CatalogUtils::getElementProps($obElement, $arPropCode) : '',
                'descr' => TruncateText(CatalogUtils::getElementDescr($obElement), 200)
            ];

            if($arElementText['descr'] == '' || empty($arElementText['descr'])) unset($arElementText['descr']);
            if($arElementText['offer_prop'] == '' || empty($arElementText['offer_prop'])) unset($arElementText['offer_prop']);
            if($arElementText['props'] == '' || empty($arElementText['props'])) unset($arElementText['props']);
            $text = implode($this->opt->getTitle('GET_DELIMITER_MSG'), $arElementText);

            $keyboard = array_merge(
                $this->_getElementMenu($elementId, $obElement['IBLOCK_SECTION_ID'], $params),
                $this->_getBackSectionButton($obElement['IBLOCK_SECTION_ID'], false)
            );

            return new Msg(['photo'=>$photo, 'caption'=>$text], $keyboard);
        }
    }

    public function getOfferProp($elementId = false, $offerId = false, $prop = false)
    {
        if(!$elementId) return false;

        $obElement = \CIBlockElement::GetByID($elementId)->Fetch();
        if(\CCatalogSKU::getExistOffers($elementId, $this->opt->getOption('catalog', 'bot_catalog_iblock'))){
            $arInfo = \CCatalogSKU::GetInfoByProductIBlock($this->opt->getOption('catalog', 'bot_catalog_iblock'));

            $allProduct = $codes = [];
            $skuProps = explode(',', $this->opt->getOption('catalog', 'bot_catalog_iblock_offer_prop'));
            if(count($skuProps) > 0){
                foreach ($skuProps as &$code){
                    $code = explode('|', $code);
                    $codes[] = $code[0];
                }
            }

            $rsOffers = \CIBlockElement::GetList(array(), ['IBLOCK_ID'=>$arInfo['IBLOCK_ID'], 'PROPERTY_'.$arInfo['SKU_PROPERTY_ID'] => $elementId]);
            while ($obOffer = $rsOffers->GetNextElement())
            {
                $el = $obOffer->GetFields();
                $el["PROPERTIES"] = $obOffer->GetProperties();
                $allProduct[] = $el;
            }

            foreach ($allProduct as $product){
                if($product['ID'] == $offerId) continue;
                foreach ($product["PROPERTIES"] as $arProp){
                    if($arProp['ID'] == $prop){
                    	$sid = !empty($arProp['USER_TYPE_SETTINGS']) ? $arProp['VALUE'] : $arProp['VALUE_ENUM_ID'];
                        $name = !empty($arProp['USER_TYPE_SETTINGS']) ? CatalogUtils::getValueByXmlId($arProp['USER_TYPE_SETTINGS']['TABLE_NAME'], $arProp['VALUE']) : $arProp['VALUE'];
                        $arProperty[$sid] = array(
                            'text' => trim($name),
                            'callback_data' => Utils::getCallBackStr([
                            	'case' => $this->opt->getCase('elementPage'),
                            	'id' => $elementId,
                            	'offerid' => $product['ID']
                            ])
                        );
                    }
                }
            }
        }

        $arProperty = array_chunk($arProperty, 2);
        $keyboard = array_merge(
            $arProperty,
            $this->_getBackSectionButton($obElement['IBLOCK_SECTION_ID'], false)
        );

        return new Msg(GetMessage('GET_OFFER_VALUE'), $keyboard);
    }
}