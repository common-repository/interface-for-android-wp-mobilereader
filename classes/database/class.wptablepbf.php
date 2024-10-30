<?php

require_once (WP_ANDROID_CLIENT_PLUGIN_DIR . "/classes/class.wpcontract.php");

class ClassWPTablePbf {
	
	private $sTableName = "";
	
	public function __construct() {
		global $wpdb;
		
		$this->sTableName = $wpdb->prefix . WPContract::$TABLE_PREVENT_BRUTEFORCE;
		
	}
	
	public function addFailedLogin($PBFIP4ADDRESS,$PBFLOGIN){
		global $wpdb;
		
		$PBFID = $this->getLoginRowId($PBFLOGIN);
		$sSQL = "";

		if($PBFID){
			$sSQL = <<< SQL
				update {$this->sTableName}
				set PBFCOUNTER = PBFCOUNTER +1
				where PBFID = {$PBFID}
SQL;
		}  else {
			$sSQL = <<< SQL
				insert into {$this->sTableName}
				(PBFDATEADD, PBFIP4ADDRESS, PBFLOGIN,PBFCOUNTER)
				values
				(NOW(), "{$PBFIP4ADDRESS}", "{$PBFLOGIN}", 1)
SQL;
		}
		
		$wpdb->query($sSQL);
	}
	
	private function getLoginRowId($PBFLOGIN){
		
		global $wpdb;
		$PBFID = 0;
		
		$sSQL = <<< SQL
			select * from {$this->sTableName}
			where PBFLOGIN = "{$PBFLOGIN}"
SQL;
		
		$arPBFs = $wpdb->get_results($sSQL,ARRAY_A);
		
		if(is_array($arPBFs) && count($arPBFs) > 0){
			$PBFID = $arPBFs[0]["PBFID"];
		}
		
		return $PBFID;
	}
	
	public function isBruteForce($PBFIP4ADDRESS,$PBFLOGIN){
		
		global $wpdb;
		$PBFIP4ADDRESS_COUNT = 0;
		$PBFLOGIN_COUNT = 0;
		$bIsBruteForce = false;
		
		$sSQL = <<< SQL
			select * from {$this->sTableName}
			where PBFIP4ADDRESS = "{$PBFIP4ADDRESS}"
			or PBFLOGIN = "{$PBFLOGIN}"
SQL;
		
		$arPBFs = $wpdb->get_results($sSQL,ARRAY_A);
		
		foreach($arPBFs as $PBFID=>$arPBF){
			
			if($arPBF["PBFIP4ADDRESS"] == $PBFIP4ADDRESS){
				$PBFIP4ADDRESS_COUNT += $arPBF["PBFCOUNTER"];
			}
			
			if($arPBF["PBFLOGIN"] == $PBFLOGIN){
				$PBFLOGIN_COUNT += $arPBF["PBFCOUNTER"];
			}
			
		}
		
		if($PBFIP4ADDRESS_COUNT > WPContract::$COUNT_PER_IP_PREVENT_BRUTEFORCE
				|| $PBFLOGIN_COUNT > WPContract::$COUNT_PER_LOGIN_PREVENT_BRUTEFORCE){
			$bIsBruteForce = true;
		}
		
		return $bIsBruteForce;		
	}
	
	public function clearTable(){
		global $wpdb;
		
		$iMinutes = WPContract::$OBSERVATION_TIME_PREVENT_BRUTEFORCE;
		
		$sSQL = <<< SQL
			delete from {$this->sTableName}
			where PBFDATEADD <= DATE_SUB(NOW(), INTERVAL {$iMinutes} MINUTE)
SQL;
		$wpdb->query($sSQL);
		
	}
	
}
