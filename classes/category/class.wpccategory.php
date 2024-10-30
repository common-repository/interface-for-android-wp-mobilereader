<?php

class ClassWPCCategory {

	public function getCategoriesAsList($objCategories){
		
		$sKatList = "";
		
		if($objCategories){
			foreach($objCategories as $objCategorie){
			
				if(strlen($sKatList))
					$sKatList .= ",";
			
				$sKatList .= $objCategorie->cat_ID;
			}
		}
		
		return $sKatList;
		
	}
	
	public function setCategorieDataFields($arCategorie)
	{
		$arResult = array();
		
		if(is_array($arCategorie) && count($arCategorie))
		{
		
			$arResult = array();
			$arResult["cat_ID"] = $arCategorie["cat_ID"];
			$arResult["category_description"] = $arCategorie["category_description"];
			$arResult["cat_name"] = $arCategorie["cat_name"];
			$arResult["category_parent"] = $arCategorie["category_parent"];
		}
		
		return $arResult;
	}
	
	public function getCategoriesData($iPostId){
		
		$objCategories = get_the_category($iPostId);
		$sKatList = $this->getCategoriesAsList($objCategories);
		
		return $sKatList;
	}
	
}
