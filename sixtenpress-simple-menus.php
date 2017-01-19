<?php
/**
 * @package           SixTenPressSimpleMenus
 * @author            Robin Cornett
 * @link              https://github.com/robincornett/sixtenpress-simple-menus
 * @copyright         2015-2016 Robin Cornett
 * @license           GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name:       Six/Ten Press Simple Menus
 * Plugin URI:        https://github.com/robincornett/sixtenpress-simple-menus
 * Description:       Six/Ten Press Simple Menus is a fork of Genesis Simple Menus.
 * Version:           0.3.0
 * Author:            Robin Cornett
 * Author URI:        https://robincornett.com/
 * Text Domain:       sixtenpress-simple-menus
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/robincornett/sixtenpress-simple-menus
 * GitHub Branch:     master
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'SIXTENPRESSSIMPLEMENUS_BASENAME' ) ) {
	define( 'SIXTENPRESSSIMPLEMENUS_BASENAME', plugin_basename( __FILE__ ) );
}

// Include classes
function sixtenpresssimplemenus_require() {
	$files = array(
		'class-sixtenpresssimplemenus',
		'class-sixtenpresssimplemenus-admin',
		'class-sixtenpresssimplemenus-output',
		'helper-functions',
	);
	foreach ( $files as $file ) {
		require plugin_dir_path( __FILE__ ) . 'includes/' . $file . '.php';
	}
}
sixtenpresssimplemenus_require();

$sixtenpresssimplemenus_admin    = new SixTenPressSimpleMenusAdmin();
$sixtenpresssimplemenus_output   = new SixTenPressSimpleMenusOutput();

$sixtenpress_simplemenus = new SixTenPressSimpleMenus(
	$sixtenpresssimplemenus_admin,
	$sixtenpresssimplemenus_output
);

// Run the plugin
$sixtenpress_simplemenus->run();
