<?php

/**
 * The main SixTenPress Simple Menus plugin class.
 * @package   SixTenPressSimpleMenus
 * @copyright 2016 Robin Cornett
 */
class SixTenPressSimpleMenus {

	/**
	 * The plugin admin related class.
	 * @var $admin SixTenPressSimpleMenusAdmin
	 */
	protected $admin;

	/**
	 * The plugin output class.
	 * @var $output SixTenPressSimpleMenusOutput
	 */
	protected $output;

	/**
	 * SixTenPressSimpleMenus constructor.
	 *
	 * @param $admin
	 * @param $output
	 */
	function __construct( $admin, $output ) {
		$this->admin  = $admin;
		$this->output = $output;
	}

	/**
	 * Add base hooks into WordPress
	 */
	public function run() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_menu', array( $this->admin, 'set_post_metaboxes' ) );
		add_action( 'admin_menu', array( $this->admin, 'set_taxonomy_metaboxes' ) );
		add_filter( 'theme_mod_nav_menu_locations', array( $this->output, 'replace_menu' ) );
		add_action( 'plugins_loaded', array( $this, 'load_settings_page' ), 20 );
	}

	/**
	 *
	 */
	public function load_settings_page() {
		if ( ! class_exists( 'SixTenPressSettings' ) ) {
			include_once plugin_dir_path( __FILE__ ) . '/common/class-sixtenpress-settings.php';
		}
		if ( ! class_exists( 'SixTenPressLicensing' ) ) {
			include_once plugin_dir_path( __FILE__ ) . '/common/class-sixtenpress-licensing.php';
		}
		$files = array( 'licensing', 'page' );
		foreach( $files as $file ) {
			include_once plugin_dir_path( __FILE__ ) . 'class-sixtenpresssimplemenus-settings-' . $file .'.php';
		}

		$settings = new SixTenPressSimpleMenuSettings();
//		$licensing      = new SixTenPressSimpleMenusLicensing();
		add_action( 'admin_menu', array( $settings, 'maybe_add_settings_page' ) );
//		add_action( 'admin_init', array( $licensing, 'set_up_licensing' ), 25 );
		add_filter( 'sixtenpresssimplemenus_get_setting', array( $settings, 'get_setting' ) );
	}

	/**
	 * deactivates the plugin if Genesis isn't running
	 *
	 * @since 0.1.0
	 *
	 */
	public function deactivate() {
		deactivate_plugins( SIXTENPRESSSIMPLEMENUS_BASENAME );
		add_action( 'admin_notices', array( $this, 'error_message' ) );
	}

	/**
	 * Error message if we're not using the Genesis Framework.
	 *
	 * @since 1.1.0
	 */
	public function error_message() {

		$error = sprintf( __( 'Sorry, Six/Ten Press Simple Menus works only with the Genesis Framework. It has been deactivated.', 'sixtenpress-simple-menus' ) );

		if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
			$error = $error . sprintf(
				__( ' But since we\'re talking anyway, did you know that your server is running PHP version %1$s, which is outdated? You should ask your host to update that for you.', 'sixtenpress-simple-menus' ),
				PHP_VERSION
			);
		}

		echo '<div class="error"><p>' . esc_attr( $error ) . '</p></div>';

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}

	/**
	 * Set up text domain for translations
	 *
	 * @since 0.1.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'sixtenpress-simple-menus', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}
