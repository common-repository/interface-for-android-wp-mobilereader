<?php

session_start();

require_once (WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/class.wpandroidclient.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/class.utils.php');

class wp_android_client_ajax {
	
	static function exec_wp_android_client(){
		
		self::handleRequest(true);
		die();
	}
	
	static function nopriv_exec_wp_android_client(){
		
		self::handleRequest();
		
		die();
	}
	
	static function handleRequest($isLoggedIn = false){
		
		$Result = json_encode(array());
		
		$arPOST = array_merge($_POST,$_GET);
		$arPOST["_FILES"] = $_FILES;
		
		$WPAndroidClient = new ClassWPAndroidClient();
		$WPUtils = new ClassWPCUtils();
		
		$WPUtils->deMaskArrayFromPost($arPOST);
		
		$Request = $WPAndroidClient->startRequest($arPOST, $isLoggedIn);
		
		if($Request !== null)
		{
			$Result = $Request->execute($arPOST, $isLoggedIn);
		}
		
		echo $Result;
	}
	
	
}

		