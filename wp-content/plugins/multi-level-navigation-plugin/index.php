<?php
/*

Plugin Name: Multi-level Navigation Plugin
Plugin URI: http://pixopoint.com/multi-level-navigation/
Description: A WordPress plugin which adds a multi-level CSS based dropdown/flyout/slider menu to your WordPress blog. Visit the <a href="http://pixopoint.com/multi-level-navigation/">WP Multi-level Navigation Plugin page</a> for more information about the plugin, or our navigation <a href="http://pixopoint.com/forum/index.php?board=4.0">support board</a> for help with adding the menu to your theme.
Author: PixoPoint Web Development / Ryan Hellyer
Version: 2.2.1
Author URI: http://pixopoint.com/

Copyright (c) 2008 PixoPoint Web Development

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
license.txt file included with this plugin for more information.

*/

// Version number as displayed in source code
$pixopoint_mln_version = '2.2.1';

// Adds support for older versions of WordPress
if (!defined( 'WP_CONTENT_URL')) {define('WP_CONTENT_URL',get_option('siteurl').'/wp-content');}
if (!defined('WP_PLUGIN_URL')) {define('WP_PLUGIN_URL',WP_CONTENT_URL.'/plugins');}

// Attempt to add localization support
function my_init() {load_plugin_textdomain ('pixopoint_mln', "/wp-content/plugins/multi-level-navigation-plugin/languages/");}
add_action('init', 'my_init');

// Adds the various menu item options into an array
$suckerfish_menuitem = array(get_option('suckerfish_menuitem1'),get_option('suckerfish_menuitem2'),get_option('suckerfish_menuitem3'),get_option('suckerfish_menuitem4'),get_option('suckerfish_menuitem5'),get_option('suckerfish_menuitem6'),get_option('suckerfish_menuitem7'),get_option('suckerfish_menuitem8'),get_option('suckerfish_menuitem9'),get_option('suckerfish_menuitem10'));
$suckerfish_2_menuitem = array(get_option('suckerfish_2_menuitem1'),get_option('suckerfish_2_menuitem2'),get_option('suckerfish_2_menuitem3'),get_option('suckerfish_2_menuitem4'),get_option('suckerfish_2_menuitem5'),get_option('suckerfish_2_menuitem6'),get_option('suckerfish_2_menuitem7'),get_option('suckerfish_2_menuitem8'),get_option('suckerfish_2_menuitem9'),get_option('suckerfish_2_menuitem10'));

// Sets javsscript location and gets CSS
$javascript_location = WP_PLUGIN_URL.'/multi-level-navigation-plugin/scripts/';
$suckerfish_css = get_option('suckerfish_css');

// If in admin page, then load admin page stuff
if (is_admin()) {require('admin_page.php');}

// If maintenance mode is off then immediately loads plugin
elseif (get_option('suckerfish_maintenance') != 'on') {require('core.php');}

// Otherwise loads maintenance mode
else {require('maintenance.php');}

?>
