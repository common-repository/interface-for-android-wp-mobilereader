<?php

/**
 * 
 * Posts lesen - registrierter User
 * 
 * http://www.trames.de/wp-admin/admin-ajax.php?action=exec_wp_android_client&cmd=wpc_get_posts_user
 */


require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/class.utils.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/user/class.wpusercapabilities.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/post/class.wpcpost.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/category/class.wpccategory.php');

class wpc_get_posts_user implements RequestCmdInterface{
	
	private $Utils = null;
	private $WPUserCapabilities = null;
	private $WPCPost = null;
	private $WPCCategory = null;
	
	public function __construct() {
		$this->Utils = new ClassWPCUtils();
		$this->WPUserCapabilities = new ClassWPUserCapabilities();
		$this->WPCPost = new ClassWPCPost();
		$this->WPCCategory = new ClassWPCCategory();
	}
	
	public function execute($arPOST, $isLoggedIn) {
		
		$arResult = array();
		$jsonResult = json_encode($arResult);
		
		if(!$isLoggedIn)
			return $jsonResult;
		
		
		$arArgs = array();
		if($this->isArgsArray($arArgs,$arPOST)){
			
			$objResultRoh = get_posts($arArgs);
			$arResultRoh = json_decode(json_encode($objResultRoh), true);
			
			$arResult = $this->customizeData($arResultRoh);
			
		}

		// ausgelagert in extra GET
//		if($arPOST["get_kat"] == 1)
//		{
//			$this->attachCategories($arResult);
//		}
		$jsonResult = json_encode($arResult);
		
		return $jsonResult;
	}
	
	private function isArgsArray(&$arArgs,$arPOST){
		
		$bIsArgsArray = true;
		global $current_user;
		
		$this->Utils->AddKeyIfSet($arArgs,'posts_per_page', $arPOST,5);
		$this->Utils->AddKeyIfSet($arArgs,'offset', $arPOST,0);
		$this->Utils->AddKeyIfSet($arArgs,'orderby', $arPOST,'date');
		$this->Utils->AddKeyIfSet($arArgs,'order', $arPOST,'DESC');
		
		$arArgs['suppress_filters'] = false;
		$arArgs["author"] = $current_user->ID;
		
		$sAllowedPostTypes = $this->WPUserCapabilities->getAllowedPostTypes();
		if($sAllowedPostTypes){
			$arArgs['post_type'] = $sAllowedPostTypes;
		}else{
			$bIsArgsArray = false;
		}
		
		$sAllowedPostStates = $this->WPUserCapabilities->getAllowedPostStates();
		if($sAllowedPostStates){
			$arArgs['post_status'] = $sAllowedPostStates;
		}  else {
			$bIsArgsArray = false;
		}
		
		return $bIsArgsArray;
	}
	
	private function customizeData($arResultRoh){
		
		$arResult["posts"] = array();
		
		foreach($arResultRoh as $arSinglePost){
			
			$arSinglePost = $this->setPostsDataFields($arSinglePost);
			
			$arSinglePost = $this->attachData($arSinglePost);
		
			$arSinglePost = $this->fixEntries($arSinglePost);
		
			array_push($arResult["posts"], $arSinglePost);
		}
		return $arResult;
	}
	
	private function setPostsDataFields($arSinglePost)
	{
		$arResult = array();
		$arResult["ID"] = $arSinglePost["ID"];
		$arResult["post_title"] = $arSinglePost["post_title"];
		$arResult["post_content"] = $arSinglePost["post_content"];
		$arResult["post_date"] = $arSinglePost["post_date"];
		$arResult["post_modified"] = $arSinglePost["post_modified"];
		$arResult["post_author"] = $arSinglePost["post_author"];
		$arResult["post_status"] = $arSinglePost["post_status"];
		
		return $arResult;
	}
	
	private function attachCategories(&$arResultRoh){
		
		$arArgs = array(
			"type" => "post",
			"orderby" => "name",
			"order" => "ASC",
			"hide_empty" => 0,
			"hierarchical" => 1,
			"taxonomy" => "category"
		);
		
		$objCategoriesRoh = get_categories($arArgs);
		$arCategoriesRoh = json_decode(json_encode($objCategoriesRoh), true);
		$arCategories = $this->customizeCategoriesData($arCategoriesRoh);
		
		$arResultRoh["kategories"] = $arCategories;
		
	}
	
	private function customizeCategoriesData($arCategoriesRoh){
		$arCategories = array();
		
		foreach($arCategoriesRoh as $arCategorie){
			
			$arCategorie = $this->WPCCategory->setCategorieDataFields($arCategorie);
			$arCategories[] = $arCategorie;
		}
		
		return $arCategories;
	}
	
	private function fixEntries($arSingePost){
		foreach($arSingePost as $key => $value){
			
			// entities in Zeichen umwandeln
			$arSingePost[$key] = html_entity_decode($value);
			$arSingePost[$key] = html_entity_decode($arSingePost[$key],ENT_QUOTES, "UTF-8");
			
		}
		
		// datetime zu timestamp
		$arSingePost["post_date"] = strtotime($arSingePost["post_date"]);
		$arSingePost["post_modified"] = strtotime($arSingePost["post_modified"]);
		
		return $arSingePost;
	}
	
	private function attachData($arSinglePost){
		
		$arImages = $this->WPCPost->getImages($arSinglePost["ID"]);
		$arSinglePost = array_merge($arSinglePost, $arImages);
		
		$arSinglePost["post_category"] = $this->attachCategoriesId($arSinglePost["ID"]);
		
		$this->setAuthorData($arSinglePost);
		
		return $arSinglePost;
	}
	
	private function attachCategoriesId($iPostId){
		
		$objCategories = get_the_category($iPostId);
		$sKatList = $this->WPCCategory->getCategoriesAsList($objCategories);
		
		return $sKatList;
	}
	
	private function setAuthorData(&$arSinglePost){
		
		$objUserdata = get_userdata($arSinglePost["post_author"]);
		
		$arSinglePost["post_author_user_login"] = $objUserdata->data->user_login;
		$arSinglePost["post_author_user_nicename"] = $objUserdata->data->user_nicename;
		$arSinglePost["post_author_display_name"] = $objUserdata->data->display_name;
		
	}
	
}
