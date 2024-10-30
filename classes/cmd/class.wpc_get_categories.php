<?php

/**
 * 
 * Posts lesen - registrierter User
 * 
 * http://www.trames.de/wp-admin/admin-ajax.php?action=exec_wp_android_client&cmd=wpc_get_categories
 */


require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/class.utils.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/user/class.wpusercapabilities.php');

class wpc_get_categories implements RequestCmdInterface{
	
	private $Utils = null;
	private $WPUserCapabilities = null;
	private $arOutputCategories = null;
	
	public function __construct() {
		$this->Utils = new ClassWPCUtils();
		$this->WPUserCapabilities = new ClassWPUserCapabilities();
		$this->arOutputCategories = array();
	}
	
	public function execute($arPOST, $isLoggedIn) {
		
		$arCategoriesTree = $this->composeCategoriesTree(0, 0, 0);
		$arCategories = $this->customizeCategoriesData($this->arOutputCategories);
//		print_r($arCategoriesTree);
		
		if(count($arCategories))
		{
			$arResult["kategories"] = $arCategories;
		}
		
		$jsonResult = json_encode($arResult);
		
		return $jsonResult;
	}
	
	private function composeCategoriesTree($iParent, $iPath, $sPath){
		
		$arResult = array();
		
		$arArgs = array(
			"parent" => $iParent,
			"child_of" => $iParent,
			"type" => "post",
			"orderby" => "name",
			"order" => "ASC",
			"hide_empty" => 0,
			"hierarchical" => 1,
			"taxonomy" => "category"
		);
		
		$arCategories = get_categories($arArgs);
		
		if(is_array($arCategories) && count($arCategories)){
			foreach($arCategories as $objCategory){
				
				$arCategory = json_decode(json_encode($objCategory), true);
				
				$arCategory["sort_string"] = $sPath . $arCategory["cat_name"];
				$arCategory["path_string"] = $iPath . "-" . $arCategory["cat_ID"];
				
				
				$this->arOutputCategories[] = $arCategory;
				$arChildren = $this->composeCategoriesTree($arCategory["cat_ID"], $arCategory["path_string"],$arCategory["sort_string"]);
				$arResult[$arCategory["cat_ID"]] = $arCategory;
				$arResult[$arCategory["cat_ID"]]["CHILDREN"] = $arChildren;
			}
		}
		
		return $arResult;
	}
	
	
	private function customizeCategoriesData($arCategoriesRoh){
		$arCategories = array();
		
		foreach($arCategoriesRoh as $arCategorie){
			$arCategorie = $this->setCategorieDataFields($arCategorie);
			
			$arCategories[] = $arCategorie;
		}
		return $arCategories;
	}
	
	private function setCategorieDataFields($arCategorie)
	{
		$arResult = array();
		$arResult["cat_ID"] = $arCategorie["cat_ID"];
		$arResult["category_description"] = $arCategorie["category_description"];
		$arResult["cat_name"] = $arCategorie["cat_name"];
		$arResult["category_parent"] = $arCategorie["category_parent"];
		$arResult["path_string"] = $arCategorie["path_string"];
		$arResult["sort_string"] = $arCategorie["sort_string"];
		
		return $arResult;
	}
	
}
