<?php

require_once (WP_ANDROID_CLIENT_PLUGIN_DIR . "/rklibs/class.rlibtime.php");

class ClassWpCron {

	private $curl;
	
	public function setCurl($curl){
		$this->curl = $curl;
	}
	
	public function isTimeToRequest($dtTarget){
		$bIsTime = false;
		
		$iTarget = CRlibTime::datetimeToTimestamp($dtTarget);
		
		$dtNow = CRlibTime::currentDatetimeUtc();
		$iNow = CRlibTime::datetimeToTimestamp($dtNow);
		
		if($iNow >$iTarget){
			$bIsTime = true;
		}
		
		
		return $bIsTime;
	}


	public function activate(){
		if ( !wp_next_scheduled('wp_android_client_cron') ) {
			wp_schedule_event(time(), 'every_1_min', 'wp_android_client_cron');
		}
	}
	
	public function deactivate(){
		wp_clear_scheduled_hook('wp_android_client_cron');
	}
	
	public function curlRequest(){
		
		if(!$this->curl){
			return;
		}
		
		$Response = $this->curl->send();
		
		return $Response;
	}


	public function filter_cron_schedules($arSchedules){
		
		$arSchedules['every_5_min'] = array( 
			'interval' => 300, // seconds
			'display'  => __( 'every_5_min' ) 
		);
	
		$arSchedules['every_1_min'] = array( 
			'interval' => 60, // seconds
			'display'  => __( 'every_1_min' ) 
		);
		
		return $arSchedules;
	}
	
	
}
