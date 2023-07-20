<?
namespace Oe\Telegrambot;

class Utils
{
	public static function priceFormat($price = NULL, $currency = NULL, $convert_currency = NULL){
		if($convert_currency){
			if($currency != $convert_currency){
				$priceStr = \CCurrencyLang::CurrencyFormat(
					\CCurrencyRates::ConvertCurrency($price, $currency, $convert_currency),
					$convert_currency,
					TRUE
				);
			}else{
				$priceStr = \CCurrencyLang::CurrencyFormat(
					$price,
					$currency,
					TRUE
				);
			}
		}else{
			$priceStr = \CCurrencyLang::CurrencyFormat(
				$price,
				$currency,
				TRUE
			);
		}
		return html_entity_decode($priceStr);
	}

    public static function getCallBackStr($callBackArray = [])
    {
        if(!is_array($callBackArray) || empty($callBackArray)) return false;

        $returnStr = '';
        foreach($callBackArray as $k=>$v)
            $returnStr .= $k . ':' . $v . ';';

        return $returnStr;
    }

    public static function isEmptyArray($arr = [])
    {
        if($arr == false) return true;
        if(count($arr) == 0) return true;

        $emptyValues = true;
        foreach($arr as $a)
            if($a) $emptyValues = false;

        return $emptyValues;
    }

    public static function getKeyboardRowButton($text = false, $callBack = '_')
    {
        return ['text' => $text, 'callback_data' => $callBack];
    }

    public static function setUserField($code = '', $value = '', $userid)
    {
        global $USER;
        $uid = $userid ? $userid : $USER->GetID();
        $user = new \CUser;
        $user->Update($uid, [$code => $value]);
    }


    public static function xml2array($xmlObject)
    {
        $out = [];

        foreach ( (array) $xmlObject as $index => $node )
            $out[$index] = ( is_object ( $node ) ) ? self::xml2array ( $node ) : $node;

        return $out;
    }
}