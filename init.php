<?php

/*
Plugin Name: Easy Theme and Plugin Upgrades
Plugin URI:  http://wordpress.org/extend/plugins/easy-theme-and-plugin-upgrades/
Description: Upgrade themes and plugins using a zip file without having to remove them first.
Author:      Chris Jean
Author URI:  https://chrisjean.com/
Version:     1.0.6-dev
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: easy-theme-and-plugin-upgrades
*/


if ( is_admin() )
	require_once( dirname( __FILE__ ) . '/modify-installer.php' );
else
	require_once( dirname( __FILE__ ) . '/show-maintenance-message.php' );
