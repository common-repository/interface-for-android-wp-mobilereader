<?php

require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/user/class.wpusercapabilities.php');

class wpc_check_user implements RequestCmdInterface{
	
	public function execute($arPOST, $isLoggedIn) {
		global $current_user;
		$arResult = array();
		$arResult["loggedin"] = "0";
		$arCapabilities = array();
		
		if($isLoggedIn){
			
			$WPUserCapabilities = new ClassWPUserCapabilities();
			
			if(get_option("wpc_allow_editing") == "1"){
				// editing is on
				if($WPUserCapabilities->isEditingByRoleAllowed()){
					// role current user is allowed
					$arCapabilities = $current_user->allcaps;
				}
			}
			
			$arResult["capabilities"] = $arCapabilities;
			$arResult["userdata"] = $this->composeUserData();
			$arResult["loggedin"] = "1";
		}
		
		$jsonResult = json_encode($arResult);
		
		return $jsonResult;
	}
	
	private function composeUserData(){
		global $current_user;
		
		$arUserdata = array();
		$arUserdata["ID"] = $current_user->data->ID;
		$arUserdata["user_login"] = $current_user->data->user_login;
		$arUserdata["user_nicename"] = $current_user->data->user_nicename;
		$arUserdata["display_name"] = $current_user->data->display_name;
		
		return $arUserdata;
	}

}
