<?php

class CRlibTime {
	
	static function currentDatetimeUtc(){
		$dtCurrentUtc = date("Y-m-d H:i:s", time()-date("Z"));
		return $dtCurrentUtc;
	}
	
	static function datetimeToTimestamp($dtDatetime){
		$iTimestamp = strtotime($dtDatetime);
		return (int)$iTimestamp;
	}
	
	static function currentDatetimeLocal(){
		$dtCurrent = date('Y-m-d H:i:s', time());
		return $dtCurrent;
	}
	
	static function timestampToDatetime($iTimestamp){
		
		$dtResult = date('Y-m-d H:i:s', time());
		
		$iTimestamp = (int)$iTimestamp;
		
		if($iTimestamp > 0){
			$dtResult = date('Y-m-d H:i:s.0', $iTimestamp);
		}
		
		return $dtResult;
	}

}
