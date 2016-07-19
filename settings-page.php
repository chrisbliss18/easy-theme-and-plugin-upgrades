<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}


final class CAJ_ETPU_Settings_Page {
	private $page_name = 'caj-etpu-settings';

	private $settings = array();
	private $self_url = '';
	private $had_error = false;
	private $messages = array();


	public function __construct() {
		require_once( dirname( __FILE__ ) . '/settings.php' );

		$this->self_url = CAJ_ETPU_Admin::get_page_url();

		add_action( 'caj_etpu_settings_page_index', array( $this, 'index' ) );
	}

	public function index() {
		$this->settings = CAJ_ETPU_Settings::get_settings();

		$this->handle_post_action();

		$this->show_settings();
	}

	private function handle_post_action() {
		$post_data = $this->get_post_data( array( 'action' ), true, true );
		$action = $post_data['action'];

		if ( 'save-settings' === $action ) {
			$this->save_settings();
		}
	}

	private function save_settings() {
		check_admin_referer( 'save-settings', 'caj-etpu-settings-nonce' );


		$post_data = $this->get_post_data( array( 'create-backups' ) );
		$settings = $this->settings;

		if ( isset( $post_data['create-backups'] ) ) {
			$settings['create-backups'] = ( 'yes' === $post_data['create-backups'] ) ? true : false;
		}

		CAJ_ETPU_Settings::update_settings( $settings );
		$this->settings = $settings;

		$this->add_success_message( __( 'Settings saved', 'easy-theme-and-plugin-upgrades' ) );
	}

	public function show_settings() {

?>
	<div class="wrap">
		<h2><?php _e( 'Easy Theme and Plugin Upgrades', 'easy-theme-and-plugin-upgrades' ); ?></h2>


		<h3><?php _e( 'Settings', 'easy-theme-and-plugin-upgrades' ); ?></h3>

		<?php $this->show_messages(); ?>

		<form id="caj-etpu-settings" enctype="multipart/form-data" method="post" action="<?php echo $this->self_url; ?>">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="create-backups"><?php _e( 'Create Backups', 'easy-theme-and-plugin-upgrades' ); ?></label>
						</th>
						<td>
							<select id="create-backups" name="create-backups">
								<option value="yes"><?php _e( 'Yes, automatically create a backup before upgrading (recommended)', 'easy-theme-and-plugin-upgrades' ); ?></option>
								<option value="no"><?php _e( 'No, do not automatically create a backup before upgrading', 'easy-theme-and-plugin-upgrades' ); ?></option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>

			<input type="hidden" name="action" value="save-settings">
			<?php wp_nonce_field( 'save-settings', 'caj-etpu-settings-nonce' ); ?>

			<p class="submit"><input type="submit" class="button button-primary" name="save" value="<?php _e( 'Save Settings', 'easy-theme-and-plugin-upgrades' ); ?>" /></p>
		</form>


		<h3><?php _e( 'Backups', 'easy-theme-and-plugin-upgrades' ); ?></h3>
	</div>
<?php

	}

	private function get_url( $path ) {
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

	private function get_post_data( $vars, $fill_missing = false, $merge_get_query = false ) {
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

	private function show_messages() {
		foreach ( $this->messages as $class => $messages ) {
			foreach ( $messages as $message ) {
				$this->show_message( $message['heading'], $message['messages'], $class );
			}
		}
	}

	private function show_message( $heading, $messages, $class ) {

?>
	<div class="message <?php echo $class; ?>">
		<h3><?php echo $heading; ?></h3>

		<?php foreach ( (array) $messages as $message ) : ?>
			<p><?php echo $message; ?></p>
		<?php endforeach; ?>
	</div>
<?php

	}

	private function add_message( $heading, $messages, $class ) {
		$this->messages[$class][] = compact( 'heading', 'messages' );
	}

	private function add_success_message( $heading, $messages = array() ) {
		$this->add_message( $heading, $messages, 'success' );
	}

	private function add_error_message( $heading, $messages = array() ) {
		$this->add_message( $heading, $messages, 'error' );
		$this->had_error = true;
	}
}

new CAJ_ETPU_Settings_Page();
