<?php



class Ithemes_Sync_Functions {
	public static function get_url( $path ) {
		$path = str_replace( '\\', '/', $path );
		$wp_content_dir = str_replace( '\\', '/', WP_CONTENT_DIR );
		
		if ( 0 === strpos( $path, $wp_content_dir ) ) {
			return content_url( str_replace( $wp_content_dir, '', $path ) );
		}
		
		$abspath = str_replace( '\\', '/', ABSPATH );
		
		if ( 0 === strpos( $path, $abspath ) ) {
			return site_url( str_replace( $abspath, '', $path ) );
		}
		
		$wp_plugin_dir = str_replace( '\\', '/', WP_PLUGIN_DIR );
		$wpmu_plugin_dir = str_replace( '\\', '/', WPMU_PLUGIN_DIR );
		
		if ( 0 === strpos( $path, $wp_plugin_dir ) || 0 === strpos( $path, $wpmu_plugin_dir ) ) {
			return plugins_url( basename( $path ), $path );
		}
		
		return false;
	}
	
	public static function get_post_data( $vars, $fill_missing = false, $merge_get_query = false ) {
		$data = array();
		
		foreach ( $vars as $var ) {
			if ( isset( $_POST[$var] ) ) {
				$clean_var = preg_replace( '/^it-updater-/', '', $var );
				$data[$clean_var] = $_POST[$var];
			}
			else if ( $merge_get_query && isset( $_GET[$var] ) ) {
				$clean_var = preg_replace( '/^it-updater-/', '', $var );
				$data[$clean_var] = $_GET[$var];
			}
			else if ( $fill_missing ) {
				$data[$var] = '';
			}
		}
		
		return stripslashes_deep( $data );
	}
	
	public static function filter_user_has_cap( $capabilities, $caps, $args ) {
		foreach ( $caps as $cap ) {
			$capabilities[$cap] = 1;
		}
		
		return $capabilities;
	}
	
	public static function get_plugin_details( $args = array() ) {
		if ( ! is_callable( 'get_plugins' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		
		if ( ! is_callable( 'get_plugins' ) ) {
			return false;
		}
		
		
		$plugins = get_plugins();
		
		$active_plugins = ( is_callable( 'wp_get_active_and_valid_plugins' ) ) ? wp_get_active_and_valid_plugins() : array();
		$network_active_plugins = ( is_callable( 'wp_get_active_network_plugins' ) ) ? wp_get_active_network_plugins() : array();
//		$mu_plugins = ( is_callable( 'get_mu_plugins' ) ) ? get_mu_plugins() : array();
//		$dropins = ( is_callable( 'get_dropins' ) ) ? get_dropins() : array();
		
		array_walk( $active_plugins, array( __CLASS__, 'strip_plugin_dir' ) );
		array_walk( $network_active_plugins, array( __CLASS__, 'strip_plugin_dir' ) );
		
		foreach ( $plugins as $plugin => $data ) {
			if ( in_array( $plugin, $active_plugins ) ) {
				$plugins[$plugin]['status'] = 'active';
			} else if ( in_array( $plugin, $network_active_plugins ) ) {
				$plugins[$plugin]['status'] = 'network_active';
			} else {
				$plugins[$plugin]['status'] = 'inactive';
			}
			
			if ( empty( $args['verbose'] ) ) {
				unset( $plugins[$plugin]['PluginURI'] );
				unset( $plugins[$plugin]['Description'] );
				unset( $plugins[$plugin]['Author'] );
				unset( $plugins[$plugin]['AuthorURI'] );
				unset( $plugins[$plugin]['TextDomain'] );
				unset( $plugins[$plugin]['DomainPath'] );
				unset( $plugins[$plugin]['Title'] );
				unset( $plugins[$plugin]['AuthorName'] );
			} else {
				$path = WP_PLUGIN_DIR . '/' . dirname( $plugin );
				
				$vcs_details = self::get_repository_directory_details( $path );
				
				if ( false !== $vcs_details ) {
					$plugins[$plugin]['vcs'] = $vcs_details;
				}
			}
		}
		
		
		return $plugins;
	}
	
	public static function get_plugin_data( $path ) {
		if ( ! is_callable( 'get_plugin_data' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		
		if ( ! is_callable( 'get_plugin_data' ) ) {
			return false;
		}
		
		$wp_plugin_dir = preg_replace( '|\\\|', '/', WP_PLUGIN_DIR );
		$path = preg_replace( '|\\\|', '/', $path );
		
		$path = preg_replace( '/^' . preg_quote( $wp_plugin_dir, '/' ) . '/', '', $path );
		$path = preg_replace( '|^/+|', '', $path );
		$path = WP_PLUGIN_DIR . "/$path";
		
		$data = get_plugin_data( $path, false, false );
		
		return $data;
	}
	
	public static function get_file_data( $file, $type = '' ) {
		$headers = array(
			'name'             => 'Name',
			'version'          => 'Version',
			'description'      => 'Description',
			'author'           => 'Author',
			'author-uri'       => 'Author URI',
			'text-domain'      => 'Text Domain',
			'text-domain-path' => 'Domain Path',
			'ithemes-package'  => 'iThemes Package',
		);
		
		$plugin_headers = array(
			'plugin-uri' => 'Plugin URI',
			'network'    => 'Network',
			'sitewide'   => '_sitewide',
		);
		
		$theme_headers = array(
			'theme-uri' => 'ThemeURI',
			'template'  => 'Template',
			'status'    => 'Status',
			'tags'      => 'Tags',
		);
		
		
		if ( 'plugin' === $type ) {
			$headers = array_merge( $headers, $plugin_headers );
		} else if ( 'theme' === $type ) {
			$headers = array_merge( $headers, $theme_headers );
		}
		
		return get_file_data( $file, $headers );
	}
	
	public static function find_match_in_file( $file, $expression, $index = false ) {
		$fh = fopen( $file, 'r' );
		
		$data = '';
		$retval = false;
		
		while ( $file_read = fread( $fh, 256 ) ) {
			$data .= $file_read;
			
			if ( preg_match( $expression, $data, $match ) ) {
				$retval = $match;
				break;
			}
		}
		
		fclose( $fh );
		
		if ( false !== $index ) {
			if ( is_array( $retval ) && isset( $retval[$index] ) ) {
				return $retval[$index];
			} else {
				return false;
			}
		}
		
		return $retval;
	}
	
	public static function get_wordpress_version() {
		return self::find_match_in_file( ABSPATH . WPINC . '/version.php', "/\\\$wp_version\s*=\s*(['\"])([^'\"]+)\\1/", 2 );
	}
	
	public static function get_wordpress_db_version() {
		return self::find_match_in_file( ABSPATH . WPINC . '/version.php', "/\\\$wp_db_version\s*=\s*['\"]?(\d+)/", 1 );
	}
	
	public static function strip_plugin_dir( &$path ) {
		$path = preg_replace( '|^' . preg_quote( WP_PLUGIN_DIR, '|' ) . '/|', '', $path );
	}
	
	public static function get_theme_details( $args = array() ) {
		if ( ! is_callable( 'wp_get_themes' ) ) {
			return false;
		}
		
		
		$themes = array();
		
		$active_stylesheet = basename( get_stylesheet_directory() );
		$active_template = basename( get_template_directory() );
		
		foreach ( wp_get_themes() as $dir => $theme ) {
			$data = array(
				'name'    => $theme['Name'],
				'version' => $theme['Version'],
				'parent'  => $theme->parent_theme,
			);
			
			if ( ! empty( $args['verbose'] ) ) {
				$data['description'] = $theme['Description'];
				$data['author'] = $theme['Author Name'];
				$data['author-uri'] = $theme['Author URI'];
				
				
				$vcs_details = self::get_repository_directory_details( $theme['Stylesheet Dir'] );
				
				if ( false !== $vcs_details ) {
					$data['vcs'] = $vcs_details;
				}
			}
			
			if ( empty( $data['parent'] ) ) {
				unset( $data['parent'] );
			} else {
				$data['parent'] = $theme->parent()->stylesheet;
			}
			
			if ( $dir == $active_stylesheet ) {
				$data['status'] = 'active';
			} else if ( $dir == $active_template ) {
				$data['status'] = 'active_parent';
			} else {
				$data['status'] = '';
			}
			
			
			$themes[$dir] = $data;
		}
		
		
		return $themes;
	}
	
	public static function refresh_plugin_updates() {
		require_once( ABSPATH . 'wp-includes/update.php' );
		
		if ( is_callable( 'wp_update_plugins' ) ) {
			return wp_update_plugins();
		}
		
		return false;
	}
	
	public static function refresh_theme_updates() {
		require_once( ABSPATH . 'wp-includes/update.php' );
		
		if ( is_callable( 'wp_update_themes' ) ) {
			return wp_update_themes();
		}
		
		return false;
	}
	
	public static function refresh_core_updates() {
		require_once( ABSPATH . 'wp-includes/update.php' );
		
		if ( is_callable( 'wp_version_check' ) ) {
			return wp_version_check( array(), true );
		}
		
		return false;
	}
	
	public static function get_update_details( $args = array() ) {
		if ( ! empty( $args['ithemes-updater-force-refresh'] ) && isset( $GLOBALS['ithemes-updater-settings'] ) ) {
			$GLOBALS['ithemes-updater-settings']->flush( 'forced sync flush' );
		}
		
		$default_args = array(
			'verbose'       => false,
			'force_refresh' => false,
		);
		$args = array_merge( $default_args, $args );
		
		
		$updates = array(
			'plugins'      => array(),
			'themes'       => array(),
			'translations' => array(),
			'core'         => array(),
		);
		
		
		if ( is_array( $args['force_refresh'] ) ) {
			if ( in_array( 'plugins', $args['force_refresh'] ) ) {
				$updates['force-refresh-results']['plugins'] = self::refresh_plugin_updates();
			}
			if ( in_array( 'themes', $args['force_refresh'] ) ) {
				$updates['force-refresh-results']['themes'] = self::refresh_theme_updates();
			}
			if ( in_array( 'core', $args['force_refresh'] ) ) {
				$updates['force-refresh-results']['core'] = self::refresh_core_updates();
			}
		} else if ( $args['force_refresh'] ) {
			$updates['force-refresh-results']['plugins'] = self::refresh_plugin_updates();
			$updates['force-refresh-results']['themes'] = self::refresh_theme_updates();
			$updates['force-refresh-results']['core'] = self::refresh_core_updates();
		}
		
		
		$update_plugins = get_site_transient( 'update_plugins' );
		
		if ( ! empty( $update_plugins->response ) ) {
			$updates['plugins'] = $update_plugins->response;
			
			if ( empty( $args['verbose'] ) ) {
				foreach ( $updates['plugins'] as $plugin => $data ) {
					unset( $updates['plugins'][$plugin]->id );
					unset( $updates['plugins'][$plugin]->slug );
					unset( $updates['plugins'][$plugin]->url );
					unset( $updates['plugins'][$plugin]->package );
				}
			}
		}
		
		if ( ! empty( $update_plugins->translations ) ) {
			$updates['translations'] = array_merge( $updates['translations'], $update_plugins->translations );
		}
		
		
		$update_themes = get_site_transient( 'update_themes' );
		
		if ( ! empty( $update_themes->response ) ) {
			$updates['themes'] = $update_themes->response;
			
			if ( empty( $args['verbose'] ) ) {
				foreach ( $updates['themes'] as $theme => $data ) {
					unset( $updates['themes'][$theme]['package'] );
					unset( $updates['themes'][$theme]['url'] );
				}
			}
		}
		
		if ( ! empty( $update_themes->translations ) ) {
			$updates['translations'] = array_merge( $updates['translations'], $update_themes->translations );
		}
		
		
		$update_core = get_site_transient( 'update_core' );
		
		if ( ! empty( $update_core->updates ) ) {
			$updates['core'] = $update_core->updates;
			
			foreach ( $updates['core'] as $index => $update ) {
				if ( empty( $update->current ) && ! empty( $update->version ) ) {
					$updates['core'][$index]->current = $update->version;
				} else if ( empty( $update->version ) && ! empty( $update->current ) ) {
					$updates['core'][$index]->version = $update->current;
				}
				
				if ( empty( $args['verbose'] ) ) {
					unset( $updates['core'][$index]->download );
					unset( $updates['core'][$index]->packages );
					unset( $updates['core'][$index]->php_version );
					unset( $updates['core'][$index]->mysql_version );
					unset( $updates['core'][$index]->new_bundled );
					unset( $updates['core'][$index]->partial_version );
				}
			}
		}
		
		if ( ! empty( $update_core->translations ) ) {
			$updates['translations'] = array_merge( $updates['translations'], $update_core->translations );
		}
		
		
		return $updates;
	}
	
	public static function get_wordpress_details( $args = array() ) {
		$details = array(
			'version'   => self::get_wordpress_version(),
			'url'       => get_bloginfo( 'url' ),
			'wpurl'     => get_bloginfo( 'wpurl' ),
			'login-url' => wp_login_url(),
			'admin-url' => admin_url(),
		);
		
		if ( is_callable( 'is_multisite' ) ) {
			if ( is_multisite() ) {
				$details['multisite'] = true;
				
				if ( is_callable( 'get_current_blog_id' ) ) {
					$details['blogid'] = get_current_blog_id();
				} else if ( isset( $GLOBALS['blogid'] ) ) {
					$details['blogid'] = $GLOBALS['blogid'];
				}
			}
			else {
				$details['multisite'] = false;
			}
		}
		
		if ( ! empty( $args['verbose'] ) ) {
			$vcs_details = self::get_repository_directory_details( ABSPATH );
			
			if ( false !== $vcs_details ) {
				$details['vcs'] = $vcs_details;
			}
		}
		
		return $details;
	}
	
	public static function get_php_details( $args = array() ) {
		$details['display_errors'] = $GLOBALS['ithemes_sync_request_handler']->original_display_errors;
		$details['error_reporting'] = $GLOBALS['ithemes_sync_request_handler']->original_error_reporting;
		
		if ( self::is_callable_function( 'ini_get' ) ) {
			$details['disable_functions'] = ini_get( 'disable_functions' );
			$details['suhosin.executor.func.blacklist'] = ini_get( 'suhosin.executor.func.blacklist' );
		}
		
		
		$functions = array(
			'phpversion',
			'PHP_VERSION',
			'php_sapi_name',
			'PHP_SAPI',
		);
		
		$details = self::get_function_results( $functions, $details );
		
		
		if ( empty( $args['verbose'] ) ) {
			return $details;
		}
		
		
		$functions = array(
			'zend_version',
			'sys_get_temp_dir',
			'get_loaded_extensions',
		);
		
		$details = self::get_function_results( $functions, $details );
		
		
		if ( self::is_callable_function( 'phpinfo' ) ) {
			ob_start();
			phpinfo();
			
			$phpinfo = ob_get_clean();
			$phpinfo = preg_replace( '/<[^>]*>/', ' ', $phpinfo );
			$phpinfo = html_entity_decode( $phpinfo, ENT_QUOTES );
			
			$patterns = array(
				'php-version'     => '/^\s*PHP Version\s+(.+)\s*$/mi',
				'build-system'    => '/^\s*System\s+(.+)\s*$/mi',
				'configure'       => '/^\s*Configure Command\s+(.+)\s*$/mi',
				'server-api'      => '/^\s*Server API\s+(.+)\s*$/mi',
				'gd-support'      => '/^\s*GD Support\s+(.+)\s*$/mi',
				'json-support'    => '/^\s*json support\s+(.+)\s*$/mi',
				'mb-support'      => '/^\s*Multibyte Support\s+(.+)\s*$/mi',
				'server-software' => '/^\s*SERVER_SOFTWARE\s+(.+)\s*$/mi',
			);
			
			$details['phpinfo'] = self::get_pattern_results( $phpinfo, $patterns );
		}
		
		
		return $details;
	}
	
	public static function get_server_details( $args = array() ) {
		$details = array(
			'timezone_string'   => get_option( 'timezone_string' ),
			'gmt_offset'        => get_option( 'gmt_offset' ),
			'ini.date.timezone' => ini_get( 'date.timezone' ),
		);
		
		$timezone = self::run_shell_command( 'date +%Z 2>/dev/null' );
		
		if ( ! empty( $timezone ) ) {
			$details['date +%Z'] = $timezone;
			$details['date +%z'] = self::run_shell_command( 'date +%z 2>/dev/null' );
			$details['date +%s'] = self::run_shell_command( 'date +%s 2>/dev/null' );
		}
		
		
		$functions = array(
			'time',
			'date_default_timezone_get',
			'sys_getloadavg',
			'php_uname',
			'PHP_OS',
			'memory_get_usage',
			'memory_get_peak_usage',
		);
		
		$details = self::get_function_results( $functions, $details );
		
		if ( ! isset( $GLOBALS['wpdb'] ) || ( empty( $GLOBALS['wpdb']->use_mysqli ) && self::is_callable_function( 'mysql_get_server_info' ) ) ) {
			$details['mysql_get_server_info'] = @mysql_get_server_info();
		} else if ( isset( $GLOBALS['wpdb']->dbh ) && self::is_callable_function( 'mysqli_get_server_info' ) ) {
			$details['mysqli_get_server_info'] = @mysqli_get_server_info( $GLOBALS['wpdb']->dbh );
		}
		
		$details['SERVER_ADDR'] = @$_SERVER['SERVER_ADDR'];
		
		
		if ( empty( $args['verbose'] ) ) {
			return $details;
		}
		
		
		$functions = array(
			'PHP_EOL',
			'DIRECTORY_SEPARATOR',
		);
		
		$details = self::get_function_results( $functions, $details );
		
		if ( ! isset( $GLOBALS['wpdb'] ) || ( empty( $GLOBALS['wpdb']->use_mysqli ) && self::is_callable_function( 'mysql_get_host_info' ) ) ) {
			$details['mysql_get_host_info'] = @mysql_get_host_info();
		} else if ( isset( $GLOBALS['wpdb']->dbh ) && self::is_callable_function( 'mysqli_get_host_info' ) ) {
			$details['mysqli_get_host_info'] = @mysqli_get_host_info( $GLOBALS['wpdb']->dbh );
		}
		
		$details['server_ip'] = self::get_server_ip();
		
		
		$commands = array(
			'lsb_release -a',
			'cat /etc/*-release',
			'who',
			'df -h',
			'ps aux|wc -l',
			'ps aux --sort=-%cpu|head -6',
			'ps aux --sort=-%mem|head -6',
		);
		
		$details = self::get_shell_command_results( $commands, $details );
		
		
		$cpuinfo = self::run_shell_command( 'cat /proc/cpuinfo' );
		
		if ( preg_match_all( '/model name\s*:\s*([^\r\n]+).*?cpu MHz\s*:\s*([^\r\n]+).*?physical id\s*:\s*([^\r\n]+).*?siblings\s*:\s*([^\r\n]+).*?cpu cores\s*:\s*([^\r\n]+)/s', $cpuinfo, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$details['cpus'][$match[3]] = array(
					'model'    => $match[1],
					'mhz'      => $match[2],
					'siblings' => $match[4],
					'cores'    => $match[5],
				);
			}
		}
		
		
		$memory_data = self::run_shell_command( '/usr/bin/free|grep -i "^Mem:"' );
		
		if ( ! empty( $memory_data ) ) {
			$memory_data = preg_split( '/\s+/', $memory_data );
			
			$memory = array(
				'total'   => $memory_data[1],
				'used'    => $memory_data[2],
				'free'    => $memory_data[3],
				'buffers' => $memory_data[5],
				'cache'   => $memory_data[6],
			);
			
			$memory['used-real'] = $memory['used'] - $memory['buffers'] - $memory['cache'];
			$memory['free-real'] = $memory['total'] - $memory['used-real'];
			
			
			$swap_data = self::run_shell_command( '/usr/bin/free|grep -i "^Swap:"' );
			
			if ( ! empty( $swap_data ) ) {
				$swap_data = preg_split( '/\s+/', $swap_data );
				
				$memory['swap'] = array(
					'total' => $swap_data[1],
					'used'  => $swap_data[2],
					'free'  => $swap_data[3],
				);
			}
			
			
			$details['memory'] = $memory;
		} else if ( file_exists( '/proc/meminfo' ) && ( false !== ( $meminfo = file_get_contents( '/proc/meminfo' ) ) ) && preg_match_all( '/^([^:]+):\s+(\d+)/m', $meminfo, $matches, PREG_SET_ORDER ) ) {
			$memory_data = array();
			
			foreach ( $matches as $match ) {
				$memory_data[$match[1]] = $match[2];
			}
			
			$memory = array(
				'total'   => $memory_data['MemTotal'],
				'used'    => (string) ( $memory_data['MemTotal'] - $memory_data['MemFree'] ),
				'free'    => $memory_data['MemFree'],
				'buffers' => $memory_data['Buffers'],
				'cache'   => $memory_data['Cached'],
			);
			
			$memory['used-real'] = $memory['used'] - $memory['buffers'] - $memory['cache'];
			$memory['free-real'] = $memory['total'] - $memory['used-real'];
			
			
			$memory['swap'] = array(
				'total' => $memory_data['SwapTotal'],
				'used'  => (string) ( $memory_data['SwapTotal'] - $memory_data['SwapFree'] ),
				'free'  => $memory_data['SwapFree'],
			);
			
			
			$details['memory'] = $memory;
		}
		
		
		return $details;
	}
	
	private static function get_server_ip() {
		$data = wp_remote_get( 'http://ithemes.com/utilities/show-remote-ip.php' );
		
		if ( ! is_wp_error( $data ) && preg_match( '/(\d+\.\d+\.\d+\.\d+)/', $data['body'], $match ) ) {
			return $match[1];
		}
		
		
		$data = wp_remote_get( 'http://ip4.me/' );
		
		if ( ! is_wp_error( $data ) && preg_match( '/>(\d+\.\d+\.\d+\.\d+)</', $data['body'], $match ) ) {
			return $match[1];
		}
		
		
		return false;
	}
	
	private static function get_function_results( $functions, $data = array() ) {
		foreach ( $functions as $function ) {
			$var = $function;
			
			if ( false === strpos( $function, '|' ) ) {
				$args = array();
			} else {
				$args = explode( '|', $function );
				$function = array_shift( $args );
				
				if ( ( 1 === count( $args ) ) && ( 0 === strpos( $args[0], '[' ) ) ) {
					$new_args = @json_decode( $args[0] );
					
					if ( ! is_null( $new_args ) ) {
						$args = $new_args;
					}
				}
			}
			
			if ( self::is_callable_function( $function ) ) {
				$data[$var] = call_user_func_array( $function, $args );
			} else if ( defined( $function ) && empty( $args ) ) {
				$data[$var] = constant( $function );
			}
		}
		
		return $data;
	}
	
	private static function get_pattern_results( $raw_data, $patterns, $data = array() ) {
		foreach ( $patterns as $name => $pattern ) {
			if ( preg_match( $pattern, $raw_data, $match ) ) {
				$data[$name] = $match[1];
			}
		}
		
		return $data;
	}
	
	private static function get_shell_command_results( $commands, $data = array() ) {
		foreach ( $commands as $command ) {
			$result = self::run_shell_command( $command );
			
			if ( false !== $result ) {
				$data[$command] = $result;
			}
		}
		
		return $data;
	}
	
	private static function run_shell_command( $command ) {
		$command = 'PATH="$PATH:/usr/local/bin:/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/sbin"; ' . $command;
		
		if ( self::is_callable_function( 'shell_exec' ) ) {
			$result = @shell_exec( $command );
			
			if ( is_null( $result ) ) {
				return false;
			}
			
			return $result;
		}
		
		if ( self::is_callable_function( 'exec' ) ) {
			@exec( $command, $results, $status );
			
			if ( ! empty( $results ) ) {
				return implode( "\n", $results );
			} else if( empty( $status ) ) {
				return '';
			} else {
				return false;
			}
		}
		
		if ( self::is_callable_function( 'system' ) ) {
			ob_start();
			$return = @system( $command, $status );
			$result = ob_get_clean();
			
			if ( false === $return ) {
				return false;
			} else if ( ! empty( $result ) ) {
				return $result;
			} else if ( empty( $status ) ) {
				return '';
			} else {
				return false;
			}
		}
		
		if ( self::is_callable_function( 'passthru' ) ) {
			ob_start();
			$return = @passthru( $command, $status );
			$result = ob_get_clean();
			
			if ( false === $return ) {
				return false;
			} else if ( ! empty( $result ) ) {
				return $result;
			} else if ( empty( $status ) ) {
				return '';
			} else {
				return false;
			}
		}
		
		return false;
	}
	
	public static function merge_defaults( $values, $defaults, $force = false ) {
		if ( ! self::is_associative_array( $defaults ) ) {
			if ( ! isset( $values ) ) {
				return $defaults;
			}
			
			if ( false === $force ) {
				return $values;
			}
			
			if ( isset( $values ) || is_array( $values ) ) {
				return $values;
			}
			
			return $defaults;
		}
		
		foreach ( (array) $defaults as $key => $val ) {
			if ( ! isset( $values[$key] ) ) {
				$values[$key] = null;
			}
			
			$values[$key] = self::merge_defaults( $values[$key], $val, $force );
		}
		
		return $values;
	}
	
	public static function is_associative_array( &$array ) {
		if ( ! is_array( $array ) || empty( $array ) ) {
			return false;
		}
		
		$next = 0;
		
		foreach ( $array as $k => $v ) {
			if ( $k !== $next++ ) {
				return true;
			}
		}
		
		return false;
	}
	
	public static function get_users( $query_args = array() ) {
		$default_query_args = array(
			'blog_id' => 0,
		);
		$query_args = array_merge( $default_query_args, $query_args );
		
		if ( ! empty( $query_args['capability'] ) ) {
			$capabilities = (array) $query_args['capability'];
			unset( $query_args['capability'] );
		}
		
		$all_users = get_users( $query_args );
		
		$users = array();
		
		foreach ( $all_users as $user ) {
			if ( ! empty( $capabilities ) ) {
				$user_can = true;
				
				foreach ( (array) $capabilities as $capability ) {
					if ( ! user_can( $user, $capability ) ) {
						$user_can = false;
						break;
					}
				}
				
				if ( ! $user_can ) {
					continue;
				}
			}
			
			$users[$user->ID] = array(
				'login'        => $user->data->user_login,
				'display_name' => $user->data->display_name,
			);
		}
		
		
		return $users;
	}
	
	public static function get_sync_settings( $args = array() ) {
		$all_settings = $GLOBALS['ithemes-sync-settings']->get_options();
		
		if ( ! empty( $args['settings'] ) ) {
			$keys = $args['settings'];
		} else if ( ! empty( $args['verbose'] ) ) {
			$keys = array_keys( $all_settings );
			
			$keys = array_flip( $keys );
			unset( $keys['authentications'] );
			$keys = array_flip( $keys );
		} else {
			$keys = array(
				'show_sync',
			);
		}
		
		$settings = array();
		
		foreach ( $keys as $key ) {
			if ( isset( $all_settings[$key] ) ) {
				$settings[$key] = $all_settings[$key];
			} else {
				$settings[$key] = null;
			}
		}
		
		if ( ! in_array( 'authentications', $keys ) && isset( $settings['authentications'] ) ) {
			unset( $settings['authentications'] );
		}
		
		
		return $settings;
	}
	
	public static function get_supported_verbs( $args = array() ) {
		if ( ! is_callable( array( $GLOBALS['ithemes-sync-api'], 'get_descriptions' ) ) ) {
			return new WP_Error( 'missing-method-api-get_descriptions', 'The Ithemes_Sync_API::get_descriptions function is not callable.' );
		}
		
		return $GLOBALS['ithemes-sync-api']->get_names();
	}
	
	public static function get_status_elements( $args = array() ) {
		if ( ! is_callable( array( $GLOBALS['ithemes-sync-api'], 'get_status_elements' ) ) ) {
			return new WP_Error( 'missing-method-api-get_status_elements', 'The Ithemes_Sync_API::get_status_elements function is not callable.' );
		}
		
		return $GLOBALS['ithemes-sync-api']->get_status_elements();
	}
	
	public static function get_default_status_elements( $args = array() ) {
		if ( ! is_callable( array( $GLOBALS['ithemes-sync-api'], 'get_default_status_elements' ) ) ) {
			return new WP_Error( 'missing-method-api-get_default_status_elements', 'The Ithemes_Sync_API::get_default_status_elements function is not callable.' );
		}
		
		return $GLOBALS['ithemes-sync-api']->get_default_status_elements();
	}
	
	public static function set_time_limit( $seconds = 60 ) {
		if ( is_callable( 'set_time_limit' ) ) {
			@set_time_limit( $seconds );
		}
	}
	
	public static function get_repository_directory_details( $path ) {
		$vcs_types = array(
			'.git' => array(
				'name' => 'git',
			),
			'.svn' => array(
				'name' => 'subversion',
			),
			'.hg'  => array(
				'name' => 'mercurial',
			),
			'.bzr' => array(
				'name' => 'bazaar',
			),
		);
		
		foreach ( $vcs_types as $directory => $details ) {
			if ( is_dir( "$path/$directory" ) ) {
				return $details;
			}
		}
		
		return false;
	}
	
	public static function is_callable_function( $function ) {
		if ( ! is_callable( $function ) ) {
			return false;
		}
		
		$disabled_functions = preg_split( '/\s*,\s*/', (string) ini_get( 'disable_functions' ) );
		
		if ( in_array( $function, $disabled_functions ) ) {
			return false;
		}
		
		$disabled_functions = preg_split( '/\s*,\s*/', (string) ini_get( 'suhosin.executor.func.blacklist' ) );
		
		if ( in_array( $function, $disabled_functions ) ) {
			return false;
		}
		
		return true;
	}
}
