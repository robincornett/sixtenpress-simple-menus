<?php

/**
 * @copyright 2016 Robin Cornett
 */
class SixTenPressSimpleMenusOutput {

	/**
	 * Post/term meta key.
	 * @var string
	 */
	protected $meta_key = '_sixtenpress_simplemenu';

	/**
	 * Replace the menu selected in the WordPress Menu settings with the custom one for this request
	 */
	public function replace_menu( $mods ) {

		$menu = $this->get_menu();
		if ( $menu ) {
			$mods['secondary'] = (int) $menu;
		}

		return $mods;
	}

	/**
	 * Return the custom menu for output to the filter.
	 *
	 * @param string $menu
	 *
	 * @return mixed|string
	 */
	protected function get_menu( $menu = false ) {

		/**
		 * If it's a singular post/page and we aren't trickling menus,
		 * get the menu and return.
		 */
		$setting = sixtenpresssimplemenus_get_setting();
		if ( is_singular() && ! $setting['trickle'] ) {
			return sixtenpresssimplemenus_get_menu();
		}

		// Sets menu based on post type. True for archives and singular.
		$post_type = get_post_type();
		if ( isset( $setting[ $post_type ]['menu'] ) ) {
			$menu = $setting[ $post_type ]['menu'];
		}

		// Check for term menus on taxonomy archives.
		if ( is_category() || is_tag() || is_tax() ) {
			$term = is_tax() ? get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ) : get_queried_object();
			if ( $term ) {
				$term_menu = sixtenpresssimplemenus_get_term_meta( $term, $this->meta_key );
				$menu      = $term_menu ? $term_menu : $menu;
			}
		}

		// Set the menu on a singular post/page.
		if ( is_singular() ) {
			$menu = $this->get_singular_menu( $menu, $post_type );
		}

		return $menu;
	}

	/**
	 * Set menu on singular posts/pages. Uses post type menu as the default,
	 * then check for a term menu, and override if there is a singular menu.
	 * @param $menu
	 *
	 * @return int|mixed
	 */
	protected function get_singular_menu( $menu, $post_type ) {

		// Check for a specifically set menu. If there is one, run.
		$post_menu = sixtenpresssimplemenus_get_menu();
		if ( $post_menu ) {
			return $post_menu;
		}
		// No menu. Check for a term menu.
		$post_menu = $this->get_term_menu( $menu );
		// No menu. Check for a parent page menu.
		if ( is_post_type_hierarchical( $post_type ) && ! $post_menu ) {
			$parent_ID = $this->get_parent_ID();
			$post_menu = sixtenpresssimplemenus_get_menu( $parent_ID );
		}
		// Return the post menu if there is one, otherwise return what we started with.
		return $post_menu ? $post_menu : $menu;
	}

	/**
	 * Get the menu related to the most popular taxonomy tied to the post.
	 *
	 * @param $menu
	 *
	 * @return int
	 */
	function get_term_menu( $menu ) {

		$taxonomies = get_taxonomies();
		$args       = array( 'orderby' => 'count', 'order' => 'DESC' );
		$terms      = wp_get_object_terms( get_the_ID(), $taxonomies, $args );

		if ( ! $terms ) {
			return $menu;
		}

		foreach ( $terms as $term ) {
			$menu = sixtenpresssimplemenus_get_term_meta( $term, $this->meta_key );
			if ( $menu ) {
				break;
			}
		}

		return $menu;
	}

	/**
	 * Get the parent post/page menu, if it exists.
	 * @return false|int
	 */
	protected function get_parent_ID() {
		// Get an array of Ancestors and Parents if they exist
		$parents = array_reverse( get_post_ancestors( get_the_ID() ) );
		// Get the top Level page->ID
		return (array) $parents ? $parents[0] : get_the_ID();
	}
}
