<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}


final class CAJ_ETPU_Settings {
	private static $option_name = 'caj-etpu-settings';
	private static $settings = false;

	private static $default_settings = array(
		'backups'        => array(),
		'create-backups' => true,
		'ignore-errors'  => false,
	);

	public static function get_settings() {
		if ( false === self::$settings ) {
			self::$settings = get_site_option( self::$option_name, false );
		}

		if ( ( false === self::$settings ) || ! is_array( self::$settings ) ) {
			self::$settings = self::$default_settings;

			if ( ! is_multisite() ) {
				add_option( self::$option_name, self::$settings, '', 'no' );
			} else {
				add_site_option( self::$option_name, self::$settings );
			}
		} else {
			self::$settings = array_merge( self::$default_settings, self::$settings );
		}

		return self::$settings;
	}

	public static function get_setting( $name ) {
		$settings = self::get_settings();

		if ( isset( $settings[$name] ) ) {
			return $settings[$name];
		}

		return null;
	}

	public static function update_settings( $settings ) {
		self::$settings = array_merge( self::get_settings(), $settings );
		update_site_option( self::$option_name, self::$settings );
	}

	public static function update_setting( $name, $value ) {
		self::update_settings( array( $name => $value ) );
	}
}
