<?php

/**
 * 
 * Posts lesen - registrierter User
 * 
 * http://www.trames.de/wp-admin/admin-ajax.php?action=exec_wp_android_client&cmd=wpc_update_post_user
 */


require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/class.utils.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/user/class.wpusercapabilities.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/post/class.wpcpost.php');
require_once (ABSPATH . 'wp-admin/includes/image.php');

class wpc_update_post_user implements RequestCmdInterface{
	
	private $Utils = null;
	private $WPUserCapabilities = null;
	private $WPCPost = null;
	
	public function __construct() {
		$this->Utils = new ClassWPCUtils();
		$this->WPUserCapabilities = new ClassWPUserCapabilities();
		$this->WPCPost = new ClassWPCPost();
	}
	
	public function execute($arPOST, $isLoggedIn) {
		
		$arResult =array();
		$arResult["post_accepted"] = 0;
		$arResult["files_saved"] = 0;
		$arResult["images"] = array();
		$arResult["ID"] = 0;
		
		if(!$isLoggedIn)
			return json_encode (array());
		
		$iPostId = $arPOST[WPContract::$COMMUNICATION_FIELD_POST_ONLINE_ID];
		$this->fixPostArray($arPOST);
		
		$arPostAlt = get_post($iPostId, ARRAY_A);
		if(is_array($arPostAlt) && count($arPostAlt)){
			// existiert
			if($this->WPUserCapabilities->hasPostUpdateCapability($iPostId, $arPostAlt["post_status"])){
				// User darf, update
				$arResult["post_accepted"] = 1;
				$this->updatePost($arPOST);
			}
		}else{
			// neu
			$arResult["post_accepted"] = 1;
			$iPostId = $this->insertPost($arPOST);
		}
		
		if($arResult["post_accepted"] && $iPostId){
			$arPOST[WPContract::$COMMUNICATION_FIELD_POST_ONLINE_ID] = $iPostId;
			$arUploadedAttachments = $this->handleUploadedFiles($arPOST);
			
			$arResult["files_saved"] = count($arUploadedAttachments);
			$arResult["ID"] = $iPostId;
			
			$arResult["images"] = $this->WPCPost->getImages($iPostId);
			
		}
		
		$jsonResult = json_encode($arResult);
		
		return $jsonResult;
	}
	
	private function fixPostArray(&$arPOST){
		
		$arPOST = $this->Utils->trimArray($arPOST);
		
		// kommaseparierte Liste zu array
		$arCategory = array_map("trim", explode(",", $arPOST[WPContract::$COMMUNICATION_FIELD_POST_CATEGORY]));
		$arPOST[WPContract::$COMMUNICATION_FIELD_POST_CATEGORY] = $arCategory;
		
		// ob User publizieren darf
		$sPostStatus = $arPOST[WPContract::$COMMUNICATION_FIELD_POST_STATUS];
		if($sPostStatus == WPContract::$POST_STATE_PUBLISH
				&& !$this->WPUserCapabilities->hasCapability(WPContract::$CAPABILITY_PUBLISH_POSTS)){
			$arPOST[WPContract::$COMMUNICATION_FIELD_POST_STATUS] = WPContract::$POST_STATE_DRAFT;
		}
		
		// ob User Dateien hochladen darf
		if(count($arPOST["_FILES"]) 
				&& !$this->WPUserCapabilities->hasCapability(WPContract::$CAPABILITY_UPLOAD_FILES)){
			$arPOST["_FILES"] = array();
		}
		
	}
	
	private function insertPost($arPOST){
		$arInsertPost = array(
			WPContract::$COMMUNICATION_FIELD_POST_TITLE => $arPOST[WPContract::$COMMUNICATION_FIELD_POST_TITLE],
			WPContract::$COMMUNICATION_FIELD_POST_CONTENT => $arPOST[WPContract::$COMMUNICATION_FIELD_POST_CONTENT],
			WPContract::$COMMUNICATION_FIELD_POST_STATUS => $arPOST[WPContract::$COMMUNICATION_FIELD_POST_STATUS]
		);
		
		$iPostId = wp_insert_post($arInsertPost,$error);
		
		return $iPostId;
	}
	
	private function updatePost($arPOST){
		$arUpdatePost = array(
			WPContract::$COMMUNICATION_FIELD_POST_ONLINE_ID => $arPOST[WPContract::$COMMUNICATION_FIELD_POST_ONLINE_ID],
			WPContract::$COMMUNICATION_FIELD_POST_TITLE => $arPOST[WPContract::$COMMUNICATION_FIELD_POST_TITLE],
			WPContract::$COMMUNICATION_FIELD_POST_CONTENT => $arPOST[WPContract::$COMMUNICATION_FIELD_POST_CONTENT],
			WPContract::$COMMUNICATION_FIELD_POST_STATUS => $arPOST[WPContract::$COMMUNICATION_FIELD_POST_STATUS],
			WPContract::$COMMUNICATION_FIELD_POST_CATEGORY => $arPOST[WPContract::$COMMUNICATION_FIELD_POST_CATEGORY]
		);
		
		wp_update_post($arUpdatePost);
	}
	
	private function handleUploadedFiles($arPOST){
		
		$arFILES = $arPOST["_FILES"];
		
		if(!$arPOST[WPContract::$COMMUNICATION_FIELD_POST_ONLINE_ID])
			return;
		
		if($arPOST[WPContract::$COMMUNICATION_FIELD_POST_STATE_IMAGE_FEATURED] == WPContract::$FEATURED_IMAGE_STATE_DELETED){
			
			$this->WPCPost->removeFeaturedImage($arPOST[WPContract::$COMMUNICATION_FIELD_POST_ONLINE_ID]);
		}
		
		if(!is_array($arFILES) || count($arFILES) == 0)
			return;
		
		$arUploadedAttachments = array();
		foreach($arFILES as $sFileType => $arFILE){

			$arOverride = array("test_form" => false);
			
			$arMoved = wp_handle_upload($arFILE,$arOverride);
			
			if(is_array($arMoved) && !isset($arMoved["error"])){
				
				$arFileData = array(
					"guid" => $arMoved["url"],
					"post_mime_type" => $arMoved["type"],
					"post_title" => "Image_" . time(),
					"post_status" => "inherit",
					"post_content" => ''
				);
				
				$sFileName = basename($arMoved["file"]);
				$iAttachId = wp_insert_attachment($arFileData,$arMoved["file"]);
				$arAttachData = wp_generate_attachment_metadata($iAttachId, $arMoved["file"]);
				wp_update_attachment_metadata($iAttachId, $arAttachData);
				
				set_post_thumbnail($arPOST[WPContract::$COMMUNICATION_FIELD_POST_ONLINE_ID], $iAttachId);
				
				$arUploadedAttachments[$sFileType] = $iAttachId;
			}
		}
		
		return $arUploadedAttachments;
	}
}
