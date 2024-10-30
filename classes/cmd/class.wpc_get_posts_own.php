<?php

require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/class.utils.php');

class wpc_get_posts_own implements RequestCmdInterface{
	
	public function execute($arPOST, $isLoggedIn) {
		
		$arResult = array();
		
		if(!$isLoggedIn){
			$arResult['error'] = 'not_logged_in';
		}else{
		
			$Utils = new ClassWPCUtils();
			global $current_user;
						
			$arArgs = array();
		
			$Utils->AddKeyIfSet($arArgs	, 'posts_per_page', $arPOST,5);
			$Utils->AddKeyIfSet($arArgs	, 'offset', $arPOST,0);
			$Utils->AddKeyIfSet($arArgs	, 'category', $arPOST);
			$Utils->AddKeyIfSet($arArgs	, 'category_name', $arPOST);
			$Utils->AddKeyIfSet($arArgs	, 'orderby', $arPOST,'date');
			$Utils->AddKeyIfSet($arArgs	, 'order', $arPOST,'DESC');
//			$Utils->AddKeyIfSet($arArgs	, 'include', $arPOST);
//			$Utils->AddKeyIfSet($arArgs	, 'exclude', $arPOST);
			$Utils->AddKeyIfSet($arArgs	, 'meta_key', $arPOST);
			$Utils->AddKeyIfSet($arArgs	, 'meta_value', $arPOST);
			$arArgs['post_type'] = 'post';
			$Utils->AddKeyIfSet($arArgs	, 'post_mime_type', $arPOST);
			$Utils->AddKeyIfSet($arArgs	, 'post_parent', $arPOST);
			$arArgs['author'] = $current_user->ID;
			$arArgs['post_status'] ='publish,private';
			$Utils->AddKeyIfSet($arArgs , 'suppress_filters',$arPOST,true);
			$arResult = get_posts($arArgs);
		}
		
		$jsonResult = json_encode($arResult);
		
		return ""; //$jsonResult;
	}
}
