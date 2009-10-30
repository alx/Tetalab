<?php
/*
Plugin Name: WPMU-Signup-Captcha
Plugin URI: http://tetalab.org/
Description: Adds a recaptcha image to WordPress MU signup.
Author: Alexandre Girard
Version: 1.0
Author URI: http://alexgirard.com/
*/

function signup_init()
{
	session_start();
}

function signup_user_recaptcha($errors)
{
	$error = $errors->get_error_message('recaptcha');
	$publickey = "6LfsGAkAAAAAAAjzKmnvmCMaSlR1aIcQUtK9bA6w";
	echo recaptcha_get_html($publickey);
}
function signup_user_recaptcha_filter($content)
{
	require_once('recaptchalib.php');
	$privatekey = "6LfsGAkAAAAAAFOd7jkM69o3QIa7VG_Diio7e2tC ";
	$resp = recaptcha_check_answer ($privatekey,
	                                $_SERVER["REMOTE_ADDR"],
	                                $_POST["recaptcha_challenge_field"],
	                                $_POST["recaptcha_response_field"]);

	if (!$resp->is_valid) {
	  $content['errors']->add('captcha', __('The reCAPTCHA wasn\'t entered correctly.'));
	}
	
	return $content;
}

add_action('init', 'signup_init');
add_action('signup_extra_fields', 'signup_user_recaptcha');
add_filter('wpmu_validate_user_signup', 'signup_user_recaptcha_filter');
?>
