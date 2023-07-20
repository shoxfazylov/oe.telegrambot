<?
use \Bitrix\Iblock;

$filterIblock = COption::GetOptionString($mid, 'bot_catalog_iblock') !== NULL ? COption::GetOptionString($mid, 'bot_catalog_iblock') : $iblocks[0];
if((int)$filterIblock > 0)
{
    $arProperty = array();
    $arProperty_N = array();
    $arProperty_X = array();
    $arProperty_F = array();

    $arInfo = \CCatalogSKU::GetInfoByProductIBlock($filterIblock);
    if(!empty($arInfo)){
        $propertyIterator = Iblock\PropertyTable::getList(array(
            'select' => array('ID', 'IBLOCK_ID', 'NAME', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE', 'LINK_IBLOCK_ID', 'USER_TYPE', 'SORT'),
            'filter' => array('=IBLOCK_ID' => $arInfo['IBLOCK_ID'], '=ACTIVE' => 'Y'),
            'order' => array('SORT' => 'ASC', 'NAME' => 'ASC')
        ));

        while ($property = $propertyIterator->fetch())
        {
            $propertyCode = (string)$property['CODE'];
            if ($propertyCode == '')
                $propertyCode = $property['ID'];
            $propertyName = '['.$propertyCode.'] '.$property['NAME'];

            if ($property['PROPERTY_TYPE'] != Iblock\PropertyTable::TYPE_FILE)
            {
                $arProperty[$propertyCode . '|' . $property['NAME']] = $propertyName;

                if ($property['MULTIPLE'] == 'Y')
                    $arProperty_X[$propertyCode] = $propertyName;
                elseif ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_LIST)
                    $arProperty_X[$propertyCode] = $propertyName;
                elseif ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_ELEMENT && (int)$property['LINK_IBLOCK_ID'] > 0)
                    $arProperty_X[$propertyCode] = $propertyName;
            }
            else
            {
                if ($property['MULTIPLE'] == 'Y')
                    $arProperty_F[$propertyCode] = $propertyName;
            }

            if ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_NUMBER)
                $arProperty_N[$propertyCode] = $propertyName;
        }
        unset($propertyCode, $propertyName, $property, $propertyIterator);

        $offerProps = $arProperty;
    }else{
        $offerProps = [];
    }
}
else
{
    $offerProps = [];
}