=== 6/10 Press Simple Menus ===

Contributors: littler.chicken
Donate link: https://robincornett.com/donate/
Tags: Genesis, StudioPress, secondary navigation, navigation
Requires at least: 4.4
Tested up to: 4.7
Stable tag: 0.3.0
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

This plugin allows users to dynamically change a specific menu location throughout their site.

== Description ==

This plugin is a fork of *Genesis Simple Menus*. This plugin goes a step or two farther than the original, as it allows a series of fallback secondary menus based on terms and post types (the original required that the menu be set for each and every post). The plugin also checks parent pages for custom menus, so a child page will inherit the parent page menu settings.

Alternatively, you can also disable the inheritance function, at which point the plugin will behave as its predecessor does.

**Update:** as of version 0.3.0, Six/Ten Press Simple Menus will work with any theme, not just Genesis child themes. Any registered navigation menu location can be selected for this plugin to modify.

== Installation ==

1. Upload the entire `sixtenpress-simple-menus` folder to your `/wp-content/plugins` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Optionally, visit the Settings > Media page to change the default behavior of the plugin.

== Frequently Asked Questions ==

= What if I've been using Genesis Simple Menus? Can I switch? =

Yes, you should be able to. I've coded this to pull the existing metadata from Genesis Simple Menus and this plugin will attempt to honor those settings. This plugin will not run if Genesis Simple Menus is active.

= Why WordPress 4.4? =

Because of term metadata and I didn't want to mess with ancient history compatibility.

== Upgrade Notice ==
= 0.3.0 =
Removes Genesis Framework dependency.

== Changelog ==

= 0.3.0 =
* Removes Genesis Framework dependency
* Updates settings files

= 0.2.0 =
* Adds a check for parent page set menu

= 0.1.0 =
* Initial fork and release on Github
