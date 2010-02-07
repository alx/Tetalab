<?php 

/**
 * Define the server path to the file wp-config here, 
 * if you placed WP-CONTENT outside the classic file structure 
 */

$path  = ''; // Should end with a trailing slash   

/**
 * That's all, don't edit stuff beyond this point. 
 */


if ( !defined('WP_LOAD_PATH') ) {

	// classic root path
	$classic_root = dirname(dirname(dirname(dirname(__FILE__)))).'/';

	if (file_exists( $classic_root . 'wp-load.php') )
		define( 'WP_LOAD_PATH', $classic_root);
	else
		if (file_exists( $path . 'wp-load.php') )
			define( 'WP_LOAD_PATH', $path);
		else
			exit("Could not find wp-load.php");
}


/**
 * The stuff above is needed because wp-content directory may now be outside of standard
 * directory structure. the solution adopted here is due to:
 * http://alexrabe.de/2008/08/06/the-hassle-with-wp_content_url-and-wp_plugin_dir/
 */


// let's load WordPress
require_once( WP_LOAD_PATH . 'wp-load.php');
require_once( WP_LOAD_PATH . 'wp-admin/includes/admin.php');

?>