<?
IncludeModuleLangFile( __FILE__ );
use \Bitrix\Main\Config\Option;
class OeTgBotOptions
{
    public $arCurOptionValues = array ();

    private $module_id = '';

    private $arTabs = array ();

    private $arGroups = array ();

    private $arOptions = array ();

    private $need_access_tab = false;

    public function __construct($module_id, $arTabs, $arGroups, $arOptions, $need_access_tab = false)
    {
        $this->module_id = $module_id;
        $this->arTabs = $arTabs;
        $this->arGroups = $arGroups;
        $this->arOptions = $arOptions;
        $this->need_access_tab = $need_access_tab;
        if($need_access_tab)
        {
            $this->arTabs[] = array (
                'DIV' => 'edit_access_tab',
                'TAB' => GetMessage( "sns.tools1c_access_Tab" ),
                'ICON' => '',
                'TITLE' => GetMessage( "sns.tools1c_access_title" )
            );
        }
    }

    public static function showAllAdminRows($optionCode, $arOption, $module_id, $optionsSiteID){
        if($optionCode)
    	{
    	    if($arOption['TYPE'] == 'note')//if($arOption['TYPE'] === 'array')
    		{
    		?>
				<div class="notes-block visible_block1" data-option_code="<?=$optionCode;?>">
					<div align="center">
						<?=BeginNote('align="center" name="'.htmlspecialcharsbx($optionCode)."_".$optionsSiteID.'"');?>
						<?=($arOption["TITLE"] ? $arOption["TITLE"] : $arOption["NOTE"])?>
						<?=EndNote();?>
					</div>
				</div>
			<?
    		}
    		elseif($arOption['TYPE'] == 'array')
    		{
    		    ?>
    		    <?if($arOption["TITLE"]){?>
    		    	<div class="title"><?=$arOption["TITLE"]?></div>
    		    <?}?>
				<?foreach($arOption['OPTIONS'] as $optionC=>$option):
				$optionVal = Option::get($module_id, $optionC, $option['DEFAULT'], $optionsSiteID);
				?>
    				<div class="item js_block <?=$option['TYPE']?>" data-class="<?=$optionC;?>">
    					<div>
    						<div class="inner_wrapper <?=$option['TYPE']?>">
								<div class="inner">
									<div class="title_wrapper"><div class="subtitle"><?=$option['TITLE']?></div></div>
									<?=self::ShowAdminRow(
									    $optionC,
									    $option,
										$optionVal,
									    $module_id,
									    $optionsSiteID
									);?>
								</div>
    						</div>
    					</div>
    				</div>
    				<?if($option['NOTE']){?>
	    		    	<div class="notes-block visible_block1" data-option_code="<?=$optionC;?>">
							<div align="center">
								<?=BeginNote('align="center" name="'.htmlspecialcharsbx($optionC)."_".$optionsSiteID.'"');?>
								<?=$option["NOTE"]?>
								<?=EndNote();?>
							</div>
						</div>
	    		    <?}?>
				<?endforeach;?>
    		    <?
			}
			else
			{
				$optionVal = Option::get($module_id, $optionCode, "", $optionsSiteID);
				?>
				<div class="item js_block <?=$arOption['TYPE']?>" data-class="<?=$optionCode;?>">
					<div>
						<div class="inner_wrapper <?=$arOption['TYPE']?>">
							<div class="title_wrapper"><div class="subtitle"><?=$arOption['TITLE']?></div></div>
							<?=self::ShowAdminRow(
								$optionCode,
							    $arOption,
								$optionVal,
							    $module_id,
							    $optionsSiteID
							);?>
						</div>
					</div>
				</div>
				<?

			}
		}
	}

	public static function ShowAdminRow($optionCode, $arOption, $optionVal, $module_id, $optionsSiteID){
		$optionName = $arOption['TITLE'];
		$optionType = $arOption['TYPE'];
		$optionList = $arOption['ITEMS'];
		$optionDefault = $arOption['DEFAULT'];
		$optionChecked = $optionVal == 'Y' ? 'checked' : '';
		?>

		<?if($optionType == "note"):?>
				<div colspan="2" align="center">
    				<?=BeginNote('align="center"');?>
    					<?=$arOption["NOTE"]?>
    				<?=EndNote();?>
				</div>
		<?else:?>
				<div class="value_wrapper">
				<?if($optionType == "checkbox"):?>
					<input type="checkbox" id="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" name="<?=htmlspecialcharsbx($optionCode)?>" value="<?=$optionVal?>" <?=$optionChecked?>>
				<?elseif($optionType == "text" || $optionType == "password"):?>
					<input type="<?=$optionType?>" <?=($arOption['PLACEHOLDER'] ? "placeholder='".$arOption['PLACEHOLDER']."'" : '');?> <?=($arOption['DISABLED'] ? "disabled" : '');?> maxlength="255" value="<?=htmlspecialcharsbx($optionVal)?>" name="<?=htmlspecialcharsbx($optionCode)?>" <?=($optionCode == "password" ? "autocomplete='off'" : "")?>>
				<?elseif($optionType == "selectbox"):?>
					<?
					if(!is_array($optionList)) $optionList = (array)$optionList;
					$arr_keys = array_keys($optionList);
					?>
						<select data-site="<?=$optionsSiteID?>" name="<?=htmlspecialcharsbx($optionCode)?>">
						<?
							for($j = 0, $c = count($arr_keys); $j < $c; ++$j):?>
								<option value="<?=$arr_keys[$j]?>" <?if($optionVal == $arr_keys[$j]) echo "selected"?> <?=(isset($optionList[$arr_keys[$j]]['DISABLED']) ? 'disabled' : '');?>><?=htmlspecialcharsbx((is_array($optionList[$arr_keys[$j]]) ? $optionList[$arr_keys[$j]]["TITLE"] : $optionList[$arr_keys[$j]]))?></option>
							<?endfor;
						?>
						</select>
				<?elseif($optionType == "multiselectbox"):?>
					<?
					if(!is_array($optionList)) $optionList = (array)$optionList;
					$arr_keys = array_keys($optionList);
					$optionVal = explode(",", $optionVal);
					if(!is_array($optionVal)) $optionVal = (array)$optionVal;?>
					<select size="5" data-site="<?=$optionsSiteID?>" multiple name="<?=htmlspecialcharsbx($optionCode)?>[]" >
						<?for($j = 0, $c = count($arr_keys); $j < $c; ++$j):?>
							<option value="<?=$arr_keys[$j]?>" <?if(in_array($arr_keys[$j], $optionVal)) echo "selected"?>><?=htmlspecialcharsbx((is_array($optionList[$arr_keys[$j]]) ? $optionList[$arr_keys[$j]]["TITLE"] : $optionList[$arr_keys[$j]]))?></option>
						<?endfor;?>
					</select>
				<?elseif($optionType == "textarea"):?>
					<textarea rows="10" cols="70" name="<?=htmlspecialcharsbx($optionCode)?>"><?=htmlspecialcharsbx($optionVal)?></textarea>
				<?elseif($optionType == "statictext"):?>
					<?=htmlspecialcharsbx($optionVal)?>
				<?elseif($optionType == "statichtml"):?>
					<?=$optionVal?>
				<?endif;?>
				</div>
		<?endif;?>
	<?}
}
