<?php


final class CAJ_ETPU_Admin {
	public static function init() {
		add_action( 'load-update.php', array( __CLASS__, 'set_hooks' ) );
	}

	public static function set_hooks() {
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		add_action( 'admin_action_upload-theme', array( __CLASS__, 'update_theme' ) );
		add_action( 'admin_action_upload-plugin', array( __CLASS__, 'update_plugin' ) );
	}

	public static function update_theme() {
		if ( ! current_user_can( 'upload_themes' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to install themes on this site.' ) );
		}

		check_admin_referer( 'theme-upload' );

		$file_upload = new File_Upload_Upgrader( 'themezip', 'package' );

		wp_enqueue_script( 'customize-loader' );

		$title = __( 'Upload Theme' );
		$parent_file = 'themes.php';
		$submenu_file = 'theme-install.php';

		require_once( ABSPATH . 'wp-admin/admin-header.php' );

		$title = sprintf( __( 'Installing Theme from uploaded file: %s' ), esc_html( basename( $file_upload->filename ) ) );
		$nonce = 'theme-upload';
		$url = add_query_arg( array( 'package' => $file_upload->id ), 'update.php?action=upload-theme' );
		$type = 'upload'; // Install plugin type, From Web or an Upload.

		require_once( dirname( __FILE__ ) . '/custom-theme-upgrader.php' );

		$upgrader = new CAJ_ETPU_Theme_Upgrader( new Theme_Installer_Skin( compact( 'type', 'title', 'nonce', 'url' ) ) );
		$result = $upgrader->install( $file_upload->package );

		if ( $result || is_wp_error( $result ) ) {
			$file_upload->cleanup();
		}

		include( ABSPATH . 'wp-admin/admin-footer.php' );

		exit();
	}

	public static function update_plugin() {
		if ( ! current_user_can( 'upload_plugins' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to install plugins on this site.' ) );
		}

		check_admin_referer( 'plugin-upload' );

		$file_upload = new File_Upload_Upgrader( 'pluginzip', 'package' );

		$title = __( 'Upload Plugin' );
		$parent_file = 'plugins.php';
		$submenu_file = 'plugin-install.php';
		require_once( ABSPATH . 'wp-admin/admin-header.php' );

		$title = sprintf( __( 'Installing Plugin from uploaded file: %s' ), esc_html( basename( $file_upload->filename ) ) );
		$nonce = 'plugin-upload';
		$url = add_query_arg( array( 'package' => $file_upload->id ), 'update.php?action=upload-plugin' );
		$type = 'upload'; // Install plugin type, From Web or an Upload.

		require_once( dirname( __FILE__ ) . '/custom-plugin-upgrader.php' );

		$upgrader = new CAJ_ETPU_Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'type', 'title', 'nonce', 'url' ) ) );
		$result = $upgrader->install( $file_upload->package );

		if ( $result || is_wp_error( $result ) ) {
			$file_upload->cleanup();
		}

		include( ABSPATH . 'wp-admin/admin-footer.php' );

		exit();
	}
}
CAJ_ETPU_Admin::init();
