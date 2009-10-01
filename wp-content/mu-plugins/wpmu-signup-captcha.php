<?php
/*
Plugin Name: WPMU-Signup-Captcha
Plugin URI: http://vdachev.net/
Description: Adds a captcha image to WordPress MU signup.
Author: Valery Dachev
Version: 1.3
Author URI: http://vdachev.net/

    Copyright 2007-2008  Valery Dachev  (email : contact@vdachev.net)

    Thanks to Chris W. and Shaythong for the fixes and suggestions made at WPMUDEV.ORG.
    Thanks to Alexander Bishop for testing plugin bugfixes.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function signup_init()
{
	session_start();
}

function signup_user_captcha_field($errors)
{
	$error = $errors->get_error_message('captcha');
?>
	<tr <?php echo($error ? 'class="error"' : '') ?>>
		<th valign="top"><?php _e('Verification:')?></th>
		<td>
<?php
        if($error)
	{
		echo '<strong>' . $error . '</strong><br />';
        }
?>
  			<img src="wp-captcha.php?rand=<?php echo rand(0,999999)?>" alt="Captcha code" border="0" />
                	<p>
	                	<input maxlength="12" name="ravin" type="text" value="" />
			</p>
		</td>
	</tr>
<?php
}
function signup_user_captcha_filter($content)
{
	if(! empty($_POST['ravin']))
		$_SESSION['goutiere'] = $_POST['ravin'];
	if(empty($_SESSION['goutiere']) || $_SESSION['pustule'] != $_SESSION['goutiere'])
		$content['errors']->add('captcha', __('Please enter correct verification number.'));
	return $content;
}

add_action('init', 'signup_init');
add_action('signup_extra_fields', 'signup_user_captcha_field');
add_filter('wpmu_validate_user_signup', 'signup_user_captcha_filter');
?>
