<?php

/*
Plugin Name: WP MobileReader
Plugin URI: http://www.trames.de/
Description: Interface for Android WP MobileReader
Version: 2.4
Author: Richard Krieger
Licence: GPL
*/

/**
 * Beispiel
 * http://www.trames.de/wp-admin/admin-ajax.php?action=exec_wp_android_client&cmd=wpc_ping
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define('WP_ANDROID_CLIENT_PLUGIN_DIR', WP_PLUGIN_DIR . '/interface-for-android-wp-mobilereader');
define('WP_ANDROID_CLIENT_PLUGIN_URL', WP_PLUGIN_URL . '/interface-for-android-wp-mobilereader');
define('WP_ANDROID_CLIENT_SEITENLISTE_URL', 'https://www.trames.de/seiten/');
// define('WP_ANDROID_CLIENT_SERVER_URL', 'http://192.168.178.38:8080/wp-admin/admin-ajax.php?action=rk_wp_sitelist');
define('WP_ANDROID_CLIENT_SERVER_URL', 'https://www.trames.de/wp-admin/admin-ajax.php?action=rk_wp_sitelist');

require_once (WP_ANDROID_CLIENT_PLUGIN_DIR . '/wp_android_client_ajax.php');

add_action("wp_ajax_exec_wp_android_client", array('wp_android_client_ajax','exec_wp_android_client'));
add_action("wp_ajax_nopriv_exec_wp_android_client", array('wp_android_client_ajax','nopriv_exec_wp_android_client'));

add_action("admin_menu", array("ClassWPAndroidClient","wpc_adminmenu"));
add_action("admin_init", array("ClassWPAndroidClient","wpc_admininit"));

if(is_admin()){
//	add_action('admin_print_scripts',array("ClassWPAndroidClient", 'js_head_admin'));
	add_action('admin_enqueue_scripts',array("ClassWPAndroidClient", 'js_head_admin'));
}

add_filter('cron_schedules', array("ClassWPAndroidClient",'filter_cron_schedules'));
add_action('wp_android_client_cron', array("ClassWPAndroidClient",'cron_function'));

register_activation_hook(__FILE__,array("ClassWPAndroidClient",'plugin_activation'));
register_deactivation_hook(__FILE__,array("ClassWPAndroidClient",'plugin_deactivation'));
register_uninstall_hook(__FILE__,array("ClassWPAndroidClient",'plugin_uninstall'));
