<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

/** @var \CAllMain $APPLICATION */
/** @var \SenderLetterEditComponent $component */
/** @var array $arParams */
/** @var array $arResult */
$containerId = 'bx-posting-letter-edit';

Extension::load("ui.buttons");
Extension::load("ui.buttons.icons");
Extension::load("ui.notification");
CJSCore::Init(array('admin_interface'));
?>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="adm-detail-content">
	<div class="adm-detail-title">Рассылка</div>
		<form method="post" action="<?=htmlspecialcharsbx($arResult['SUBMIT_FORM_URL'])?>" enctype="multipart/form-data">
		<?=bitrix_sessid_post()?>
		<div class="adm-detail-content-item-block">

			<div class="title" style="margin-top: 0;">Название:</div>
			<div class="groups_block block">
				<div class="item text">
					<div>
						<div class="inner_wrapper text">
							<div class="inner">
								<div class="value_wrapper">
									<input type="text" maxlength="255" value="<?=$arResult['ITEM']['TITLE']?>" name="TITLE">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="title">Картинка для рассылки:</div>
			<div class="groups_block block">
				<div class="item text">
					<div>
						<div class="inner_wrapper text">
							<div class="inner">
								<div class="value_wrapper">
									<?
									echo \Bitrix\Main\UI\FileInput::createInstance(array(
											"name" => 'FILE',
											"description" => true,
											"upload" => true,
											"allowUpload" => "F",
											"medialib" => true,
											"fileDialog" => true,
											"cloud" => true,
											"delete" => true,
											"maxCount" => 1
									))->show(
											$arResult['ITEM']['FILE_ID'],
											intval($arResult['ITEM']['FILE_ID']) ? true : false
										);
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="title">Текст рассылки:</div>
			<div class="groups_block block">
				<div class="item text">
					<div>
						<div class="inner_wrapper text">
							<div class="inner">
								<div class="value_wrapper">
									<?
									$LHE = new \CHTMLEditor;
									$code = 'TEXT';
									$LHE->Show([
										'name' => $code,
										'id' => $code . '_id',
										'inputName' => $code,
										'content' => $arResult['ITEM'][$code],
										'width' => '100%',
										'minBodyWidth' => 350,
										'normalBodyWidth' => 555,
										'height' => '500',
										'bAllowPhp' => false,
										'limitPhpAccess' => false,
										'autoResize' => true,
										'autoResizeOffset' => 40,
										'useFileDialogs' => false,
										'saveOnBlur' => true,
										'showTaskbars' => false,
										'showNodeNavi' => false,
										'askBeforeUnloadPage' => true,
										'bbCode' => false,
										'siteId' => SITE_ID,
										'controlsMap' => [
											['id' => 'Bold', 'compact' => true, 'sort' => 80],
											['id' => 'Italic', 'compact' => true, 'sort' => 90],
											['id' => 'Underline', 'compact' => true, 'sort' => 100],
											['id' => 'Strikeout', 'compact' => true, 'sort' => 110],
											['id' => 'RemoveFormat', 'compact' => true, 'sort' => 120],
											['id' => 'Color', 'compact' => true, 'sort' => 130],
											['id' => 'FontSelector', 'compact' => false, 'sort' => 135],
											['id' => 'FontSize', 'compact' => false, 'sort' => 140],
											['separator' => true, 'compact' => false, 'sort' => 145],
											['id' => 'OrderedList', 'compact' => true, 'sort' => 150],
											['id' => 'UnorderedList', 'compact' => true, 'sort' => 160],
											['id' => 'AlignList', 'compact' => false, 'sort' => 190],
											['separator' => true, 'compact' => false, 'sort' => 200],
											['id' => 'InsertLink', 'compact' => true, 'sort' => 210],
											['id' => 'InsertImage', 'compact' => false, 'sort' => 220],
											['id' => 'InsertVideo', 'compact' => true, 'sort' => 230],
											['id' => 'InsertTable', 'compact' => false, 'sort' => 250],
											['separator' => true, 'compact' => false, 'sort' => 290],
											['id' => 'Fullscreen', 'compact' => false, 'sort' => 310],
											['id' => 'More', 'compact' => true, 'sort' => 400]
										],
									]);?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div data-role="letter-buttons" style="<?=($arResult['SHOW_TEMPLATE_SELECTOR'] ? 'display: none;' : '')?>">
				<?
				$buttons = [];
				$buttons[] = ['TYPE' => 'save'];
				$buttons[] = ['TYPE' => 'cancel', 'LINK' => $arParams['PATH_TO_LIST']];
				$APPLICATION->IncludeComponent(
					"bitrix:ui.button.panel",
					"",
					array(
						'BUTTONS' => $buttons
					),
					false
				);
				?>
			</div>
		</div>
	</form>
</div>



