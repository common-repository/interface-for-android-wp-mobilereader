<?php


class ClassWpCurl {
	
	private $ch;
	private $arPost;
	
	public function __construct($sUrl = null) {
		
		if(!$sUrl){
			$sUrl = WP_ANDROID_CLIENT_SERVER_URL;
		}
		
		$this->ch = curl_init($sUrl);
		$this->setDefaultOptions();
	}
	
	private function setDefaultOptions(){
		$this->setOption(CURLOPT_POST, 1);
		$this->setOption(CURLOPT_POST, 1);
		$this->setOption(CURLOPT_RETURNTRANSFER, 1);
		$this->setOption(CURLOPT_FOLLOWLOCATION, 1);
		$this->setOption(CURLOPT_SSL_VERIFYHOST, 0);
		$this->setOption(CURLOPT_SSL_VERIFYPEER, 0);
	}
	
	public function send(){
		$this->setOption(CURLOPT_POSTFIELDS, $this->arPost);
		$jsonResponse = curl_exec($this->ch);
		
		if($jsonResponse === FALSE){
			// erstmal nix
			$sFehler = curl_error($this->ch);
		}
		
		return $jsonResponse;
	}
	
	public function addPostvar($sPostvar, $sValue){
		$this->arPost[$sPostvar] = $sValue;
	}
	
	public function setOption($curl_option,$curl_value){
		curl_setopt($this->ch, $curl_option, $curl_value);
	}
	
}
