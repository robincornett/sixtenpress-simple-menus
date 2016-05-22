# 6/10 Press Simple Menus

This plugin works within the Genesis Framework, to allow users to dynamically change their secondary menu throughout their site.

## Description

This plugin is a fork of *Genesis Simple Menus*. This plugin goes a step or two farther than the original, as it allows a series of fallback secondary menus based on terms and post types (the original required that the menu be set for each and every post). The plugin also checks parent pages for custom menus, so a child page will inherit the parent page menu settings.

Alternatively, you can also disable the inheritance function, at which point the plugin will behave as its predecessor does.

## Requirements
* WordPress 4.4, tested up to 4.5
* the Genesis Framework

## Installation

### Upload

1. Download the latest tagged archive (choose the "zip" option).
2. Go to the __Plugins -> Add New__ screen and click the __Upload__ tab.
3. Upload the zipped archive directly.
4. Go to the Plugins screen and click __Activate__.

### Manual

1. Download the latest tagged archive (choose the "zip" option).
2. Unzip the archive.
3. Copy the folder to your `/wp-content/plugins/` directory.
4. Go to the Plugins screen and click __Activate__.

Check out the Codex for more information about [installing plugins manually](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

### Git

Using git, browse to your `/wp-content/plugins/` directory and clone this repository:

`git clone git@github.com:robincornett/sixtenpress-simple-menus.git`

Then go to your Plugins screen and click __Activate__.

## Screenshots

## Frequently Asked Questions

### What if I've been using Genesis Simple Menus? Can I switch?

Yes, you should be able to. I've coded this to pull the existing metadata from Genesis Simple Menus and this plugin will attempt to honor those settings. This plugin will not run if Genesis Simple Menus is active.

### Why WordPress 4.4?

Because of term metadata and I didn't want to mess with ancient history compatibility.

## Credits

* Built by [Robin Cornett](http://robincornett.com/)

## Changelog

### 0.2.0
* Adds a check for parent page set menu

### 0.1.0
* Initial fork and release on Github
