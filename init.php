<?php

/*
Plugin Name: Easy Theme and Plugin Upgrades
Plugin URI: http://wordpress.org/extend/plugins/easy-theme-and-plugin-upgrades/
Description: This plugin allows for installed themes to be upgraded by using the Appearance > Add New Themes > Upload feature of WordPress. Without this plugin, themes can only be installed using this method, requiring you to first delete the theme before installing the newer version. Now features the same easy upgrading for plugins via the Plugins > Add New > Upload page.
Author: Chris Jean
Version: 1.0.4
Author URI: http://ithemes.com/
*/


if ( is_admin() )
	require_once( dirname( __FILE__ ) . '/modify-installer.php' );
else
	require_once( dirname( __FILE__ ) . '/show-maintenance-message.php' );
