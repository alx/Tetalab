<?php
/**
 * Loads the WordPress environment and template.
 *
 * @package WordPress
 */

if ( !isset($wp_did_header) ) {
	// WPMU Runs installer if things aren't set up correctly
	if ( file_exists( dirname(__FILE__) . '/wp-config.php' ) || ( file_exists( dirname( dirname(__FILE__) ) . '/wp-config.php' ) && ! file_exists( dirname( dirname(__FILE__) ) . '/wp-settings.php' ) ) ) {
		$wp_did_header = true;

		require_once( dirname(__FILE__) . '/wp-load.php' );

		wp();

		require_once( ABSPATH . WPINC . '/template-loader.php' );
	} else {
		if ( strpos( $_SERVER[ 'PHP_SELF' ], 'wp-admin' ) !== false ) {
			$path = ''; 
		} else {
			$path = 'wp-admin/';
		}

		include( "index-install.php" ); // install WPMU!
		die();
	}

}

?>
