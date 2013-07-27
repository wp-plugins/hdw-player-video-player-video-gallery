<?php
/******************************************************************
Plugin Name:HDW Player
Plugin URI:http://hdwplayer.com/
Description: HDW Player Plugin for Wordpress Websites.
Version:1.2
Author:Mr. Hdwplayer
Author URI:http://hdwplayer.com
License: GPLv2
******************************************************************/

require_once('installer.php');
require_once('uninstaller.php');
require_once('shortcode.php');
require_once('tabs.php');

global $hdwplayer_version;
global $installed_hdwplayer_version;

$hdwplayer_version = "1.1";
$installed_hdwplayer_version = get_site_option('hdwplayer_version');

/******************************************************************
/* Add Custom CSS file
******************************************************************/
function hdwplayer_plugin_css() {
    $siteurl = get_option('siteurl');
    $url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/hdwplayer.css';
    echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
}

/******************************************************************
/* Creating Menus
******************************************************************/
function hdwplayer_plugin_menu() {
	add_menu_page("HDW Player Title", "HDW Player", "administrator", "hdwplayer", "hdwplayer_plugin_pages");
	add_submenu_page("hdwplayer", "HDW Player Videos", "Videos", "administrator", "videos", "hdwplayer_plugin_pages");
	add_submenu_page("hdwplayer", "HDW Player Playlist", "Playlist", "administrator", "playlist", "hdwplayer_plugin_pages");
	add_submenu_page("hdwplayer", "HDW Player Documentation", "Documentation", "administrator", "documentation", "hdwplayer_plugin_pages");
}

/******************************************************************
/* Assigning Menu Pages
******************************************************************/
function hdwplayer_plugin_pages() {
	hdwplayer_admin_tabs($_GET["page"]);
	require_once (dirname(__FILE__) . "/" . $_GET["page"] . "/__default.php");
}

/******************************************************************
/* Implementing Hooks
******************************************************************/
if (is_admin()) {
	add_action('admin_head', 'hdwplayer_plugin_css');
  	add_action("admin_menu", "hdwplayer_plugin_menu");
	register_activation_hook(__FILE__,'hdwplayer_db_install');
	register_activation_hook(__FILE__,'hdwplayer_db_install_data');
	add_action('plugins_loaded', 'hdwplayer_update_db_check');
	register_uninstall_hook(__FILE__, 'hdwplayer_db_uninstall');
}

?>