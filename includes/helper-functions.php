<?php
/**
 * @copyright 2016 Robin Cornett
 */

function sixtenpresssimplemenus_get_menu( $post_id = '' ) {
	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}
	$menu_key = get_post_meta( $post_id, '_sixtenpress_simplemenu', true );
	if ( ! $menu_key ) {
		$menu_key = get_post_meta( $post_id, '_gsm_menu', true );
	}
	return $menu_key;
}

/**
 * Get the term meta (generally headline or intro text). Backwards compatible,
 * but uses new term meta (as of Genesis 2.2.7)
 * @param $term object the term
 * @param $key string meta key to retrieve
 * @param string $value string output of the term meta
 *
 * @return mixed|string
 *
 */
function sixtenpresssimplemenus_get_term_meta( $term, $key, $value = '' ) {
	if ( ! $term ) {
		return $value;
	}
	if ( function_exists( 'get_term_meta' ) ) {
		$value = get_term_meta( $term->term_id, $key, true );
		if ( ! $value ) {
			get_term_meta( $term->term_id, '_gsm_menu', true );
		}
	}
	if ( ! $value && isset( $term->meta[ '_gsm_menu' ] ) ) {
		$value = $term->meta[ '_gsm_menu' ];
	}
	return $value;
}

/**
 * Helper function to get the plugin settings.
 * @return mixed|void
 *
 * @since 0.1.0
 */
function sixtenpresssimplemenus_get_setting() {
	return apply_filters( 'sixtenpresssimplemenus_get_setting', false );
}
