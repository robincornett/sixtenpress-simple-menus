<?php

/**
 * @package SixTenPressSimpleMenus
 * @copyright 2016 Robin Cornett
 */
class SixTenPressSimpleMenusAdmin {

	protected $handle     = 'sixtenpress-post-metabox';
	protected $nonce_key  = 'sixtenpress-post-metabox-nonce';
	protected $field_name = '_sixtenpress_simplemenu';
	protected $menu       = null;
	protected $taxonomies = null;

	/*
	 * Add the post metaboxes to the supported post types
	 */
	public function set_post_metaboxes() {

		$post_types = $this->get_post_types();
		foreach ( $post_types as $type ) {
			$setting = sixtenpresssimplemenus_get_setting();
			if ( isset( $setting[ $type ]['support'] ) && $setting[ $type ]['support'] ) {
				add_post_type_support( $type, 'sixtenpress-simple-menus' );
			}
			if ( in_array( $type, array( 'post', 'page' ), true ) || post_type_supports( $type, 'sixtenpress-simple-menus' ) || post_type_supports( $type, 'genesis-simple-menus' ) ) {
				add_meta_box( $this->handle, __( 'Secondary Navigation', 'sixtenpress-simple-menus' ), array(
					$this,
					'do_post_metabox',
				), $type, 'side', 'low' );
			}
		}
	}

	protected function get_post_types() {
		return (array) get_post_types( array( 'public' => true ) );
	}

	public function set_taxonomy_metaboxes() {

		$_taxonomies      = get_taxonomies( array( 'show_ui' => true, 'public' => true ) );
		$this->taxonomies = apply_filters( 'genesis_simple_menus_taxonomies', array_keys( $_taxonomies ) );

		if ( empty( $this->taxonomies ) || ! is_array( $this->taxonomies ) ) {
			return;
		}

		register_meta( 'term', $this->field_name, 'esc_attr' );
		foreach ( $this->taxonomies as $tax ) {
			add_action( "{$tax}_edit_form_fields", array( $this, 'term_edit' ) );
		}
	}

	/*
	 * Does the metabox on the post edit page
	 */
	public function do_post_metabox() {
		echo '<p>';
			$this->print_menu_select( $this->field_name, sixtenpresssimplemenus_get_menu() );
		echo '</p>';
	}

	/*
	 * Does the metabox on the term edit page
	 */
	public function term_edit( $term ) {

		$menu = sixtenpresssimplemenus_get_term_meta( $term, $this->field_name );

		echo '<tr class="form-field">';
		printf( '<th scope="row" valign="top"><label for="%s">%s</label></th>',
			esc_attr( $this->field_name ),
			esc_attr__( 'Secondary Navigation', 'sixtenpress-simple-menus' )
		);
		echo '<td>';
			$this->print_menu_select( $this->field_name, $menu );
		echo '</td>';
		echo '</tr>';
	}

	public function cpt_edit() {

	}

	/*
	 * Support function for the metaboxes, outputs the menu dropdown
	 */
	function print_menu_select( $field_name, $selected ) {

		printf( '<select name="%1$s" id="%1$s">', $field_name );
			printf ( '<option value="">%s</option>', __( 'Default Secondary Menu', 'sixtenpress-simple-menus' ) );
			$menus = wp_get_nav_menus( array( 'orderby' => 'name' ) );
			foreach ( $menus as $menu ) {
				printf( '<option value="%d" %s>%s</option>', $menu->term_id, selected( $menu->term_id, $selected, false ), esc_html( $menu->name ) );
			}
		echo '</select>';
	}

	/*
	 * Handles the post save & stores the menu selection in the post meta
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

		if ( empty( $_POST[ $this->field_name ] ) ) {
			delete_post_meta( $post_id, $this->field_name );
		} else {
			update_post_meta( $post_id, $this->field_name, $_POST[ $this->field_name ] );
		}
	}
}
