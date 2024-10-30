<?php

/*
 * error_log
 */


require_once (WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/class.wpcontract.php');

class ClassWPCUtils {
	
	public function AddKeyIfSet(&$arResult,$key,$arPOST,$defValue = null){
		if(isset($arPOST[$key]) && strlen($arPOST[$key])){
			$arResult[$key] = $arPOST[$key];
		}else{
			if($defValue !== null)
				$arResult[$key] = $defValue;
		}
		
	}
	
	public function trimArray($arQuelle){
		$arZiel = array();
		
		foreach($arQuelle as $key=>$value){
			if(is_array($value)){
				$arZiel[$key] = $value;
			}else{
				$arZiel[$key] = trim($value);
			}
		}
			
		return $arZiel;
		
	}
	
	public function deMaskArrayFromPost(&$arPOST){
		
		$arKeys = array_keys($arPOST);
		$arKeysToRemove = array();
		
		foreach($arKeys as $sKey){
			
			if(is_array($arPOST[$sKey])){
				$this->deMaskArrayFromPost($arPOST[$sKey]);
			}
			
			if(strpos($sKey, WPContract::$COMMUNICATION_FIELD_MASK_POST_FIELD_NAME) === 0){
				$sKeyNew = str_replace(WPContract::$COMMUNICATION_FIELD_MASK_POST_FIELD_NAME, "", $sKey);
				$arKeysToRemove[] = $sKey;
				$arPOST[$sKeyNew] = $arPOST[$sKey];
			}
		}
		
		foreach ($arKeysToRemove as $sKeyToRemove){
			unset($arPOST[$sKeyToRemove]);
		}
	}
	
	
}
