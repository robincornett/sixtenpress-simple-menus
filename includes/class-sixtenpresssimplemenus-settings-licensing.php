<?php
/**
 * @copyright 2016 Robin Cornett
 */

class SixTenPressSimpleMenusLicensing extends SixTenPressLicensing {

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
	 * Plugin author.
	 * @var string
	 */
	protected $author = 'Robin Cornett';

	/**
	 * Plugin basename
	 * @var string
	 */
	protected $basename = SIXTENPRESSSIMPLEMENUS_BASENAME;

	/**
	 * SixTenPress Licensing constructor.
	 */
	public function __construct() {
		$this->license = get_option( $this->key . '_key', '' );
		$this->status  = get_option( $this->key . '_status', false );
		$this->data    = get_option( $this->key . '_data', false );
//		add_action( 'sixtenpress_weekly_events', array( $this, 'weekly_license_check' ) );
	}

	/**
	 * Function to set up licensing fields and call the updater.
	 */
	public function set_up_licensing() {
		$sections     = $this->register_section();
		$this->fields = $this->register_fields();
		$this->register_settings();
		$this->add_fields( $this->fields, $sections );
		$this->updater();
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
}
