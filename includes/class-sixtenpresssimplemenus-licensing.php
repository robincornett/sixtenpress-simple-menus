<?php
/**
 * @copyright 2016 Robin Cornett
 */

class SixTenPressSimpleMenusLicensing extends SixTenPressSimpleMenusHelper {

	/**
	 * Current plugin version
	 * @var string $version
	 */
	public $version = '1.0.0';

	/**
	 * Licensing page/setting
	 * @var string $page
	 */
	protected $page = 'sixtenpress';

	/**
	 * Key for plugin setting base
	 * @var string
	 */
	protected $key = 'sixtenpresssimplemenus';

	/**
	 * Array of fields for licensing
	 * @var $fields
	 */
	protected $fields;

	/**
	 * License key
	 * @var $license
	 */
	protected $license = '';

	/** License status
	 * @var $status
	 */
	protected $status = false;

	/**
	 * License data for this site (expiration date, latest version)
	 * @var $data
	 */
	protected $data = false;

	/**
	 * Store URL for Easy Digital Downloads.
	 * @var string
	 */
	protected $url = 'http://local.sandbox.dev';

	/**
	 * Plugin name for EDD.
	 * @var string
	 */
	protected $name = 'Six/Ten Press Simple Menus';

	/**
	 * Plugin slug for license check.
	 * @var string
	 */
	protected $slug = 'sixtenpress-simple-menus';

	/**
	 * SixTenPress Licensing constructor.
	 */
	public function __construct() {
		$this->license = get_option( $this->key . '_key', '' );
		$this->status  = get_option( $this->key . '_status', false );
		$this->data    = get_option( $this->key . '_data', false );
		add_action( 'admin_init', array( $this, 'updater' ), 15 );
//		add_action( 'sixtenpress_weekly_events', array( $this, 'weekly_license_check' ) );
	}

	/**
	 * Set up EDD licensing updates
	 * @since 1.4.0
	 */
	public function updater() {

		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			// load our custom updater if it doesn't already exist
			include plugin_dir_path( __FILE__ ) . 'class-eddpluginupdater.php';
		}

		$edd_updater = new EDD_SL_Plugin_Updater( $this->url, SIXTENPRESSSIMPLEMENUS_BASENAME, array(
			'version'   => $this->version,
			'license'   => trim( $this->license ),
			'item_name' => $this->name,
			'author'    => 'Robin Cornett',
			'url'       => home_url(),
		) );

		if ( is_multisite() && ! is_main_site() ) {
			return;
		}

		$sections     = $this->register_section();
		$this->fields = $this->register_fields();
		$this->register_settings();
		if ( ! class_exists( 'SixTenPress' ) ) {
			$this->page = $this->key;
			$this->add_sections( $sections );
		}
		$this->add_fields( $this->fields, $sections );
		$this->activate_license();
		$this->deactivate_license();
	}

	/**
	 * Register plugin license settings and fields
	 * @since 1.4.0
	 */
	public function register_settings() {
		$options_group = class_exists( 'SixTenPress' ) ? $this->page . 'licensing' : 'sixtenpresssimplemenus';
		register_setting( $options_group, $this->key . '_key', array( $this, 'sanitize_license' ) );
	}

	/**
	 * Register the licensing section.
	 * @return array
	 */
	protected function register_section() {
		return array(
			'licensing' => array(
				'id'    => 'licensing',
				'tab'   => 'licensing',
				'title' => __( 'License', 'sixtenpress-simple-menus' ),
			),
		);
	}

	/**
	 * Register the license key field.
	 * @return array
	 */
	protected function register_fields() {
		return array(
			array(
				'id'       => $this->key . '_key',
				'title'    => __( 'Simple Menus License Key', 'sixtenpress' ),
				'callback' => 'do_license_key_field',
				'section'  => 'licensing',
				'args'     => array(
					'setting' => $this->key . '_key',
					'label'   => __( 'Enter your license key.', 'sixtenpress' ),
				),
			),
		);
	}

	/**
	 * License key input field
	 * @param  array $args parameters to define field
	 * @return input field
	 *
	 * @since 1.4.0
	 */
	public function do_license_key_field( $args ) {
		if ( 'valid' === $this->status ) {
			$style = 'color:white;background-color:green;border-radius:100%;margin-right:8px;vertical-align:middle;';
			printf( '<span class="dashicons dashicons-yes" style="%s"></span>',
				esc_attr( $style )
			);
		}
		printf( '<input type="text" class="regular-text" id="%1$s" name="%1$s" value="%2$s" />',
			esc_attr( $args['setting'] ),
			esc_attr( $this->license )
		);
		if ( ! empty( $this->license ) && 'valid' === $this->status ) {
			$this->add_deactivation_button();
		}
		if ( 'valid' === $this->status ) {
			return;
		}
		if ( ! class_exists( 'SixTenPress' ) ) {
			$this->add_activation_button();
		}
		printf( '<p class="description"><label for="%3$s[%1$s]">%2$s</label></p>', esc_attr( $args['setting'] ), esc_html( $args['label'] ), esc_attr( $this->page ) );
	}

	/**
	 * License deactivation button
	 */
	public function add_activation_button() {

		if ( 'valid' === $this->status ) {
			return;
		}

		$value = sprintf( __( 'Activate', 'sixtenpress-simple-menus' ) );
		$name  = 'sixtenpress_activate';
		$class = 'button-primary';
		$this->print_button( $class, $name, $value );
	}

	/**
	 * License deactivation button
	 */
	public function add_deactivation_button() {

		if ( 'valid' !== $this->status ) {
			return;
		}

		$value = sprintf( __( 'Deactivate', 'sixtenpress-simple-menus' ) );
		$name  = $this->key . '_deactivate';
		$class = 'button-secondary';
		$this->print_button( $class, $name, $value );
	}

	/**
	 * Sanitize license key
	 * @param  string $new_value license key
	 * @return license key
	 *
	 * @since 1.4.0
	 */
	public function sanitize_license( $new_value ) {
		$license = get_option( $this->key . '_key' );
		if ( ( $license && $license !== $new_value ) || empty( $new_value ) ) {
			delete_option( $this->key . '_status' );
		}
		if ( $license !== $new_value || 'valid' !== $this->status ) {
			$this->activate_license( $new_value );
		}
		return sanitize_text_field( $new_value );
	}

	/**
	 * Activate plugin license
	 * @param  string $new_value entered license key
	 * @uses do_remote_request()
	 * @return string   whether key is valid or not
	 *
	 * @since 1.4.0
	 */
	public function activate_license( $new_value = '' ) {

		if ( 'valid' === $this->status ) {
			return;
		}

		// listen for our activate button to be clicked
		if ( isset( $_POST['sixtenpress_activate'] ) ) {

			$action = "{$this->page}_save-settings";
			$nonce  = "{$this->page}_nonce";
			// If the user doesn't have permission to save, then display an error message
			if ( ! $this->user_can_save( $action, $nonce ) ) {
				wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'sixtenpress-simple-menus' ) );
			}

			// run a quick security check
			if ( ! check_admin_referer( $action, $nonce ) ) {
				return; // get out if we didn't click the Activate button
			}

			// retrieve the license from the database
			$license = trim( $this->license );
			$license = $new_value !== $license ? trim( $new_value ) : $license;

			if ( empty( $license ) || empty( $new_value ) ) {
				delete_option( $this->key . '_status' );
				return;
			}

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->name ), // the name of our product in EDD
				'url'        => esc_url( home_url() ),
			);
			$license_data = $this->do_remote_request( $api_params );

			// $license_data->license will be either "valid" or "invalid"
			update_option( $this->key . '_status', $license_data->license );
		}
	}

	/**
	 * Deactivate license
	 * @uses do_remote_request()
	 * @return deletes license status key and deactivates with store
	 *
	 * @since 1.4.0
	 */
	function deactivate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST[$this->key . '_deactivate'] ) ) {

			$action = "{$this->page}_save-settings";
			$nonce  = "{$this->page}_nonce";
			// If the user doesn't have permission to save, then display an error message
			if ( ! $this->user_can_save( $action, $nonce ) ) {
				wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'sixtenpress' ) );
			}

			// run a quick security check
			if ( ! check_admin_referer( $action, $nonce ) ) {
				return; // get out if we didn't click the Activate button
			}

			// retrieve the license from the database
			$license = trim( $this->license );

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->name ), // the name of our product in EDD
				'url'        => home_url(),
			);
			$license_data = $this->do_remote_request( $api_params );

			// $license_data->license will be either "deactivated" or "failed"
			if ( 'deactivated' === $license_data->license ) {
				delete_option( $this->key . '_status' );
			}
		}
	}

	/**
	 * Weekly cron job to compare activated license with the server.
	 * @uses check_license()
	 * @since 2.0.0
	 */
	public function weekly_license_check() {
		if ( apply_filters( 'sixtenpress_skip_license_check', false ) ) {
			return;
		}

		if ( ! empty( $_POST[$this->page . '_nonce'] ) ) {
			return;
		}

		if ( empty( $this->license ) ) {
			delete_option( $this->key . '_status' );
			return;
		}

		$license_data = $this->check_license();
		if ( $license_data->license !== $this->status ) {
			// Update local plugin status
			update_option( $this->key . '_status', $license_data->license );
		}

		$data_setting = $this->key . '_data';
		if ( ! isset( $this->data['expires'] ) || $license_data->expires !== $this->data['expires'] ) {
			$this->update_settings( array(
				'expires' => $license_data->expires,
			), $data_setting );
		}

		if ( 'valid' === $license_data->license ) {
			return;
		}

		$latest_version = $this->get_latest_version();
		if ( ! isset( $this->data['latest_version'] ) || $latest_version !== $this->data['latest_version'] ) {
			$this->update_settings( array(
				'latest_version' => $latest_version,
			), $data_setting );
		}
	}

	/**
	 * Check plugin license status
	 * @uses do_remote_request()
	 * @return mixed data
	 *
	 * @since 1.4.0
	 */
	protected function check_license( $license = '' ) {

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => empty( $license ) ? $this->license : $license,
			'item_name'  => urlencode( $this->name ), // the name of our product in EDD
			'url'        => esc_url( home_url() ),
		);
		if ( empty( $api_params['license'] ) ) {
			return '';
		}
		return $this->do_remote_request( $api_params );
	}

	/**
	 * Get the latest plugin version.
	 * @uses do_remote_request()
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	protected function get_latest_version() {
		$api_params = array(
			'edd_action' => 'get_version',
			'item_name'  => $this->name,
			'slug'       => $this->slug,
		);
		$request = $this->do_remote_request( $api_params );

		if ( $request && isset( $request->sections ) ) {
			$request->sections = maybe_unserialize( $request->sections );
		} else {
			return false;
		}
		return $request->new_version;
	}

	/**
	 * Send the request to the remote server.
	 * @param $api_params
	 *
	 * @return array|bool|mixed|object
	 *
	 * @since 2.0.0
	 */
	private function do_remote_request( $api_params, $timeout = 15 ) {
		$response = wp_remote_post( $this->url, array( 'timeout' => $timeout, 'sslverify' => false, 'body' => $api_params ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Pick which error message to display. Based on whether license has never been activated, or is no longer valid, or has expired.
	 * @param string $message
	 *
	 * @return string|void
	 */
	public function select_error_message( $message = '' ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( 'valid' === $this->status ) {
			return;
		}
		$screen   = get_current_screen();
		$haystack = array( 'settings_page_' . $this->page, 'update', 'update-core', 'plugins' );
		$class    = 'notice-info';
		if ( ! in_array( $screen->id, $haystack, true ) ) {
			return;
		}
		$message .= '<p>' . sprintf( __( 'Sorry, there is an issue with your license for Six/Ten Press Sermons. Please check the <a href="%s">plugin license</a>.', 'sixtenpress' ), esc_url( admin_url( 'options-general.php?page=sixtenpress&tab=licensing' ) ) ) . '</p>';
		if ( $this->license && ! in_array( $this->status, array( 'valid', false ), true ) ) {
			$message .= '<p>';
			if ( isset( $this->data['latest_version'] ) && $this->version < $this->data['latest_version'] ) {
				$message .= sprintf( __( 'The latest version of Six/Ten Press Sermons is %s and you are running %s. ', 'sixtenpress' ), $this->data['latest_version'], $this->version );
			}
			if ( 'expired' === $this->status ) {
				$class       = 'error';
				$license     = get_option( $this->page . '_data' );
				$date        = strtotime( $license['expires'] );
				$pretty_date = $this->pretty_date( array( 'field' => $date ) );
				$renew_url   = trailingslashit( $this->url ) . 'checkout/?edd_action=add_to_cart&download_id=3772&discount=PASTDUE15';
				$message    .= sprintf( __( 'It looks like your license expired on %s. To continue receiving updates, <a href="%s">renew now and receive a discount</a>.', 'sixtenpress' ), $pretty_date, $renew_url );
			} else {
				$message .= __( 'If you\'re seeing this message and have recently migrated from another site, you should just need to reactivate your license.', 'sixtenpress' );
			}
			$message .= '</p>';
		}
		if ( empty( $this->license ) || false === $this->status ) {
			$message = '<p>' . sprintf( __( 'Please make sure you <a href="%s">activate your Six/Ten Press Sermons license</a> in order to receive automatic updates and support.', 'sixtenpress' ), esc_url( admin_url( 'options-general.php?page=sixtenpress&tab=licensing' ) ) ) . '</p>';
		}

		$this->do_error_message( $message, $class );
	}

	/**
	 * Error messages
	 * @return error if license is empty or invalid
	 *
	 * @since 1.4.0
	 */
	protected function do_error_message( $message, $class = '' ) {
		if ( empty( $message ) ) {
			return;
		}
		printf( '<div class="notice %s">%s</div>', $class, wp_kses_post( $message ) );
	}

	/**
	 * Convert a date string to a pretty format.
	 * @param $args
	 * @param string $before
	 * @param string $after
	 *
	 * @return string
	 */
	protected function pretty_date( $args, $before = '', $after = '' ) {
		$date_format = isset( $args['date_format'] ) ? $args['date_format'] : get_option( 'date_format' );

		return $before . date_i18n( $date_format, $args['field'] ) . $after;
	}
}
