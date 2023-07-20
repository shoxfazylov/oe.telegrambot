<?
namespace Oe\Telegrambot;

class Search
{
    public function getSearchButton()
    {
        global $botOptions;
        $keyboard = [
			[[
				'text' => $botOptions->getTitle('BACK_BUTTON'),
				'search' => true,
				'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
					'case' => $botOptions->getCase('searchQuery')
				])
			]]
		];

        return new \Oe\Telegrambot\Msg($botOptions->getTitle('GET_SEARCH_TEXT'), $keyboard);
    }

    public function find($query, $startNavPage = 1)
    {
    		global $botOptions;

    		\Bitrix\Main\Loader::includeModule('iblock');
    		$pageCount = $botOptions->getOption('catalog', 'bot_catalog_pagecount_element');

    		$sort = ['SORT' => 'ASC', 'NAME' => 'DESC'];
    		$arFilter = [
    			'ACTIVE' => 'Y',
    		    'SECTION_GLOBAL_ACTIVE' => 'Y',
    			'IBLOCK_ID' => $botOptions->getOption('catalog', 'bot_catalog_iblock'),
    			'%SEARCHABLE_CONTENT'=>$query
    		];

    		if($botOptions->getOption('catalog', 'bot_catalog_hidenotavail_element') == 'Y'){
    			$arFilter['CATALOG_AVAILABLE'] = 'Y';
    		}

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
    		while ($obElement = $dbElement->Fetch()){
    			$offerId = \Oe\Telegrambot\CatalogUtils::getSelectOffer($obElement['ID']);
    			$elementListMenu[] = [
    					'text' => TruncateText(strip_tags(html_entity_decode($obElement['NAME'])),50),
    					'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
    						'case' => $botOptions->getCase('elementPage'),
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
    				$elementListMenu = array_merge($elementListMenu, \Oe\Telegrambot\CatalogUtils::getPageNav($botOptions->getCase('elementList'), $sectionId, $startNavPage, $sumPages));
    		}

    		if(!empty($elementListMenu)){
    			$keyboard = array_merge(
    					$elementListMenu,
    					[
	    					[[
	    							'text' => $botOptions->getTitle('BACK_BUTTON'),
	    							'search' => true,
	    							'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
	    									'case' => $botOptions->getCase('searchQuery')
	    							])
	    					]]
    					]
    			);
    			$msg = str_replace('#FIND#',$query, $botOptions->getTitle('GET_SEARCH_QUERY'));
    		}else{
    			$keyboard = [
					[[
						'text' => $botOptions->getTitle('BACK_BUTTON'),
						'search' => true,
						'callback_data' => \Oe\Telegrambot\Utils::getCallBackStr([
							'case' => $botOptions->getCase('searchQuery')
						])
					]]
				];
    			$msg = $botOptions->getTitle('GET_SEARCH_NONE_TEXT');
    		}

	    	return new \Oe\Telegrambot\Msg($msg, $keyboard);
    }
}