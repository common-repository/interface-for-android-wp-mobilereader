<?php

/**
 * 
 * "Hallo"
 * 
 * http://www.trames.de/wp-admin/admin-ajax.php?action=exec_wp_android_client&cmd=wpc_ping
 */

class wpc_ping implements RequestCmdInterface{
	
	public function execute($arPOST, $isLoggedIn) {
		$Result = array("response"=>"wpc_pong");
		$Result = json_encode($Result);
		
		return $Result;
	}

}
