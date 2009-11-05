<?php
/**
 * New User Administration Panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once('admin.php');

if ( !current_user_can('create_users') )
	wp_die(__('Cheatin&#8217; uh?'));

/** WordPress Registration API */
require_once( ABSPATH . WPINC . '/registration.php');

function admin_created_user_email( $text ) {
	return sprintf( __( "Hi,
You've been invited to join '%s' at
%s as a %s.
If you do not want to join this blog please ignore
this email. This invitation will expire in a few days.

Please click the following link to activate your user account:
%%s" ), get_bloginfo('name'), site_url(), wp_specialchars( $_REQUEST[ 'role' ] ) );
}
add_filter( 'wpmu_signup_user_notification_email', 'admin_created_user_email' );

function admin_created_user_subject( $text ) {
	return "[" . get_bloginfo('name') . "] Your blog invite";
}
add_filter( 'wpmu_signup_user_notification_subject', 'admin_created_user_subject' );

if ( isset($_REQUEST['action']) && 'adduser' == $_REQUEST['action'] ) {
	check_admin_referer('add-user');

	if ( ! current_user_can('create_users') )
		wp_die(__('You can&#8217;t create users.'));

	$user_login = preg_replace( "/\s+/", '', sanitize_user( $_REQUEST[ 'user_login' ], true ) );
	$user_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->users} WHERE user_login = %s AND user_email = %s", $user_login, $_REQUEST[ 'email' ] ) );
	if( $user_details ) {
		// Adding an existing user to this blog
		$new_user_email = wp_specialchars(trim($_REQUEST['email']));
		$redirect = 'user-new.php';
		$username = $user_details->user_login;
		$user_id = $user_details->ID;
		if( ($username != null && is_site_admin( $username ) == false ) && ( array_key_exists($blog_id, get_blogs_of_user($user_id)) ) ) {
			$redirect = add_query_arg( array('update' => 'addexisting'), 'user-new.php' );
		} else {
			if ( isset( $_POST[ 'noconfirmation' ] ) && is_site_admin() ) {
				add_existing_user_to_blog( array( 'user_id' => $user_id, 'role' => $_REQUEST[ 'role' ] ) );
				$redirect = add_query_arg( array('update' => 'addnoconfirmation'), 'user-new.php' );
			} else {
				$newuser_key = substr( md5( $user_id ), 0, 5 );
				add_option( 'new_user_' . $newuser_key, array( 'user_id' => $user_id, 'email' => $user_details->user_email, 'role' => $_REQUEST[ 'role' ] ) );
				$message = __("Hi,\n\nYou have been invited to join '%s' at\n%s as a %s.\nPlease click the following link to confirm the invite:\n%s\n");
				wp_mail( $new_user_email, sprintf( __( '[%s] Joining confirmation' ), get_option( 'blogname' ) ),  sprintf($message, get_option('blogname'), site_url(), $_REQUEST[ 'role' ], site_url("/newbloguser/$newuser_key/")));
				$redirect = add_query_arg( array('update' => 'add'), 'user-new.php' );
			}
		}
		wp_redirect( $redirect );
		die();
	} else {
		// Adding a new user to this blog
		$user_details = wpmu_validate_user_signup( $_REQUEST[ 'user_login' ], $_REQUEST[ 'email' ] );
		unset( $user_details[ 'errors' ]->errors[ 'user_email_used' ] );
		if ( is_wp_error( $user_details[ 'errors' ] ) && !empty( $user_details[ 'errors' ]->errors ) ) {
			$add_user_errors = $user_details[ 'errors' ];
		} else {
			$new_user_login = apply_filters('pre_user_login', sanitize_user(stripslashes($_REQUEST['user_login']), true));
			if ( isset( $_POST[ 'noconfirmation' ] ) && is_site_admin() ) {
				add_filter( 'wpmu_signup_user_notification', create_function('', '{return false;}') ); // Disable confirmation email
			}
			wpmu_signup_user( $new_user_login, $_REQUEST[ 'email' ], array( 'add_to_blog' => $wpdb->blogid, 'new_role' => $_REQUEST[ 'role' ] ) );
			if ( isset( $_POST[ 'noconfirmation' ] ) && is_site_admin() ) {
				$key = $wpdb->get_var( $wpdb->prepare( "SELECT activation_key FROM {$wpdb->signups} WHERE user_login = %s AND user_email = %s", $new_user_login, $_REQUEST[ 'email' ] ) );
				wpmu_activate_signup( $key );
				$redirect = add_query_arg( array('update' => 'addnoconfirmation'), 'user-new.php' );
			} else {
				$redirect = add_query_arg( array('update' => 'newuserconfimation'), 'user-new.php' );
			}
			wp_redirect( $redirect );
			die();
		}
	}
}

$title = __('Add New User');
$parent_file = 'users.php';

wp_enqueue_script('wp-ajax-response');
wp_enqueue_script('user-profile');
wp_enqueue_script('password-strength-meter');

require_once ('admin-header.php');

switch( $_GET[ 'update' ] ) {
	case "newuserconfimation":
		$messages[] = '<div id="message" class="updated fade"><p>' . __('Invitation email sent to new user. A confirmation link must be clicked before their account is created.') . '</p></div>';
		break;
	case "add":
		$messages[] = '<div id="message" class="updated fade"><p>' . __('Invitation email sent to user. A confirmation link must be clicked for them to be added to your blog.') . '</p></div>';
		break;
	case "addnoconfirmation":
		$messages[] = '<div id="message" class="updated fade"><p>' . __('User has been added to your blog.') . '</p></div>';
		break;
	case "addexisting":
		$messages[] = '<div id="message" class="updated fade"><p>' . __('That user is already a member of this blog.') . '</p></div>';
		break;
}

?>
<div class="wrap">
<?php screen_icon(); ?>
<h2 id="add-new-user"><?php _e('Add New User') ?></h2>

<?php if ( isset($errors) && is_wp_error( $errors ) ) : ?>
	<div class="error">
		<ul>
		<?php
			foreach ( $errors->get_error_messages() as $err )
				echo "<li>$err</li>\n";
		?>
		</ul>
	</div>
<?php endif;

if ( ! empty($messages) ) {
	foreach ( $messages as $msg )
		echo $msg;
} ?>

<?php if ( isset($add_user_errors) && is_wp_error( $add_user_errors ) ) : ?>
	<div class="error">
		<?php
			foreach ( $add_user_errors->get_error_messages() as $message )
				echo "<p>$message</p>";
		?>
	</div>
<?php endif; ?>
<div id="ajax-response"></div>

<?php
echo '<p>' . __( 'You can add new users to your blog in two ways:' ) . '<ol><li> ' . __( 'Enter the username and email address of an existing user on this site.' ) . '</li><li> ' . __( 'Enter the username and the email address of a person who is not already a member of this site. Choose the username carefully, it cannot be changed.' ) . '</li></ol></p>';
echo '<p>' . __( 'That person will be sent an email asking them to click a link confirming the invite. New users will then be sent an email with a randomly generated password and a login link.' ) . '</p>';
?>
<form action="" method="post" name="adduser" id="adduser" class="add:users: validate">
<?php wp_nonce_field('add-user') ?>
<?php
//Load up the passed data, else set to a default.
foreach ( array('user_login' => 'login', 'first_name' => 'firstname', 'last_name' => 'lastname',
				'email' => 'email', 'url' => 'uri', 'role' => 'role') as $post_field => $var ) {
	$var = "new_user_$var";
	if ( ! isset($$var) )
		$$var = isset($_POST[$post_field]) ? stripslashes($_POST[$post_field]) : '';
}
?>
<table class="form-table">
	<tr class="form-field form-required">
		<th scope="row"><label for="user_login"><?php _e('Username (required)') ?></label><input name="action" type="hidden" id="action" value="adduser" /></th>
		<td ><input name="user_login" type="text" id="user_login" value="<?php echo $new_user_login; ?>" aria-required="true" /></td>
	</tr>
	<tr class="form-field form-required">
		<th scope="row"><label for="email"><?php _e('E-mail (required)') ?></label></th>
		<td><input name="email" type="text" id="email" value="<?php echo $new_user_email; ?>" /></td>
	</tr>

	<tr class="form-field">
		<th scope="row"><label for="role"><?php _e('Role'); ?></label></th>
		<td><select name="role" id="role">
			<?php
			if ( !$new_user_role )
				$new_user_role = !empty($current_role) ? $current_role : get_option('default_role');
			wp_dropdown_roles($new_user_role);
			?>
			</select>
		</td>
	</tr>
	<?php if ( is_site_admin() ) { ?>
	<tr class="form-field">
		<th scope="row"><label for="noconfirmation"><?php _e('Skip Confirmation Email') ?></label></th>
		<td><input name="noconfirmation" type="checkbox" id="noconfirmation" value="1" /> <label for="noconfirmation"><?php _e( 'Site administrators can add a user without sending the confirmation email.' ); ?></label></td>
	</tr>
	<?php } ?>
</table>
<p class="submit">
	<input name="adduser" type="submit" id="addusersub" class="button-primary" value="<?php esc_attr_e('Add User') ?>" />
</p>
</form>

</div>
<?php
include('admin-footer.php');
?>
