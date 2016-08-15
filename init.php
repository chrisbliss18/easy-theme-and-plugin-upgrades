<?php

/*
Plugin Name: Easy Theme and Plugin Upgrades
Plugin URI:  http://wordpress.org/extend/plugins/easy-theme-and-plugin-upgrades/
Description: Upgrade themes and plugins using a zip file without having to remove them first.
Author:      Chris Jean
Author URI:  https://chrisjean.com/
Version:     2.0.0
License:     GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: easy-theme-and-plugin-upgrades

Easy Theme and Plugin Upgrades is free software you can redistribute
it and/or modify it under the terms of the GNU General Public License
as published by the Free Software Foundation, either version 2 of the
License, or any later version.

Easy Theme and Plugin Upgrades is distributed in the hope that it
will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See
the GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Easy Theme and Plugin Upgrades. If not, see
https://www.gnu.org/licenses/gpl-2.0.html.
*/


if ( is_admin() ) {
	require( dirname( __FILE__ ) . '/admin.php' );
}
