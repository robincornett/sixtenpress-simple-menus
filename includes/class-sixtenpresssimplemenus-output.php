<?php

/**
 * @copyright 2016 Robin Cornett
 */
class SixTenPressSimpleMenusOutput {

	protected $menu_key = '_sixtenpress_simplemenu';

	/*
	 * Once we hit wp_head, the WordPress query has been run, so we can determine if this request uses a custom subnav
	 */
	public function get_menu( $menu = '' ) {

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
				$term_menu = sixtenpresssimplemenus_get_term_meta( $term, $this->menu_key );
				$menu      = $term_menu ? $term_menu : $menu;
			}
		}
		if ( is_singular() ) {
			$post_menu = sixtenpresssimplemenus_get_menu();
			$menu      = $post_menu ? $post_menu : $menu;
		}

		return $menu;
	}

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
}
