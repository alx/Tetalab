<?php
require_once('admin.php');

if ( !is_site_admin() )
    wp_die( __( 'You do not have permission to access this page.' ) );

if ( !isset( $_GET['action'] ) || !isset($_GET['plugin']) )
	wp_redirect( 'plugins.php' );
	
if ( 'activate' == $_GET['action'] ) {
	
	check_admin_referer( 'activate-sitewide-plugin' );
		
	if ( !activate_sitewide_plugin( $_GET['plugin'] ) ) {
		wp_redirect( 'plugins.php?error=true' );
	} else {
		do_action( 'activate_sitewide_plugin', $_GET['plugin'] );
		wp_redirect( 'plugins.php?activate=true' );
	}
	
} else if ( 'deactivate' == $_GET['action'] ) {
	
	check_admin_referer( 'deactivate-sitewide-plugin' );

	if ( !deactivate_sitewide_plugin( $_GET['plugin'] ) ) {
		wp_redirect( 'plugins.php?error=true' );
	} else {
		do_action( 'deactivate_sitewide_plugin', $_GET['plugin'] );
		wp_redirect( 'plugins.php?deactivate=true' );
	}
	
}

die;
?>