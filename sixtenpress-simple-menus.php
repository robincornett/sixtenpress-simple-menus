<?php
/*
Plugin Name: SixTen Press Simple Menus
Plugin URI: https://robincornett.com/
Description: SixTen Press Simple Menus is a fork of Genesis Simple Menus.
Version: 0.1.0
Author: Robin Cornett
Author URI: https://robincornett.com/
Text Domain: sixtenpress-simple-menus
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
		'class-sixtenpresssimplemenus-settings',
		'helper-functions',
	);
	foreach ( $files as $file ) {
		require plugin_dir_path( __FILE__ ) . 'includes/' . $file . '.php';
	}
}
sixtenpresssimplemenus_require();

$sixtenpresssimplemenus_admin    = new SixTenPressSimpleMenusAdmin();
$sixtenpresssimplemenus_output   = new SixTenPressSimpleMenusOutput();
$sixtenpresssimplemenus_settings = new SixTenPressSimpleMenuSettings();

$sixtenpress_simplemenus = new SixTenPressSimpleMenus(
	$sixtenpresssimplemenus_admin,
	$sixtenpresssimplemenus_output,
	$sixtenpresssimplemenus_settings
);

// Run the plugin
$sixtenpress_simplemenus->run();
