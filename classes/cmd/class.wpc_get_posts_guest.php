<?php

/**
 * 
 * Öffentlichen Posts lesen
 * 
 * http://www.trames.de/wp-admin/admin-ajax.php?action=exec_wp_android_client&cmd=wpc_get_posts_guest
 */


require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/class.utils.php');

class wpc_get_posts_guest implements RequestCmdInterface{
	
	public function execute($arPOST, $isLoggedIn) {
		$Utils = new ClassWPCUtils();
		
		$arResult = array();
		
		$arArgs = array();
		
		$Utils->AddKeyIfSet($arArgs	, 'posts_per_page', $arPOST,5);
		$Utils->AddKeyIfSet($arArgs	, 'offset', $arPOST,0);
		$Utils->AddKeyIfSet($arArgs	, 'category', $arPOST);
		$Utils->AddKeyIfSet($arArgs	, 'category_name', $arPOST);
		$Utils->AddKeyIfSet($arArgs	, 'orderby', $arPOST,'date');
		$Utils->AddKeyIfSet($arArgs	, 'order', $arPOST,'DESC');
//		$Utils->AddKeyIfSet($arArgs	, 'include', $arPOST);
//		$Utils->AddKeyIfSet($arArgs	, 'exclude', $arPOST);
		$Utils->AddKeyIfSet($arArgs	, 'meta_key', $arPOST);
		$Utils->AddKeyIfSet($arArgs	, 'meta_value', $arPOST);
		$arArgs['post_type'] = 'post';
		$Utils->AddKeyIfSet($arArgs	, 'post_mime_type', $arPOST);
		$Utils->AddKeyIfSet($arArgs	, 'post_parent', $arPOST);
		$Utils->AddKeyIfSet($arArgs	, 'author', $arPOST);
		$arArgs['post_status'] ='publish';
		$Utils->AddKeyIfSet($arArgs , 'suppress_filters',$arPOST,true);
		
		$objResultRoh = get_posts($arArgs);
		$arResultRoh = json_decode(json_encode($objResultRoh), true);
		
		foreach($arResultRoh as $arSinglePost){
			$arSinglePost = $this->attachData($arSinglePost);
			
			$arSinglePost = $this->fixEntries($arSinglePost);
			
			array_push($arResult, $arSinglePost);
		}
		$jsonResult = json_encode($arResult);
		
		return $jsonResult;
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
		
		$featured_image = wp_get_attachment_url(get_post_thumbnail_id($arSinglePost["ID"]));
		$arSinglePost["featured_image"] = $featured_image;

		$featured_image_thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($arSinglePost["ID"]),"thumbnail",false);
		$arSinglePost["featured_image_thumbnail"] = $featured_image_thumbnail[0];

		$featured_image_medium = wp_get_attachment_image_src(get_post_thumbnail_id($arSinglePost["ID"]),"medium",false);
		$arSinglePost["featured_image_medium"] = $featured_image_medium[0];
		
		$featured_image_large = wp_get_attachment_image_src(get_post_thumbnail_id($arSinglePost["ID"]),"large",false);
		$arSinglePost["featured_image_large"] = $featured_image_large[0];
		
		$featured_image_full = wp_get_attachment_image_src(get_post_thumbnail_id($arSinglePost["ID"]),"full",false);
		$arSinglePost["featured_image_full"] = $featured_image_full[0];
		
		$this->setAuthorData($arSinglePost);
		
		return $arSinglePost;
	}
	
	private function setAuthorData(&$arSinglePost){
		
		$objUserdata = get_userdata($arSinglePost["post_author"]);
		
		$arSinglePost["post_author_user_login"] = $objUserdata->data->user_login;
		$arSinglePost["post_author_user_nicename"] = $objUserdata->data->user_nicename;
		$arSinglePost["post_author_display_name"] = $objUserdata->data->display_name;
		
	}
	
}
