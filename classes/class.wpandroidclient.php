<?php

require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/database/class.wpdatabasemanager.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/user/class.wpusercapabilities.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/cron/class.wpcron.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/curl/class.wpcurl.php');
require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/rklibs/class.rlibtime.php');

class ClassWPAndroidClient {
	
	public function startRequest($arPOST,$isLoggedIn){
		
		$Result = '';
		
		switch ($arPOST['cmd']) {
			case 'wpc_ping':
				require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/cmd/class.wpc_ping.php');
				return new wpc_ping();
				break;
			
			case 'wpc_login':
				require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/cmd/class.wpc_login.php');
				return new wpc_login();
				break;

			case 'wpc_get_posts_guest':
				require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/cmd/class.wpc_get_posts_guest.php');
				return new wpc_get_posts_guest();
				break;

			case 'wpc_get_posts_user':
				require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/cmd/class.wpc_get_posts_user.php');
				return new wpc_get_posts_user();
				break;
				
			case 'wpc_get_posts_own':
				require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/cmd/class.wpc_get_posts_own.php');
				return new wpc_get_posts_own();
				break;
				
			case 'wpc_check_user':
				require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/cmd/class.wpc_check_user.php');
				return new wpc_check_user();
				break;
				
			case 'wpc_update_post_user':
				require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/cmd/class.wpc_update_post_user.php');
				return new wpc_update_post_user();
				break;
			
			case 'wpc_get_categories':
				require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/cmd/class.wpc_get_categories.php');
				return new wpc_get_categories();
				break;
			
			case 'wpc_get_post_single':
				require_once(WP_ANDROID_CLIENT_PLUGIN_DIR . '/classes/cmd/class.wpc_get_post_single.php');
				return new wpc_get_post_single();
				break;
			
			default:
				return null;
				break;
		}
		
		return null;

	}
	
	static function plugin_activation(){
		$WPDatabaseManager = new ClassWPDatabaseManager();
		$WPDatabaseManager->create();
		$WpCron = new ClassWpCron();
		$WpCron->activate();
	}
	
	static function plugin_deactivation(){
		$WpCron = new ClassWpCron();
		$WpCron->deactivate();
	}
	
	static function cron_function($dtNextUpdate = 0){
		$WpCron = new ClassWpCron();
		
		$checkedAllowPublish = get_option("wpc_allow_publish");
		
		if(!$dtNextUpdate){
			$dtNextUpdate = get_option("wpc_publish_datetime_next_update");
		}
		
		if($checkedAllowPublish && $WpCron->isTimeToRequest($dtNextUpdate))
		{
			$sPublishUrl = get_option("wpc_publish_url", get_option("siteurl"));
			$sPublishTitle = get_option("wpc_publish_title", get_option("blogname"));
			$sPublishDesc = get_option("wpc_publish_desc", get_option("blogdescription"));

			$sPublishUrl = wp_strip_all_tags($sPublishUrl);
			$sPublishTitle = wp_strip_all_tags($sPublishTitle);
			$sPublishDesc = wp_strip_all_tags($sPublishDesc);
			
			$WpCurl = new ClassWpCurl();
			$WpCurl->addPostvar("cmd", "rws_update_site");
			$WpCurl->addPostvar("wpc_publish_url", $sPublishUrl);
			$WpCurl->addPostvar("wpc_publish_title", $sPublishTitle);
			$WpCurl->addPostvar("wpc_publish_desc", $sPublishDesc);
			
			$WpCron->setCurl($WpCurl);
			$jsonResponse = $WpCron->curlRequest();
			self::updateRefreshOption($jsonResponse);
		}
		
	}
	
	static function updateRefreshOption($jsonResponse){
		
		$arResponse = json_decode($jsonResponse,true);
		
		if(isset($arResponse["response"]["datetime_next_update"]) && $arResponse["response"]["datetime_next_update"])
		{
			update_option("wpc_publish_datetime_next_update",$arResponse["response"]["datetime_next_update"]);
		}else{
			$dtNow = CRlibDatetime::currentDatetimeUtc();
			$iNow = CRlibDatetime::datetimeToTimestamp($dtNow);
			$iUpdate = $iNow + (1*60*60*24);
			$dtUpdate = CRlibDatetime::timestampToDatetime($iUpdate);
			update_option("wpc_publish_datetime_next_update",$dtUpdate);
		}
		
		
	}
	
	static function plugin_uninstall(){
		$WPDatabaseManager = new ClassWPDatabaseManager();
		$WPDatabaseManager->remove();
	}
	
	static function js_head_admin(){
		
		$wp_admin_js = WP_ANDROID_CLIENT_PLUGIN_URL . '/js/obj.wpc-main.js';
		wp_enqueue_script('admin_enqueue_scripts', $wp_admin_js, array('jquery-ui-tabs'), '1.1');
		wp_enqueue_style('wp_android_client-admin-ui-css',WP_ANDROID_CLIENT_PLUGIN_URL.'/jquery-1.11.4/jquery-ui.min.css');
	}
	
	static function filter_cron_schedules($arSchedules)
	{
		$WpCron = new ClassWpCron();
		$arSchedules = $WpCron->filter_cron_schedules($arSchedules);
		return $arSchedules;
	}
	
	static function wpc_adminmenu(){
		add_options_page('WP MobileReader', 'WP MobileReader', 'administrator', 'wp_android_client', array("ClassWPAndroidClient",'wpc_admin_page'));
	}
	
	static function wpc_admininit(){
		register_setting("wp_android_client","wpc_allow_editing");
		register_setting("wp_android_client","wpc_editing_available_by_role");
		register_setting("wp_android_client","wpc_allow_publish");
		register_setting("wp_android_client","wpc_publish_url");
		register_setting("wp_android_client","wpc_publish_title");
		register_setting("wp_android_client","wpc_publish_desc");
		
		add_option("wpc_publish_datetime_next_update",0);
	}
	
	static function wpc_admin_page(){
		
		$sTabEditingContent = self::getTabEditingContent();
		$sTabCommunicationContent = self::getTabCommunicationContent();
		
		echo <<< HTML
		<div class="wrap">
			<h2>WP MobileReader Settings</h2>
			<form method="POST" action="options.php">
HTML;
		settings_fields("wp_android_client");
		
		echo <<< HTML
				<div id="wpc_tabs">
					<ul>
						<li><a href="#wpc_communication">Publish</a></li>
						<li><a href="#wpc_editing">Editing</a></li>
					</ul>
					<div id="wpc_communication">
						{$sTabCommunicationContent}
					</div>
					<div id="wpc_editing">
						{$sTabEditingContent}
					</div>
HTML;
		submit_button();
		echo <<< HTML
			</form>
		</div>
HTML;

	
	}
	
	static function getTabCommunicationContent(){
		
		$checkedAllowPublish = checked("1", get_option("wpc_allow_publish"),false);
		$sPublishUrl = get_option("wpc_publish_url", get_option("siteurl"));
		$sPublishTitle = get_option("wpc_publish_title", get_option("blogname"));
		$sPublishDesc = get_option("wpc_publish_desc", get_option("blogdescription"));
		
		$sPublishUrl = wp_strip_all_tags($sPublishUrl);
		$sPublishTitle = wp_strip_all_tags($sPublishTitle);
		$sPublishDesc = wp_strip_all_tags($sPublishDesc);

		$sSeitenliste = WP_ANDROID_CLIENT_SEITENLISTE_URL;
		
		$bSettingsUpdated = isset($_GET["settings-updated"]) ? true: false ;
		if($bSettingsUpdated){
			self::cron_function(CRlibTime::timestampToDatetime(1));
		}
		
		
		$sTab = <<< HTML
			<table class="form-table">
				<tr>
					<th scope="row">
						Publish my URL on
							<a href="{$sSeitenliste}" target="_blank">
								trames.de
							</a>
						<span style="color:red;font-size:90%;display:none;">
							One-time deactivation and activation of the extension is necessary.
						</span>
					</th>
					<td>
						<input name="wpc_allow_publish" type="checkbox" value="1" {$checkedAllowPublish}>
					</td>
				</tr>
				<tr>
					<th scope="row">Page URL</th>
					<td>
						<input name="wpc_publish_url" type="text" size="100" value="{$sPublishUrl}">
					</td>
				</tr>
				<tr>
					<th scope="row">Page Title</th>
					<td>
						<input name="wpc_publish_title" type="text" size="100" maxlength="100" value="{$sPublishTitle}">
					</td>
				</tr>
				<tr>
					<th scope="row">Page Description</th>
					<td>
						<textarea name="wpc_publish_desc" cols="100" maxlength="300">{$sPublishDesc}</textarea>
					</td>
				</tr>
			</table>
HTML;
		return $sTab;
	}
	
	static function getTabEditingContent(){
		$WPUserCapabilities = new ClassWPUserCapabilities();
//		$sCapabilitiesInfo = $WPUserCapabilities->getCapabilityInfo();
		
		$arRoles = $WPUserCapabilities->getAllRoles();
		
		$sRolesSection = self::composeRolesSection($arRoles);
		
		$checkedAllowEditing = checked("1", get_option("wpc_allow_editing"),false);
		
		$sTab = <<< HTML
			<table class="form-table">
				<tr>
					<th scope="row">Allow Editing</th>
					<td>
						<input name="wpc_allow_editing" type="checkbox" value="1" {$checkedAllowEditing}>
					</td>
				</tr>
				<tr>
					<th scope="row">Editing available for roles:</th>
					<td>{$sRolesSection}</td>
				</tr>
					
			</table>
HTML;
		return $sTab;
		
	}
	
	static function composeRolesSection($arRoles){
		$sResult = "";
		
		if(!is_array($arRoles))
			return "";
		
		$arChecked= get_option("wpc_editing_available_by_role");
		
		foreach($arRoles as $keyRole => $sNameRole){
			$checkedRole = "";
			if(is_array($arChecked) && isset($arChecked[$keyRole]) && $arChecked[$keyRole])
			{
				$checkedRole = " checked='checked' ";
			}
			
			$sResult .= <<< HTML
				<div>
					<input type="checkbox" name="wpc_editing_available_by_role[{$keyRole}]" value=1 {$checkedRole}>{$sNameRole}
				</div>
HTML;
		}
		
		return $sResult;
	}
	
}

interface RequestCmdInterface{
	
	public function execute($arPOST, $isLoggedIn);
	
}
