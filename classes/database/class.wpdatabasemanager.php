<?php

require_once (WP_ANDROID_CLIENT_PLUGIN_DIR . "/classes/class.wpcontract.php");

class ClassWPDatabaseManager {
	
	public function create(){
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		$tablePreventBruteforce = $wpdb->prefix . WPContract::$TABLE_PREVENT_BRUTEFORCE;
		
				$sSQL = <<< SQL
			CREATE TABLE {$tablePreventBruteforce} (
			PBFID int NOT NULL AUTO_INCREMENT,
			PBFDATEADD datetime NOT NULL,
			PBFIP4ADDRESS varchar(24),
			PBFLOGIN varchar(64),
			PBFCOUNTER int,
			UNIQUE KEY PBFID (PBFID),
			KEY PBFIP4ADDRESS (PBFIP4ADDRESS)
			) {$charset_collate};
SQL;
			
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sSQL);
	}
	
	public function remove(){
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		$tablePreventBruteforce = $wpdb->prefix . WPContract::$TABLE_PREVENT_BRUTEFORCE;
		
		$sSQL = <<< SQL
			DROP TABLE IF EXISTS {$tablePreventBruteforce}
SQL;
		
		$wpdb->query($sSQL);
	}
	
	
	
}
