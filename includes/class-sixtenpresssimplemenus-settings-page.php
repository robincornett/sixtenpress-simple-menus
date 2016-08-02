<?php

/**
 * Class for adding a new settings page to the WordPress admin, under Settings.
 * @package   SixTenPressSimpleMenus
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2016 Robin Cornett Creative, LLC
 */
class SixTenPressSimpleMenuSettings extends SixTenPressSettings {

	/**
	 * Option registered by plugin.
	 * @var array $setting
	 */
	protected $setting;

	/**
	 * Public registered post types.
	 * @var array $post_types
	 */
	protected $post_types;

	/**
	 * Slug for settings page.
	 * @var string $page
	 */
	protected $page = 'sixtenpress';

	/**
	 * Settings fields registered by plugin.
	 * @var array
	 */
	protected $fields;

	/**
	 * Tab/page for settings.
	 * @var string $tab
	 */
	protected $tab = 'sixtenpresssimplemenus';

	/**
	 * Maybe add a new settings page.
	 */
	public function maybe_add_settings_page() {
		if ( $this->is_sixten_active() ) {
			$this->add_sixten();
			return;
		}
		register_setting( $this->page, $this->page, array( $this, 'do_validation_things' ) );

		add_options_page(
			__( '6/10 Press Featured Content Settings', 'sixtenpress' ),
			__( '6/10 Press Featured Content', 'sixtenpress' ),
			'manage_options',
			$this->page,
			array( $this, 'do_settings_form' )
		);

		$this->page    = $this->tab;
		$this->setting = $this->get_setting();
		$sections      = $this->register_sections();
		$this->fields  = $this->register_fields();
		$this->add_sections( $sections );
		$this->add_fields( $this->fields, $sections );
	}

	protected function add_sixten() {
		$this->setting = $this->get_setting();
		$sections      = $this->register_sections();
		$this->fields  = $this->register_fields();
		add_filter( 'sixtenpress_settings_tabs', array( $this, 'add_tab' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		$this->add_sections( $sections );
		$this->add_fields( $this->fields, $sections );
	}

	/**
	 * Output the plugin settings form.
	 *
	 * @since 1.0.0
	 */
	public function do_settings_form() {

		echo '<div class="wrap">';
		echo '<h1>' . esc_attr( get_admin_page_title() ) . '</h1>';
		echo '<form action="options.php" method="post">';
		settings_fields( $this->page );
		do_settings_sections( $this->page );
		wp_nonce_field( "{$this->page}_save-settings", "{$this->page}_nonce", false );
		submit_button();
		echo '</form>';
		echo '</div>';

	}

	/**
	 * Add isotope settings to 6/10 Press as a new tab, rather than creating a unique page.
	 * @param $tabs
	 *
	 * @return array
	 */
	public function add_tab( $tabs ) {
		$tabs[] = array( 'id' => 'simplemenus', 'tab' => __( 'Simple Menus', 'sixtenpress-simple-menus' ) );

		return $tabs;
	}

	/**
	 * Add new fields to wp-admin/options-general.php?page=sixtenpressisotope
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {
		register_setting( 'sixtenpresssimplemenus', 'sixtenpresssimplemenus', array( $this, 'do_validation_things' ) );
	}

	/**
	 * @return array $setting for plugin, or defaults.
	 */
	public function get_setting() {

		$defaults = array(
			'trickle' => 1,
		);
		$setting = get_option( 'sixtenpresssimplemenus', $defaults );

		return wp_parse_args( $setting, $defaults );
	}

	/**
	 * Define the array of post types for the plugin to show/use.
	 * @return array
	 */
	protected function post_types() {
		$args         = array(
			'public'      => true,
			'_builtin'    => false,
			'has_archive' => true,
		);
		$output       = 'names';
		$post_types   = get_post_types( $args, $output );
		$post_types[] = 'post';

		return $post_types;
	}

	/**
	 * Register sections for settings page.
	 *
	 * @since 0.1.0
	 */
	protected function register_sections() {
		
		$sections = array(
			'general' => array(
				'id'    => 'general',
				'tab'   => 'simplemenus',
				'title' => __( 'General Settings', 'sixtenpress-simple-menus' ),
			),
		);
		$post_types = array();
		$this->post_types = $this->post_types();
		if ( $this->post_types ) {
			$post_types = array(
				'cpt' => array(
					'id'    => 'cpt',
					'tab'   => 'simplemenus',
					'title' => __( 'Menu Settings for Content Types', 'sixtenpress-simple-menus' ),
				),
			);
		}

		return array_merge( $sections, $post_types );
	}

	/**
	 * Register settings fields
	 *
	 * @param  settings array $sections
	 *
	 * @return array $fields settings fields
	 *
	 * @since 1.0.0
	 */
	protected function register_fields( $fields = array() ) {

		$fields = array(
			array(
				'id'       => 'trickle',
				'title'    => __( 'Use Content Type/Term Menus', 'sixtenpress-simple-menus' ),
				'callback' => 'do_checkbox',
				'section'  => 'general',
				'args'     => array(
					'setting' => 'trickle',
					'label'   => __( 'Use content type/term menus on single posts if no menu is set', 'sixtenpress-simple-menus' ),
				),
			),
		);

		if ( $this->post_types ) {
			foreach ( $this->post_types as $post_type ) {
				$object   = get_post_type_object( $post_type );
				$label    = $object->labels->name;
				$fields[] = array(
					'id'       => '[post_types]' . esc_attr( $post_type ),
					'title'    => esc_attr( $label ),
					'callback' => 'set_post_type_options',
					'section'  => 'cpt',
					'args'     => array( 'post_type' => $post_type ),
				);
			}
		}

		return $fields;
	}

	/**
	 * Callback for the general section.
	 * @return string
	 */
	public function general_section_description() {
		return '';
	}
	/**
	 * Callback for the content types section description.
	 */
	public function cpt_section_description() {
		$description = __( 'Set the default secondary navigation for each content type.', 'sixtenpress-simple-menus' );
		printf( '<p>%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * Set the field for each post type.
	 * @param $args
	 */
	public function set_post_type_options( $args ) {
		$post_type  = $args['post_type'];
		$checkbox_args = array(
			'setting' => 'support',
			'label'   => __( 'Enable Simple Menus for this post type?', 'sixtenpress-simple-menus' ),
			'key'     => $post_type,
		);
		if ( 'post' !== $post_type ) {
			echo '<p>';
			$this->do_checkbox( $checkbox_args );
			echo '</p>';
		}
		$select_args = array(
			'options' => 'menus',
			'setting' => 'menu',
			'key'     => $post_type,
		);
		$this->do_select( $select_args );
	}

	/**
	 * Get the list of menus for each post type.
	 * @return mixed
	 */
	protected function pick_menus() {
		$options[''] = __( 'Default Secondary Menu', 'sixtenpress-simple-menus' );
		$menus       = wp_get_nav_menus( array( 'orderby' => 'name' ) );
		foreach ( $menus as $menu ) {
			$options[ $menu->term_id ] = $menu->name;
		}
		return $options;
	}

	/**
	 * Sanitize/validate all settings.
	 * @param $new_value
	 *
	 * @return array
	 */
	public function do_validation_things( $new_value ) {
		$action = $this->page . '_save-settings';
		$nonce  = $this->page . '_nonce';
		// If the user doesn't have permission to save, then display an error message
		if ( ! $this->user_can_save( $action, $nonce ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'sixtenpress' ) );
		}

		check_admin_referer( "{$this->page}_save-settings", "{$this->page}_nonce" );
		$diff = array_diff_key( $this->setting, $new_value );
		foreach ( $diff as $key => $value ) {
			if ( empty( $new_value[ $key ] ) ) {
				unset( $this->setting[ $key ] );
			}
		}
		$new_value = array_merge( $this->setting, $new_value );

		foreach ( $this->fields as $field ) {
			switch ( $field['callback'] ) {
				case 'do_checkbox':
					$new_value[ $field['id'] ] = $this->one_zero( $new_value[ $field['id'] ] );
					break;
				case 'do_select':
					$new_value[ $field['id'] ] = esc_attr( $new_value[ $field['id'] ] );
					break;
			}
		}
		foreach ( $this->post_types as $post_type ) {
			$new_value[ $post_type ]['menu']    = (int) $new_value[ $post_type ]['menu'];
			$new_value[ $post_type ]['support'] = $this->one_zero( $new_value[ $post_type ]['support'] );
		}

		return $new_value;
	}
}