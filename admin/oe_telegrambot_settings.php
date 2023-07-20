<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;

global $APPLICATION;
define('ADMIN_MODULE_NAME', 'oe.telegrambot'); $mid = ADMIN_MODULE_NAME;
IncludeModuleLangFile(__FILE__);
Loader::includeModule($mid);
$RIGHT = $APPLICATION->GetGroupRight($mid);
$APPLICATION->SetTitle(Loc::getMessage('OE_TGBOT_TITLE'));

//check modules
if(!Loader::IncludeModule('iblock')){echo Loc::getMessage('MODULE_IBLOCK_ERROR'); return false;}
if(!Loader::IncludeModule('catalog')){echo Loc::getMessage('MODULE_CATALOG_ERROR'); return false;}
if(!Loader::IncludeModule('sale')){echo Loc::getMessage('MODULE_SALE_ERROR'); return false;}

if($RIGHT >= "R"){
	$errorList = $arSites = [];
    $defSite = COption::GetOptionString($mid, 'bot_site_id');
    include __DIR__.'/../include/set_webhook.php';
    include __DIR__.'/../include/options/set_site_id.php';
    include __DIR__.'/../include/options/get_user_groups.php';
    include __DIR__.'/../include/options/get_iblocks.php';
    include __DIR__.'/../include/options/get_iblock_props.php';
    include __DIR__.'/../include/options/get_iblock_offer_props.php';
    include __DIR__.'/../include/options/get_order_props.php';
    include __DIR__.'/../include/options/get_order_dp.php';
    include __DIR__.'/../include/options/get_currency.php';
    include __DIR__.'/../include/options/get_prices.php';

    $push = isset($_REQUEST['bot_push_address'])?$_REQUEST['bot_push_address']:COption::GetOptionString($mid, 'bot_push_address');
    if($push){
        $bot = $telegram->getMe();
        try {
            $pushResult = $telegram->getChatMember(['chat_id' => $push,'user_id' => $bot['id']]);
            $pushAddress = GetMessage('OE_TGBOT_PUSH_IS_SET');
        } catch (Exception $e) {
            $pushAddress = GetMessage('OE_TGBOT_PUSH_IS_BAD');
        }
    }

    $sortElement = CIBlockParameters::GetElementSortFields(
        array('SHOWS', 'SORT', 'TIMESTAMP_X', 'NAME', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO'),
        array('KEY_LOWERCASE' => 'Y')
    );
    $sortSection = $sortElement;
    $sortElement = array_merge($sortElement, CCatalogIBlockParameters::GetCatalogSortFields());
    $byElement = ['ASC' => Loc::getMessage('OE_TGBOT_ASC'),'DESC' => Loc::getMessage('OE_TGBOT_DESC')];
    $pageCount = [6=>6,8=>8,10=>10,20=>20,30=>30,40=>40,100=>100,500=>500];
    $descrFields = ['PREVIEW_TEXT' => Loc::getMessage('OE_TGBOT_PREVIEW_TEXT'), 'DETAIL_TEXT' => Loc::getMessage('OE_TGBOT_DETAIL_TEXT')];
    $photoFields = ['PREVIEW_PICTURE' => Loc::getMessage('OE_TGBOT_PREVIEW_TEXT'), 'DETAIL_PICTURE' => Loc::getMessage('OE_TGBOT_DETAIL_TEXT')];

	$arAllOptions = [
		"MAIN" => [
		    "bot_params1" => [
		    	"TITLE" => "",
		        "TYPE" => "array",
		        "OPTIONS"=>[
		            "bot_token" => [
		                "TITLE" => Loc::getMessage("OE_TGBOT_TOKEN"),
		                "TYPE" => "text",
		                "DEFAULT" => "9999:XXXX",
	            		"NOTE" => $webHookInfo
		            ],
		            "bot_siteurl" => [
		                "TITLE" => Loc::getMessage("OE_TGBOT_BOTSITEPATH"),
		                "TYPE" => "text",
		                "DEFAULT" => ""
		            ],
		            "bot_webhook_url" => [
		                "TITLE" => Loc::getMessage("OE_TGBOT_WEBHOOKURL"),
		                "TYPE" => "text",
		            	"DISABLED" => true,
		                "DEFAULT" => "/xbot/hook.php"
		            ]
		        ]
		    ],
		    "note1" =>[
	           "TITLE" => "",
		       "TYPE" => "note",
	           "NOTE" => Loc::getMessage("OE_TGBOT_NOTE")
		    ],
		    "bot_params2" => [
		        "TITLE" => "",
		        "TYPE" => "array",
		        "OPTIONS"=>[
// 		            "bot_view_feedback" => [
// 		                "TITLE" => Loc::getMessage("OE_TGBOT_VIEW_FEEDBACK"),
// 		                "TYPE" => "checkbox",
// 		                "DEFAULT" => "N"
// 		            ],
		            "bot_max_users" => [
		                "TITLE" => Loc::getMessage("OE_TGBOT_MAXUSERS"),
		                "TYPE" => "selectbox",
		                "DEFAULT" => "40",
		            	"ITEMS" => [10=>10,20=>20,30=>30,40=>40,50=>50,60=>60,70=>70,80=>80,90=>90,100=>100]
		            ],
		            "bot_start_text" => [
		                "TITLE" => Loc::getMessage("OE_TGBOT_START_TEXT"),
		                "TYPE" => "textarea",
		                "DEFAULT" => Loc::getMessage("OE_TGBOT_START_TEXT_DEFAULT")
		            ],
		            "bot_user_group" => [
		                "TITLE" => Loc::getMessage("OE_TGBOT_USERGROUP"),
		                "TYPE" => "multiselectbox",
		                "DEFAULT" => "3,5",
		                "ITEMS" => $groups
		            ]
	            ]
	        ]
		],
		"STATIC" => [
		    "bot_about" => [
		        "TITLE" => Loc::getMessage("OE_TGBOT_BOTABOUT"),
		        "TYPE" => "textarea",
		        "DEFAULT" => ""
		    ],
		    "bot_help" => [
		        "TITLE" => Loc::getMessage("OE_TGBOT_BOTHELP"),
		        "TYPE" => "textarea",
		        "DEFAULT" => ""
		    ],
		    "bot_delivery" => [
		        "TITLE" => Loc::getMessage("OE_TGBOT_BOTDELIVERY"),
		        "TYPE" => "textarea",
		        "DEFAULT" => ""
		    ],
		    "bot_garanty" => [
		        "TITLE" => Loc::getMessage("OE_TGBOT_BOTGARANTY"),
		        "TYPE" => "textarea",
		        "DEFAULT" => ""
		    ],
		    "bot_news" => [
		        "TITLE" => Loc::getMessage("OE_TGBOT_BOTNEWS"),
		        "TYPE" => "textarea",
		        "DEFAULT" => ""
		    ],
		    "bot_contacts" => [
		        "TITLE" => Loc::getMessage("OE_TGBOT_BOTCONTACTS"),
		        "TYPE" => "textarea",
		        "DEFAULT" => ""
		    ]
		],
		"CATALOG" => [
			"bot_params1" => [
				"TITLE" => "",
				"TYPE" => "array",
				"OPTIONS"=>[
				    "bot_catalog_iblock" => [
				        "TITLE" => Loc::getMessage("OE_TGBOT_CATALOGIBLOCK"),
				        "TYPE" => "selectbox",
				        "DEFAULT" => "",
				        "ITEMS" => $iblocks
				    ],
				    "bot_catalog_price" => [
				    	"TITLE" => Loc::getMessage("OE_TGBOT_CATALOGGROUP"),
				        "TYPE" => "selectbox",
				        "DEFAULT" => "",
				        "ITEMS" => $prices
				    ],
				    "bot_catalog_currency" => [
				        "TITLE" => Loc::getMessage("OE_TGBOT_CATALOGCURRENCY"),
				        "TYPE" => "selectbox",
				        "DEFAULT" => "",
				        "ITEMS" => $currencies
				    ]
				]
			],
			"bot_params2" => [
				"TITLE" => Loc::getMessage('OE_TGBOT_SECTION_LIST'),
				"TYPE" => "array",
				"OPTIONS"=>[
					"bot_catalog_sort_section" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_CATALOGSORTSECTION"),
						"TYPE" => "selectbox",
						"DEFAULT" => "",
						"ITEMS" => $sortSection
					],
					"bot_catalog_by_section" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_CATALOGBYSECTION"),
						"TYPE" => "selectbox",
						"DEFAULT" => "",
						"ITEMS" => $byElement
					],
					"bot_catalog_pagecount_section" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_CATALOGPAGECOUNTSECTION"),
						"TYPE" => "selectbox",
						"DEFAULT" => "10",
						"ITEMS" => $pageCount
					],
					"bot_catalog_twice_section" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_CATALOGTWICESECTION"),
						"TYPE" => "checkbox",
						"DEFAULT" => "Y"
					],
					"bot_catalog_count_section" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_CATALOGCOUNTSECTION"),
						"TYPE" => "checkbox",
						"DEFAULT" => "N"
					],
				]
			],
			"bot_params3" => [
				"TITLE" => Loc::getMessage("OE_TGBOT_SECTION"),
				"TYPE" => "array",
				"OPTIONS"=>[
					"bot_catalog_sort_element" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_CATALOGSORTELEMENT"),
						"TYPE" => "selectbox",
						"DEFAULT" => "",
						"ITEMS" => $sortElement
					],
					"bot_catalog_by_element" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_CATALOGBYELEMENT"),
						"TYPE" => "selectbox",
						"DEFAULT" => "",
						"ITEMS" => $byElement
					],
					"bot_catalog_pagecount_element" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_CATALOGPAGECOUNTELEMENT"),
						"TYPE" => "selectbox",
						"DEFAULT" => "10",
						"ITEMS" => $pageCount
					],
					"bot_catalog_hidenotavail_element" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_CATALOGHIDENOTAVAILELEMENT"),
						"TYPE" => "checkbox",
						"DEFAULT" => "N"
					],
					"bot_catalog_element_descr" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_CATALOGELEMENTDESCR"),
						"TYPE" => "selectbox",
						"DEFAULT" => "DETAIL_TEXT",
						"ITEMS" => $descrFields
					],
					"bot_catalog_element_photofields" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_CATALOGELEMENTPHOTOFIELD"),
						"TYPE" => "selectbox",
						"DEFAULT" => "DETAIL_PICTURE",
						"ITEMS" => array_merge(['NOT' => Loc::getMessage('OE_TGBOT_NOT')], $photoFields)
					],
					"bot_catalog_element_props" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_CATALOGELEMENTPROPS"),
						"TYPE" => "multiselectbox",
						"DEFAULT" => "",
						"ITEMS" => $props
					]
				]
			]
		],
		"ORDER" => [
			"bot_params1" => [
				"TITLE" => "",
				"TYPE" => "array",
				"OPTIONS"=>[
					"bot_order_person_type" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_ORDERPTYPE"),
						"TYPE" => "selectbox",
						"DEFAULT" => "",
						"ITEMS" => $personType
					],
					"bot_order_props_phone" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_ORDERPROPSPHONE"),
						"TYPE" => "selectbox",
						"DEFAULT" => "",
						"REQUIRED" => true,
						"ITEMS" => $orderProps
					],
					"bot_order_props_address" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_ORDERPROPSADDRESS"),
						"TYPE" => "selectbox",
						"DEFAULT" => "",
						"REQUIRED" => true,
						"ITEMS" => $orderProps
					],
					"bot_order_delivery" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_ORDERDELIVERY"),
						"TYPE" => "multiselectbox",
						"DEFAULT" => "",
						"REQUIRED" => true,
						"ITEMS" => $delivery
					],
					"bot_order_paysystem" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_ORDERPAYSYSTEM"),
						"TYPE" => "multiselectbox",
						"DEFAULT" => "",
						"REQUIRED" => true,
						"ITEMS" => $paysystem
					]
				]
			],
			"bot_params2" => [
				"TITLE" => Loc::getMessage('OE_TGBOT_YANDEX_SETTINGS'),
				"TYPE" => "array",
				"OPTIONS"=>[
					"bot_order_yandex_apikey" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_YANDEX_APIKEY"),
						"TYPE" => "text",
						"DEFAULT" => "",
						"NOTE" => Loc::getMessage('OE_TGBOT_YANDEX_APIKEY_DESC')
					]
				]
			],
			"bot_params3" => [
				"TITLE" => Loc::getMessage("OE_TGBOT_ORDER_MIN_PRICE"),
				"TYPE" => "array",
				"OPTIONS"=>[
					"bot_order_min_price" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_ORDER_MIN_PRICE"),
						"TYPE" => "text",
						"DEFAULT" => "1000"
					],
					"bot_order_min_price_text" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_ORDER_MIN_PRICE_TEXT"),
						"TYPE" => "textarea",
						"DEFAULT" => Loc::getMessage("OE_TGBOT_ORDER_MIN_PRICE_DESC")
					]
				]
			]
    	],
		"PUSH" => [
			"bot_params1" => [
				"TITLE" => Loc::getMessage('OE_TGBOT_PUSHSETTINGS'),
				"TYPE" => "array",
				"OPTIONS"=>[
					"bot_push_address" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_PUSHADDRESS"),
						"TYPE" => "text",
						"DEFAULT" => "",
						"PLACEHOLDER" => "@username",
					    "NOTE" => $pushAddress
					]
				]
			],
			"note1" =>[
					"TITLE" => "",
					"TYPE" => "note",
					"NOTE" => Loc::getMessage("OE_TGBOT_PUSHADDRESSNOTE")
			],
			"bot_params2" => [
				"TITLE" => Loc::getMessage("OE_TGBOT_PUSHACTIVE"),
				"TYPE" => "array",
				"OPTIONS"=>[
					"bot_push_order_new" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_PUSHNEWORDER"),
						"TYPE" => "checkbox",
						"DEFAULT" => "N"
					],
					"bot_push_order_change_status" => [
						"TITLE" => Loc::getMessage("OE_TGBOT_PUSHCHANGEORDER"),
						"TYPE" => "checkbox",
						"DEFAULT" => "Y"
					]
				]
			]
		]
	];


    if(!empty($offerProps)){
        	$arAllOptions['CATALOG']['bot_params1']['OPTIONS']['bot_catalog_iblock_offer_prop'] = [
                "TITLE" => Loc::getMessage("OE_TGBOT_CATALOGIBLOCK_OFFER_PROP"),
                "TYPE" => "multiselectbox",
                "DEFAULT" => "",
                "ITEMS" => $offerProps
            ];
    }

    $arOptionsForClass = [];
    foreach($arAllOptions as $key=>$section)
    {
        foreach($section as $option)
        {
            if(is_array($option) && !empty($option[0]))
                $arOptionsForClass[$key][$option[0]] = $option[2];
        }
    }
	$errors = [];
    if($_SERVER["REQUEST_METHOD"] == "POST" && $RIGHT >= "W" && check_bitrix_sessid())
    {
        foreach($arAllOptions as $key=>$section)
        {
            foreach($section as $code=>$option)
            {
            	if($option['TYPE'] != 'array' && $option['TYPE'] != 'note')
                {
                	if(is_array($_REQUEST[$code]))$_REQUEST[$code] = implode(',', $_REQUEST[$code]);
                	COption::SetOptionString($mid, $code, $_REQUEST[$code]);
                }else{
                	if(!empty($option['OPTIONS'])){
	                	foreach ($option['OPTIONS'] as $subCode=>$subOption){
	                		if($subOption['REQUIRED']){
	                			if(empty($_REQUEST[$subCode])){
	                				$errors[$subCode] = $subCode;
	                			}
	                		}
	                		if($subOption['TYPE'] == 'checkbox' && !isset($_REQUEST[$subCode])){
	                			COption::SetOptionString($mid, $subCode, 'N');
	                		}
	                		if(isset($_REQUEST[$subCode])){
		                		if(is_array($_REQUEST[$subCode]))$_REQUEST[$subCode] = implode(',', $_REQUEST[$subCode]);
			                	COption::SetOptionString($mid, $subCode, $_REQUEST[$subCode]);
	                		}
	                	}
                	}
                }
            }

        }
    }
    $arTabs = [];
	$arTabs[] = array(
		"DIV" => "edit",
		"TAB" => Loc::getMessage("OE_OPTIONS_SITE_TITLE"),
		"ICON" => "settings",
		"PAGE_TYPE" => "site_settings",
		"SITE_ID" => $defSite
	);
    $tabControl = new CAdminTabControl("tabControl", $arTabs);
    CJSCore::Init(array("jquery"));
    $tabControl->Begin();
    ?>
    <link rel="stylesheet" type="text/css" href="/bitrix/themes/.default/icons/oe.telegrambot/style.css">
    <form method="post" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>">
        <?=bitrix_sessid_post()?>
        <?
        foreach($arTabs as $key => $arTab):
        $tabControl->BeginNextTab();
        $optionsSiteID = $arTab["SITE_ID"];
        ?>
        <input type="hidden" name="bot_site_id" value="<?=$optionsSiteID?>">
        <tr>
			<td colspan="2" class="site_<?=$optionsSiteID;?>">
		        <div class="tabs-wrapper">
    	        	<div class="tabs">
    	        	<div class="tabs-heading">
    	        	<?$l = 0;?>
    		        <?foreach($arAllOptions as $k => $arAllOption):?>
						<div class="head <?=($_COOKIE['activeHead_site_'.$optionsSiteID] !== false ? ($_COOKIE['activeHead_site_'.$optionsSiteID] == $l ? "active" : "") : (!$l ? "active" : ""));?>">
							<?=Loc::getMessage('OE_TGBOT_TAB_'.$k)?>
						</div>
					<?$l++;?>
					<?endforeach;?>
    				</div>
    			   	<div class="tabs-content">
    			   	<?if(!empty($errors)){?>
    			   		<div class="adm-info-message">
	    			   		<span class="required">
	    			   			<?=Loc::getMessage('OE_TGBOT_ERRORS')?>
			    			   	<?foreach ($errors as $error):?>
			    			   		<?=Loc::getMessage('OE_TGBOT_ERROR_'.$error)?>
	    			   			<?endforeach; ?>
	    			   		</span>
    			   		</div>
    			   	<?}?>
    			   	<?$i = 0;?>
    			    <?foreach($arAllOptions as $code => $arAllOption):?>
			        	<div class="tab <?=($_COOKIE['activeHead_site_'.$optionsSiteID] !== false ? ($_COOKIE['activeHead_site_'.$optionsSiteID] == $i ? "active" : "") : (!$i ? "active" : ""));?>" data-prop_code="<?=$code?>">
			        		<div class="title bg"><?=Loc::getMessage('OE_TGBOT_TAB_'.$code)?></div>
					        <?foreach ($arAllOption as $optionCode=>$arOption):?>
					        	<div class="<?=(!empty($arOption['OPTIONS']))?('groups_block '):('')?>block">
					        		<?=OeTgBotOptions::showAllAdminRows($optionCode, $arOption, $mid, $optionsSiteID);?>
					        	</div>
					        <?endforeach;?>
				        </div>
			        <?$i++;?>
					<?endforeach;?>
					</div>
					</div>
        		</div>
        	</td>
        </tr>
        <?
        endforeach;
        $tabControl->Buttons();
        ?>

        <input type="hidden" value="<?=$_REQUEST["tabControl_active_tab"]?>" name="tabControl_active_tab" id="tabControl_active_tab">
        <input type="hidden" name="update" value="Y" />
        <input type="submit" class="adm-btn-save" name="save" value="<?=Loc::getMessage( "MAIN_SAVE" )?>" />
    </form>
    <script type="text/javascript">
        /*set active tab*/
    	$('.tabs-wrapper .tabs-heading .head').click(function(){
    		var _this = $(this);
    		_this.siblings().removeClass('active');
    		_this.addClass('active');
    		_this.closest('.tabs-wrapper').find('.tabs-content .tab').removeClass('active');
    		_this.closest('.tabs-wrapper').find('.tabs-content .tab:eq('+_this.index()+')').addClass('active');

    		if(!!document.cookie)
    			document.cookie = 'activeHead_'+_this.closest('td').attr('class')+'='+_this.index()
    	});
    	$(document).on('change', 'input[type="checkbox"]', function() {
  			if($(this).is(':checked')){
  				$(this).val('Y');
  	  	  	}else{
  	  	  	  	$(this).val('N');
  	  	  	}
  		});
    </script>
    <?$tabControl->End();?>


    <?
}else{
    echo CAdminMessage::ShowMessage(Loc::getMessage('MODULE_NO_RIGHTS'));
}
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
