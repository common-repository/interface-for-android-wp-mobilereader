<?php

/**
 * 
 * Posts lesen - registrierter User
 * 
 * http://www.trames.de/wp-admin/admin-ajax.php?action=exec_wp_android_client&cmd=wpc_get_post_single
 */


require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/class.utils.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/post/class.wpcpost.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/post/class.wpcauthor.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/category/class.wpccategory.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/user/class.wpusercapabilities.php');

class wpc_get_post_single implements RequestCmdInterface{
	
	private $Utils = null;
	private $WPCPost = null;
	private $WPCCategory = null;
	private $WPCAuthor = null;
	private $WPUserCapabilities = null;
	
	public function __construct() {
		$this->Utils = new ClassWPCUtils();
		$this->WPCPost = new ClassWPCPost();
		$this->WPCCategory = new ClassWPCCategory();
		$this->WPCAuthor = new ClassWPCAuthor();
		$this->WPUserCapabilities = new ClassWPUserCapabilities();
	}
	
	public function execute($arPOST, $isLoggedIn) {
		
		global $current_user;
		$arResult = array();
		
		$iPostId = $arPOST[WPContract::$COMMUNICATION_FIELD_POST_ONLINE_ID];
		
		if($iPostId){
			
			$arReadedPost = get_post($iPostId, ARRAY_A);
			if(is_array($arReadedPost) && count($arReadedPost)){
				if($arReadedPost["post_status"] == WPContract::$POST_STATE_PUBLISH){
					// freigeschaltet, ausgabe
					$arResult = $this->createResponse($arReadedPost);
				}else{
					if($isLoggedIn){

						if($this->WPUserCapabilities->hasPostUpdateCapability($iPostId, $arReadedPost["post_status"])){
							// mein Post oder habe recht, ausgabe
							$arResult = $this->createResponse($arReadedPost);
						}
					}
				}
			}
		
		}
		
		$jsonResult = json_encode($arResult);
		
		return $jsonResult;
	}
	
	private function createResponse($arReadedPost)
	{
		$arPost = array();
		$arPost = array_merge($arPost,$this->WPCPost->getPostsDataFields($arReadedPost));
		$arPost = array_merge($arPost,$this->WPCPost->getImageData($arReadedPost));
		$arPost["post_category"] = $this->WPCCategory->getCategoriesData($arReadedPost["ID"]);
		$arPost = array_merge($arPost,$this->WPCAuthor->getAuthorData($arReadedPost));
		
		$arResponse = array();
		
		$arResponse["posts"] = array($arPost);
		return $arResponse;
	}
	

	
	
}
