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

		$setting = sixtenpresssimplemenus_get_setting();

		if ( is_singular() && ! $setting['trickle'] ) {
			return sixtenpresssimplemenus_get_menu();
		}

		$post_type = get_post_type();
		if ( isset( $setting[ $post_type ]['menu'] ) ) {
			$menu = $setting[ $post_type ]['menu'];
		}
		if ( is_category() || is_tag() || is_tax() ) {
			$term = is_tax() ? get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ) : get_queried_object();
			if ( $term ) {
				$term_menu = sixtenpresssimplemenus_get_term_meta( $term, $this->meta_key );
				$menu      = $term_menu ? $term_menu : $menu;
			}
		}
		if ( is_singular() ) {
			$post_menu = false;
			if ( ! $menu ) {
				$post_menu = sixtenpresssimplemenus_get_menu() ? sixtenpresssimplemenus_get_menu() : $this->get_term_menu( $menu );
			}
			if ( is_page() && ! $post_menu ) {
				$parent_ID = $this->get_parent_ID();
				$post_menu = sixtenpresssimplemenus_get_menu( $parent_ID );
			}
			$menu = $post_menu ? $post_menu : $menu;
		}

		return $menu;
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

		foreach ( $terms as $term ) {
			$menu = sixtenpresssimplemenus_get_term_meta( $term, $this->meta_key );
			if ( $menu ) {
				break;
			}
		}

		return (int) $menu;
	}

	protected function get_parent_ID() {
		if ( ! is_page() ) {
			return false;
		}
		/* Get an array of Ancestors and Parents if they exist */
		$parents = array_reverse( get_post_ancestors( get_the_ID() ) );
		/* Get the top Level page->ID count base 1, array base 0 so -1 */
		return (array) $parents ? $parents[0] : get_the_ID();
	}
}
