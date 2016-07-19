<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}


final class CAJ_ETPU_Upgrade_Handler {
	private $errors = array();
	
	private $type;
	private $zip_url;
	
	
	public function __construct() {
		if ( isset( $_FILES['pluginzip'] ) && current_user_can( 'install_plugins' ) ) {
			$this->type = 'plugin';
		} else if ( isset( $_FILES['themezip'] ) && current_user_can( 'install_themes' ) ) {
			$this->type = 'theme';
		} else {
			return;
		}
		
		
		require_once( dirname( __FILE__ ) . '/settings.php' );
		
		
		add_filter( 'upgrader_source_selection', array( $this, 'filter_upgrader_source_selection' ), 1000 );
		add_filter( 'upgrader_package_options', array( $this, 'filter_upgrader_package_options' ), 100 );
		
		add_action( 'admin_notices', array( $this, 'show_error_message' ) );
		add_action( 'all_admin_notices', array( $this, 'show_message' ) );
		
		$this->create_backup();
	}
	
	private function create_backup() {
		$create_backup = $GLOBALS['caj-etpu-settings']->get_option( 'create-backup' );
		
		if ( ! $create_backup ) {
			return;
		}
		
		
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		
		
		@set_time_limit( 300 );
		
		$archive = new PclZip( $_FILES["{$this->type}zip"]['tmp_name'] );
		
		$directory = '';
		$contents = $archive->listContent();
		
		foreach ( (array) $contents as $content ) {
			if ( preg_match( '|^([^/]+)/$|', $content['filename'], $matches ) ) {
				$directory = $matches[1];
				break;
			}
		}
		
		
		if ( 'plugin' === $this->type ) {
			$plugins = get_plugins();
			
			foreach ( (array) $plugins as $file => $plugin_data ) {
				if ( dirname( $file ) !== $directory ) {
					continue;
				}
				
				$data = array(
					'version'   => $plugin_data['Version'],
					'name'      => $plugin_data['Name'],
					'directory' => WP_PLUGIN_DIR . "/$directory"
				);
				
				break;
			}
		} else {
			$themes = wp_get_themes();
			
			foreach ( $themes as $theme ) {
				if ( $directory !== $theme['Stylesheet'] ) {
					continue;
				}
				
				$data = array(
					'version'   => $theme['Version'],
					'name'      => $theme['Name'],
					'directory' => $theme['Stylesheet Dir'],
				);
				
				break;
			}
		}
		
		if ( empty( $data ) ) {
			return;
		}
		
		
		$ignore_errors = $GLOBALS['caj-etpu-settings']->get_option( 'ignore-errors' );
		
		if ( ! is_dir( $data['directory'] ) ) {
			if ( 'theme' === $this->type ) {
				if ( $ignore_errors ) {
					$this->errors[] = sprintf( __( 'Unable to make a backup of the existing theme. The directory where the existing theme should be (%s) was unable to be found.', 'easy-theme-and-plugin-upgrades' ), "<code>{$data['directory']}</code>" );
				} else {
					$this->errors[] = sprintf( __( 'Unable to make a backup of the existing theme. The directory where the existing theme should be (%s) was unable to be found. The upgrade will not proceed.', 'easy-theme-and-plugin-upgrades' ), "<code>{$data['directory']}</code>" );
				}
			} else {
				if ( $ignore_errors ) {
					$this->errors[] = sprintf( __( 'Unable to make a backup of the existing plugin. The directory where the existing plugin should be (%s) was unable to be found.', 'easy-theme-and-plugin-upgrades' ), "<code>{$data['directory']}</code>" );
				} else {
					$this->errors[] = sprintf( __( 'Unable to make a backup of the existing plugin. The directory where the existing plugin should be (%s) was unable to be found. The upgrade will not proceed.', 'easy-theme-and-plugin-upgrades' ), "<code>{$data['directory']}</code>" );
				}
			}
			
			return;
		}
		
		
		$zip_paths = $this->get_zip_paths( $directory, $data['version'] );
		$zip_path = $zip_paths['path'];
		$zip_url = $zip_paths['url'];
		
		$archive = new PclZip( $zip_path );
		$zip_result = $archive->create( $data['directory'], PCLZIP_OPT_REMOVE_PATH, dirname( $data['directory'] ) );
		
		if ( 0 === $zip_result ) {
			if ( 'theme' === $this->type ) {
				if ( $ignore_errors ) {
					$this->errors[] = __( 'Unable to make a backup of the existing theme.', 'easy-theme-and-plugin-upgrades' );
				} else {
					$this->errors[] = __( 'Unable to make a backup of the existing theme. The upgrade will not proceed.', 'easy-theme-and-plugin-upgrades' );
					
					return;
				}
			} else {
				if ( $ignore_errors ) {
					$this->errors[] = __( 'Unable to make a backup of the existing plugin.', 'easy-theme-and-plugin-upgrades' );
				} else {
					$this->errors[] = __( 'Unable to make a backup of the existing plugin. The upgrade will not proceed.', 'easy-theme-and-plugin-upgrades' );
					
					return;
				}
			}
		}
		
		
		$backups = $GLOBALS['caj-etpu-settings']->get_option( 'backups' );
		
		while ( empty( $id ) || isset( $backups[$id] ) ) {
			$id = uniqid();
		}
		
		$backups[$id] = array(
			'name'      => $data['name'],
			'version'   => $data['version'],
			'path'      => $zip_path,
			'url'       => $zip_url,
			'timestamp' => time(),
		);
		
		$GLOBALS['caj-etpu-settings']->update_option( 'backups', $backups );
		
		
		$this->zip_url = $zip_url;
	}
	
	private function get_zip_paths( $name, $version ) {
		$wp_upload_dir = wp_upload_dir();
		
		if ( is_dir( "{$wp_upload_dir['basedir']}/easy-upgrades" ) ) {
			$basedir = "{$wp_upload_dir['basedir']}/easy-upgrades";
			$baseurl = "{$wp_upload_dir['baseurl']}/easy-upgrades";
		} else if ( mkdir( "{$wp_upload_dir['basedir']}/easy-upgrades", 0775, true ) ) {
			$basedir = "{$wp_upload_dir['basedir']}/easy-upgrades";
			$baseurl = "{$wp_upload_dir['baseurl']}/easy-upgrades";
		} else {
			$basedir = $wp_upload_dir['path'];
			$baseurl = $wp_upload_dir['url'];
		}
		
		if ( ! is_file( "$basedir/index.php" ) ) {
			file_put_contents( "$basedir/index.php", "<?php\n// Silence is golden." );
		}
		
		
		while ( empty( $filename ) || file_exists( "$basedir/$filename" ) ) {
			$random_string = $this->get_random_string();
			$filename = "$name-$version-$random_string.zip";
		}
		
		
		$paths = array(
			'path' => "$basedir/$filename",
			'url'  => "$baseurl/$filename",
		);
		
		return $paths;
	}
	
	private function get_random_string() {
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$max_index = strlen( $characters ) - 1;
		$length = wp_rand( 10, 20 );
		$rand_string = '';
		
		while ( strlen( $rand_string ) < $length ) {
			$rand_string .= $characters[wp_rand( 0, $max_index )];
		}
		
		return $rand_string;
	}
	
	public function filter_upgrader_package_options( $options ) {
		$ignore_errors = $GLOBALS['caj-etpu-settings']->get_option( 'ignore-errors' );
		
		if ( empty( $this->errors ) || $ignore_errors ) {
			$options['abort_if_destination_exists'] = false;
		}
		
		return $options;
	}
	
	public function show_message() {
		if ( empty( $this->zip_url ) ) {
			return;
		}
		
		
		echo '<div id="message" class="updated fade"><p><strong>';
		
		if ( 'theme' === $this->type ) {
			printf( __( 'A backup zip file of the old theme version can be downloaded <a href="%1$s">here</a>. This backup can also be found by going to <a href="%2$s">Media > Library</a>.', 'easy-theme-and-plugin-upgrades' ), $this->zip_url, admin_url( 'upload.php' ) );
		} else {
			printf( __( 'A backup zip file of the old plugin version can be downloaded <a href="%1$s">here</a>. This backup can also be found by going to <a href="%2$s">Media > Library</a>.', 'easy-theme-and-plugin-upgrades' ), $this->zip_url, admin_url( 'upload.php' ) );
		}
		
		echo "</strong></p></div>\n";
	}
	
	public function show_error_message( $errors = false ) {
		if ( empty( $errors ) ) {
			$errors = $this->errors;
		}
		
		if ( empty( $errors ) ) {
			return;
		}
		
		foreach ( (array) $errors as $error ) {
			echo "<div id=\"message\" class=\"error\"><p><strong>$error</strong></p></div>\n";
		}
	}
	
	public function filter_upgrader_source_selection( $source ) {
		if ( ! is_wp_error( $source ) ) {
			return $source;
		}
		
		foreach ( $source->get_error_codes() as $code ) {
			if ( in_array( $code, array( 'incompatible_archive_theme_no_style', 'incompatible_archive_theme_no_name', 'incompatible_archive_theme_no_index' ) ) ) {
				$this->show_error_message( sprintf( __( 'The theme could not be installed due to a problem with the provided zip file. This typically indicates that you attempted to install a plugin using the theme installer. The plugin installer can be found <a href="%s">here</a>.', 'easy-theme-and-plugin-upgrades' ), admin_url( 'plugin-install.php?tab=upload' ) ) );
			} else if ( $code === 'incompatible_archive_no_plugins' ) {
				$this->show_error_message( sprintf( __( 'The plugin could not be installed due to a problem with the provided zip file. This typically indicates that you attempted to install a theme using the plugin installer. The theme installer can be found <a href="%s">here</a>.', 'easy-theme-and-plugin-upgrades' ), admin_url( 'theme-install.php?upload' ) ) );
			}
		}
		
		return $source;
	}
}

new CAJ_ETPU_Upgrade_Handler();
