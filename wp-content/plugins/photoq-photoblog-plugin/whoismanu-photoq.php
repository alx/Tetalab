<?php
/*
 Plugin Name: PhotoQ
 Version: 1.8.3
 Plugin URI: http://www.whoismanu.com/blog/
 Description: Adds queue based photo management and upload capability to WordPress.
 Author: M. Flury
 Author URI: http://www.whoismanu.com
 */

if(!defined(PHOTOQ_PATH)){
	//convert backslashes (windows) to slashes
	$cleanPath = str_replace('\\', '/', dirname(__FILE__));
	define('PHOTOQ_PATH', $cleanPath.'/');
}

//photoq should only be loaded in the admin section to save webserver cpu.
//on ajax calls is_admin() seems not to be ready yet -> we load photoq also whenever 
//we do ajax.
if(( defined('DOING_AJAX') && DOING_AJAX ) || (defined('EXPORTING_PHOTOQ_XML') && EXPORTING_PHOTOQ_XML ) || is_admin() ){
	
	//set PhotoQ debug level
	if (! defined('PHOTOQ_DEBUG_LEVEL')) {
		//define the debug levels
		define('PHOTOQ_DEBUG_OFF', '0');
		define('PHOTOQ_SHOW_PHP_ERRORS', '1');
		define('PHOTOQ_LOG_MESSAGES', '2');

		//set the debug level here
		define('PHOTOQ_DEBUG_LEVEL', PHOTOQ_DEBUG_OFF);
	}

	//set displaying of error messages
	if(PHOTOQ_DEBUG_LEVEL >= PHOTOQ_SHOW_PHP_ERRORS){
		ini_set('display_errors', 1);
		ini_set('error_reporting', E_ALL ^ E_NOTICE);
	}

	set_time_limit(0);

	//include all classes and libraries needed by PhotoQ
	if (!class_exists("PhotoQ")) {
		//Load PEAR_ErrorStack which is used for error handling.
		
		//careful if some other plugin already required ErrorStack (but from
		//a different path we are not allowed to redefine
		if (!class_exists("ErrorStack"))
			require_once(PHOTOQ_PATH.'lib/PEAR_ErrorStack/ErrorStack.php');
		
		//include the other files required by photoq
		require_once(PHOTOQ_PATH.'classes/PhotoQObject.php');
		require_once(PHOTOQ_PATH.'classes/PhotoQError.php');
		require_once(PHOTOQ_PATH.'classes/PhotoQHelpers.php');
		require_once(PHOTOQ_PATH.'classes/PhotoQ.php');
		
		// import ReusableOptions Library, same here add safety check
		if (!class_exists("OptionController"))
			require_once(PHOTOQ_PATH.'lib/ReusableOptions/OptionController.php');
		//import remaining PhotoQ classes
		require_once(PHOTOQ_PATH.'classes/PhotoQOptionController.php');
		require_once(PHOTOQ_PATH.'classes/PhotoQDB.php');
		require_once(PHOTOQ_PATH.'classes/PhotoQQueue.php');
		require_once(PHOTOQ_PATH.'classes/PhotoQBatch.php');
		require_once(PHOTOQ_PATH.'classes/PhotoQPhoto.php');
		require_once(PHOTOQ_PATH.'classes/PhotoQExif.php');
		require_once(PHOTOQ_PATH.'classes/PhotoQImageSize.php');
	}
	

	if (class_exists("PhotoQ")) {

		PhotoQHelper::debug('enter photoq exists()');
		//$timer =& PhotoQSingleton::getInstance('PhotoQTimers');
		//$timer->start('photoQInit');
		
		$photoq = new PhotoQ();
		//print_r($timer->stop('photoQInit'));
		
		/*in the case where batch upload is enabled, we have to override the pluggable functions
		 responsible for reading auth cookie, so that they allow login info to be submitted via post
		 or get request. The reason is that the upload request comes from the flash script which doesn't
		 have access to the user, password cookie. Try to minimize this, so only do it when something is uploaded.
		 */
		if ( !function_exists('wp_validate_auth_cookie') && $photoq->_oc->getValue('enableBatchUploads') && isset($_POST['batch_upload']) ) :
		function wp_validate_auth_cookie($cookie = '', $scheme = 'auth') {
				
			//here starts the part that is new -- get cookie value from request, model taken from media.php
			global $photoq;
			if ( is_ssl() && empty($_COOKIE[SECURE_AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']) )
			$_COOKIE[SECURE_AUTH_COOKIE] = $_REQUEST['auth_cookie'];
			elseif ( empty($_COOKIE[AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']) )
			$_COOKIE[AUTH_COOKIE] = $_REQUEST['auth_cookie'];
			//here ends the part that is new -- the rest is copy paste from pluggable.php

			
			//this is for wordpress 2.7 or 2.7.1
			if( get_bloginfo('version') === '2.7' || get_bloginfo('version') === '2.7.1' ){
						
				if ( ! $cookie_elements = wp_parse_auth_cookie($cookie, $scheme) ) {
					do_action('auth_cookie_malformed', $cookie, $scheme);
					return false;
				}

				extract($cookie_elements, EXTR_OVERWRITE);

				$expired = $expiration;

				// Allow a grace period for POST and AJAX requests
				if ( defined('DOING_AJAX') || 'POST' == $_SERVER['REQUEST_METHOD'] )
				$expired += 3600;

				// Quick check to see if an honest cookie has expired
				if ( $expired < time() ) {
					do_action('auth_cookie_expired', $cookie_elements);
					return false;
				}

				$key = wp_hash($username . '|' . $expiration, $scheme);
				$hash = hash_hmac('md5', $username . '|' . $expiration, $key);

				if ( $hmac != $hash ) {
					do_action('auth_cookie_bad_hash', $cookie_elements);
					return false;
				}

				$user = get_userdatabylogin($username);
				if ( ! $user ) {
					do_action('auth_cookie_bad_username', $cookie_elements);
					return false;
				}

				do_action('auth_cookie_valid', $cookie_elements, $user);

				return $user->ID;
					
			}else{
					
					
				// this replaces the above in wp 2.8
					
				if ( ! $cookie_elements = wp_parse_auth_cookie($cookie, $scheme) ) {
					do_action('auth_cookie_malformed', $cookie, $scheme);
					return false;
				}

				extract($cookie_elements, EXTR_OVERWRITE);

				$expired = $expiration;

				// Allow a grace period for POST and AJAX requests
				if ( defined('DOING_AJAX') || 'POST' == $_SERVER['REQUEST_METHOD'] )
				$expired += 3600;

				// Quick check to see if an honest cookie has expired
				if ( $expired < time() ) {
					do_action('auth_cookie_expired', $cookie_elements);
					return false;
				}

				$user = get_userdatabylogin($username);
				if ( ! $user ) {
					do_action('auth_cookie_bad_username', $cookie_elements);
					return false;
				}

				$pass_frag = substr($user->user_pass, 8, 4);

				$key = wp_hash($username . $pass_frag . '|' . $expiration, $scheme);
				$hash = hash_hmac('md5', $username . '|' . $expiration, $key);

				if ( $hmac != $hash ) {
					do_action('auth_cookie_bad_hash', $cookie_elements);
					return false;
				}

				do_action('auth_cookie_valid', $cookie_elements, $user);

				return $user->ID;
			}
			
		}
		endif;


	}//if (class_exists("PhotoQ")) {
}//if(( defined('DOING_AJAX') && DOING_AJAX ) || is_admin() ){





?>
