<?php

require_once (WP_ANDROID_CLIENT_PLUGIN_DIR . "/classes/database/class.wptablepbf.php");

class wpc_login implements RequestCmdInterface{
	
	private $WPTablePbf = null;
	
	public function __construct() {
		$this->WPTablePbf = new ClassWPTablePbf();
	}
	
	public function execute($arPOST, $isLoggedIn) {

		$userCreds = array();
		$Result = json_encode(array());
		
		if(isset($_SERVER["PHP_AUTH_USER"]) && isset($_SERVER["PHP_AUTH_PW"])){
			$userCreds['user_login'] = $_SERVER["PHP_AUTH_USER"];
			$userCreds['user_password'] = $_SERVER["PHP_AUTH_PW"];
		}elseif(isset ($_SERVER["HTTP_AUTHORIZATION"])){
			$sDataEncoded = base64_decode($_SERVER["HTTP_AUTHORIZATION"]);
			list($userCreds['user_login'],$userCreds['user_password']) = explode(":", $sDataEncoded);
		}elseif (isset($arPOST[WPContract::$USER_AUTH_KEY])){
			$sDataEncoded = base64_decode($arPOST[WPContract::$USER_AUTH_KEY]);
			list($userCreds['user_login'],$userCreds['user_password']) = explode(":", $sDataEncoded);
		}
		
		$userCreds['remember'] = true;
		$sIpAddress = $this->getClientIp();
		
		if(!$this->isBruteForce($userCreds, $sIpAddress)){
		
			$user = wp_signon($userCreds);
		
			
			if(is_wp_error($user)){
				$arError = array('error'=>$user->get_error_code());
				$Result = json_encode($arError);
			
				$this->WPTablePbf->addFailedLogin($sIpAddress, $userCreds['user_login']);
			
			}else{
				$Result = json_encode($user);
			}
		}
		return $Result;
	}
	
	private function isBruteForce($arUserCreds,$sIpAddress){
		$this->WPTablePbf->clearTable();
		
		$bIsBruteForce = $this->WPTablePbf->isBruteForce($sIpAddress, $arUserCreds['user_login']);
		return $bIsBruteForce;
	}
	
	private function getClientIp(){
		
		$sIpAdresse = "undefined";
		
		if(isset($_SERVER['HTTP_CLIENT_IP']) && strlen($_SERVER['HTTP_CLIENT_IP'])){
			$sIpAdresse = $_SERVER['HTTP_CLIENT_IP'];
		}elseif(isset($_SERVER['HTTP_X_FORWARDE‌​D_FOR']) && strlen($_SERVER['HTTP_X_FORWARDE‌​D_FOR'])){
			$sIpAdresse = $_SERVER['HTTP_X_FORWARDE‌​D_FOR'];
		}elseif(isset($_SERVER['REMOTE_ADDR']) && strlen($_SERVER['REMOTE_ADDR'])){
			$sIpAdresse = $_SERVER['REMOTE_ADDR'];
		}
		
		return $sIpAdresse;
	}

}
