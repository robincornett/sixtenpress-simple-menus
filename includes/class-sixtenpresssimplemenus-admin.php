<?php

/**
 * @package SixTenPressSimpleMenus
 * @copyright 2016 Robin Cornett
 */
class SixTenPressSimpleMenusAdmin {

	/**
	 * Metabox ID.
	 * @var string
	 */
	protected $handle = 'sixtenpress-post-metabox';

	/**
	 * Post/term meta key
	 * @var string
	 */
	protected $meta_key = '_sixtenpress_simplemenu';

	/**
	 * All public taxonomies
	 * @var null
	 */
	protected $taxonomies = null;

	/*
	 * Add the post metaboxes to the supported post types
	 */
	public function set_post_metaboxes() {

		foreach ( (array) get_post_types( array( 'public' => true ) ) as $post_type ) {
			$setting = sixtenpresssimplemenus_get_setting();
			if ( isset( $setting[ $post_type ]['support'] ) && $setting[ $post_type ]['support'] ) {
				add_post_type_support( $post_type, 'sixtenpress-simple-menus' );
			}
			if ( in_array( $post_type, array( 'post', 'page' ), true ) || post_type_supports( $post_type, 'sixtenpress-simple-menus' ) || post_type_supports( $post_type, 'genesis-simple-menus' ) ) {
				add_meta_box( $this->handle,
					__( 'Secondary Navigation', 'sixtenpress-simple-menus' ),
					array( $this, 'do_post_metabox' ),
					$post_type,
					'side',
					'low'
				);
			}
		}
	}

	/**
	 * Add metaboxes to supported taxonomy terms
	 */
	public function set_taxonomy_metaboxes() {

		$_taxonomies      = get_taxonomies( array( 'show_ui' => true, 'public' => true ) );
		$this->taxonomies = apply_filters( 'genesis_simple_menus_taxonomies', array_keys( $_taxonomies ) );

		if ( empty( $this->taxonomies ) || ! is_array( $this->taxonomies ) ) {
			return;
		}

		register_meta( 'term', $this->meta_key, array( $this, 'validate_term' ) );
		foreach ( $this->taxonomies as $taxonomy ) {
			add_action( "{$taxonomy}_edit_form_fields", array( $this, 'term_edit' ) );
			add_action( "edited_{$taxonomy}", array( $this, 'save_taxonomy_custom_meta' ) );
			add_action( "edit_{$taxonomy}", array( $this, 'save_taxonomy_custom_meta' ) );
		}
	}

	/**
	 * Does the metabox on the post edit page
	 */
	public function do_post_metabox() {
		echo '<p>';
			$this->print_menu_select( $this->meta_key, sixtenpresssimplemenus_get_menu() );
		echo '</p>';
	}

	/**
	 * Does the metabox on the term edit page
	 * @param $term
	 */
	public function term_edit( $term ) {

		$menu = sixtenpresssimplemenus_get_term_meta( $term, $this->meta_key );

		echo '<tr class="form-field">';
		printf( '<th scope="row" valign="top"><label for="%s">%s</label></th>',
			esc_attr( $this->meta_key ),
			esc_attr__( 'Secondary Navigation', 'sixtenpress-simple-menus' )
		);
		echo '<td>';
			$this->print_menu_select( $this->meta_key, $menu );
		echo '</td>';
		echo '</tr>';
	}

	/**
	 * Support function for the metaboxes, outputs the menu dropdown.
	 * @param $field_name
	 * @param $selected
	 */
	protected function print_menu_select( $field_name, $selected ) {

		printf( '<select name="%1$s" id="%1$s">', $field_name );
			printf ( '<option value="">%s</option>', __( 'Default Secondary Navigation', 'sixtenpress-simple-menus' ) );
			$menus = wp_get_nav_menus( array( 'orderby' => 'name' ) );
			foreach ( $menus as $menu ) {
				printf( '<option value="%d" %s>%s</option>', $menu->term_id, selected( $menu->term_id, $selected, false ), esc_html( $menu->name ) );
			}
		echo '</select>';
	}

	/**
	 * Handles the post save & stores the menu selection in the post meta
	 * @param $post_id
	 * @param $post
	 */
	public function save_post( $post_id, $post ) {

		//	don't try to save the data under autosave, ajax, or future post.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}
		if ( 'revision' === $post->post_type ) {
			return;
		}
		if ( isset( $_REQUEST['bulk_edit'] ) ) {
			return;
		}

		$perm = 'edit_' . ( 'page' === $post->post_type ? 'page' : 'post' ) . 's';
		if ( ! current_user_can( $perm, $post_id ) ) {
			return;
		}

		if ( empty( $_POST[ $this->meta_key ] ) ) {
			delete_post_meta( $post_id, $this->meta_key );
		} else {
			update_post_meta( $post_id, $this->meta_key, $_POST[$this->meta_key ] );
		}
	}

	/**
	 * Save the new term metadata.
	 * @param $term_id
	 */
	public function save_taxonomy_custom_meta( $term_id ) {

		if ( ! isset( $_POST[ $this->meta_key ] ) ) {
			return;
		}
		$input = $_POST[ $this->meta_key ];
		if ( empty( $input ) ) {
			delete_term_meta( $term_id, $this->meta_key );
			return;
		}
		$current_setting = get_term_meta( $term_id, $this->meta_key );
		if ( $current_setting !== $input ) {
			update_term_meta( $term_id, $this->meta_key, (int) $input );
		}
	}

	/**
	 * @param $input
	 *
	 * @return int|string
	 */
	function validate_term( $input ) {
		return ( empty( $input ) ) ? '' : (int) $input;
	}
}
