<?php


if ( ! class_exists( 'ETUModifyInstaller' ) ) {
	class ETUModifyInstaller {
		var $_errors = array();
		var $_type = '';
		
		
		function ETUModifyInstaller() {
			if ( preg_match( '/update\.php/', $_SERVER['REQUEST_URI'] ) && isset( $_REQUEST['action'] ) ) {
				if ( 'upload-theme' === $_REQUEST['action'] ) {
					$this->_type = 'theme';
				} else if ( 'upload-plugin' === $_REQUEST['action'] ) {
					$this->_type = 'plugin';
				}
				
				if ( ! empty( $this->_type ) ) {
					add_action( 'admin_init', array( $this, 'handle_upgrades' ), 100 );
				}
			}
			
			add_action( 'load-theme-install.php', array( $this, 'start_theme_output_buffering' ) );
			add_action( 'load-plugin-install.php', array( $this, 'start_plugin_output_buffering' ) );
		}
		
		function filter_output( $output ) {
			$text = "<div style='max-width:600px;'>\n";
			$text .= "<p><i>By default, the installer will not overwrite an existing {$this->_type}. Change the following option to \"Yes\" to allow this installer to perform upgrades as well.</i></p>";
			$text .= "<p>Upgrade existing {$this->_type}? <select name='caj_etu_upgrade_existing'><option value=''>No</option><option value='yes'>Yes</option></select></p>\n";
			$text .= "<p>If a {$this->_type} is upgraded, the following process will be used:</p>\n";
			$text .= "<ol>\n";
			$text .= "<li>A backup zip of the existing {$this->_type} will be created and added to the <a href='" . admin_url( 'upload.php' ) . "'>Media Library</a>.</li>\n";
			$text .= "<li>If the selected {$this->_type} is active, the site will display a 'Site being updated' message until the upgrade has finished. This typically lasts a few seconds at most.</li>\n";
			$text .= "<li>The existing {$this->_type} directory will be deleted in order for the installer to install the new version.</li>\n";
			$text .= "<li>The {$this->_type} installer will install the new {$this->_type}.</li>\n";
			$text .= "<li>The site update message will be removed from the site.</li>\n";
			$text .= "</ol><br />\n";
			$text .= "</div>\n";
			
			$output = preg_replace( '/(<input [^>]*name="(?:theme|plugin)zip".+?\n)/', "\$1$text", $output );
			
			return $output;
		}
		
		function start_theme_output_buffering() {
			$this->_type = 'theme';
			ob_start( array( $this, 'filter_output' ) );
		}
		
		function start_plugin_output_buffering() {
			$this->_type = 'plugin';
			ob_start( array( $this, 'filter_output' ) );
		}
		
		function _get_themes() {
			global $wp_themes;
			
			if ( isset( $wp_themes ) ) {
				return $wp_themes;
			}
			
			$themes = wp_get_themes();
			$wp_themes = array();
			
			foreach ( $themes as $theme ) {
				$name = $theme->get( 'Name' );
				if ( isset( $wp_themes[$name] ) )
					$wp_themes[$name . '/' . $theme->get_stylesheet()] = $theme;
				else
					$wp_themes[$name] = $theme;
			}
			
			return $wp_themes;
		}
		
		function _get_theme_data( $directory ) {
			$data = array();
			
			$themes = $this->_get_themes();
			$active_theme = wp_get_theme();
			$current_theme = array();
			
			foreach ( (array) $themes as $theme_name => $theme_data ) {
				if ( $directory === $theme_data['Stylesheet'] )
					$current_theme = $theme_data;
			}
			
			if ( empty( $current_theme ) )
				return $data;
			
			$data['version'] = $current_theme['Version'];
			$data['name'] = $current_theme['Name'];
			$data['directory'] = $current_theme['Stylesheet Dir'];
			
			$data['is_active'] = false;
			if ( ( $active_theme->template_dir === $current_theme['Template Dir'] ) || ( $active_theme->template_dir === $current_theme['Template Dir'] ) )
				$data['is_active'] = true;
			
			global $wp_version;
			if ( version_compare( '2.8.6', $wp_version, '>' ) )
				$data['directory'] = WP_CONTENT_DIR . $current_theme['Stylesheet Dir'];
			
			return $data;
		}
		
		function _get_plugin_data( $directory ) {
			$data = array();
			
			$plugins = get_plugins();
			$active_plugins = get_option('active_plugins');
			$current_plugin = array();
			
			foreach ( (array) $plugins as $plugin_path_file => $plugin_data ) {
				$path_parts = explode( '/', $plugin_path_file );
				if ( $directory === reset( $path_parts ) ) {
					$current_plugin = array( 'path' => $plugin_path_file, 'data' => $plugin_data );
				}
			}
			
			if ( empty( $current_plugin ) ) {
				return $data;
			}
			
			$data['version'] = $current_plugin['data']['Version'];
			$data['name'] = $current_plugin['data']['Name'];
			$data['directory'] = WP_PLUGIN_DIR . '/' . $directory;
			$data['is_active'] = ( is_plugin_active( $current_plugin['path'] ) ) ? true : false;
			
			return $data;
		}
		
		function handle_upgrades() {
			if ( empty( $_POST['caj_etu_upgrade_existing'] ) ) {
				$this->_errors[] = "The Easy Theme and Plugin Upgrades plugin was unable to handle requests for this upgrade. Unfortunately, this setup may be incompatible with the plugin.";
				add_action( 'admin_notices', array( $this, 'show_upgrade_option_error_message' ) );
				
				return;
			}
			
			if ( 'yes' !== $_POST['caj_etu_upgrade_existing'] ) {
				if ( 'plugin' == $this->_type ) {
					$link = admin_url( "plugin-install.php?tab=upload" );
				} else if ( version_compare( $GLOBALS['wp_version'], '3.8.9', '>' ) ) {
					$link = admin_url( "theme-install.php" );
				} else {
					$link = admin_url( "theme-install.php?tab=upload" );
				}
				
				$this->_errors[] = "You must select \"Yes\" from the \"Upgrade existing {$this->_type}?\" dropdown option in order to upgrade an existing {$this->_type}. <a href=\"$link\">Try again</a>.";
				add_action( 'admin_notices', array( $this, 'show_upgrade_option_error_message' ) );
				
				return;
			}
			
			remove_action( 'admin_print_styles', 'builder_add_global_admin_styles' );
			
			
			include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
			require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			
			check_admin_referer( "{$this->_type}-upload" );
			@set_time_limit( 300 );
			
			$archive = new PclZip( $_FILES["{$this->_type}zip"]['tmp_name'] );
			
			$directory = '';
			$contents = $archive->listContent();
			
			foreach ( (array) $contents as $content ) {
				if ( preg_match( '|^([^/]+)/$|', $content['filename'], $matches ) ) {
					$directory = $matches[1];
					break;
				}
			}
			
			if ( 'theme' === $this->_type )
				$data = $this->_get_theme_data( $directory );
			else if ( 'plugin' === $this->_type )
				$data = $this->_get_plugin_data( $directory );
			
			if ( empty( $data ) )
				return;
			
			
			$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			$rand_string = '';
			$length = rand( 10, 20 );
			for ( $count = 0; $count < $length; $count++ )
				$rand_string .= $characters[rand( 0, strlen( $characters ) - 1 )];
			
			$zip_file = "$directory-{$data['version']}-$rand_string.zip";
			
			$wp_upload_dir = wp_upload_dir();
			$zip_path = $wp_upload_dir['path'] . '/' . $zip_file;
			$zip_url = $wp_upload_dir['url'] . '/' . $zip_file;
			
			$archive = new PclZip( $zip_path );
			
			$zip_result = $archive->create( $data['directory'], PCLZIP_OPT_REMOVE_PATH, dirname( $data['directory'] ) );
			
			if ( 0 == $zip_result ) {
				$this->_errors[] = "Unable to make a backup of the existing {$this->_type}. Will not proceed with the upgrade.";
				add_action( 'admin_notices', array( $this, 'show_upgrade_option_error_message' ) );
				
				return;
			}
			
			
			$attachment = array(
				'post_mime_type'	=> 'application/zip',
				'guid'				=> $zip_url,
				'post_title'		=> ucfirst( $this->_type ) . " Backup - {$data['name']} - {$data['version']}",
				'post_content'		=> '',
			);
			
			$id = wp_insert_attachment( $attachment, $zip_path );
			if ( !is_wp_error( $id ) )
				wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $zip_path ) );
			
			
			if ( $data['is_active'] )
				set_transient( 'etu-in-maintenance-mode', '1', 300 );
			
			
			global $wp_filesystem;
			
			if ( ! WP_Filesystem() ) {
				$this->_errors[] = 'Unable to initialize WP_Filesystem. Will not proceed with the upgrade.';
				add_action( 'admin_notices', array( $this, 'show_upgrade_option_error_message' ) );
				
				return;
			}
			
			if ( ! $wp_filesystem->delete( $data['directory'], true ) ) {
				$this->_errors[] = "Unable to remove the existing {$this->_type} directory. Will not proceed with the upgrade.";
				add_action( 'admin_notices', array( $this, 'show_upgrade_option_error_message' ) );
				
				return;
			}
			
			
			$this->_zip_url = $zip_url;
			
			add_action( 'all_admin_notices', array( $this, 'show_message' ) );
		}
		
		function show_message() {
			echo "<div id=\"message\" class=\"updated fade\"><p><strong>A backup zip file of the old {$this->_type} version can be downloaded <a href='$this->_zip_url'>here</a>.</strong></p></div>\n";
			
			delete_transient( 'etu-in-maintenance-mode' );
		}
		
		function show_upgrade_option_error_message() {
			if ( ! isset( $this->_errors ) )
				return;
			
			if ( ! is_array( $this->_errors ) )
				$this->_errors = array( $this->_errors );
			
			foreach ( (array) $this->_errors as $error )
				echo "<div id=\"message\" class=\"error\"><p><strong>$error</strong></p></div>\n";
		}
	}
	
	new ETUModifyInstaller();
}
