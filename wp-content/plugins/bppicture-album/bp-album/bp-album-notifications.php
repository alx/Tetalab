<?php

function bp_picture_new_wire_post_notification( $picture_id, $content ) {
	global $bp;

	/* Let's grab both user's names to use in the email. */
	$sender_name = bp_fetch_user_fullname( $bp->loggedin_user->id, false );
	$reciever_name = bp_fetch_user_fullname( $bp->displayed_user->id, false );
    
	if ( 'no' == get_usermeta( (int)$to_user_id, 'notification_wire_post_picture' ) )
		return false;

	/* Get the userdata for the reciever and sender, this will include usernames and emails that we need. */
	$reciever_ud = get_userdata( $bp->displayed_user->id );
	$sender_ud = get_userdata( $bp->loggedin_user->id );

	/* Now we need to construct the URL's that we are going to use in the email */
	$sender_profile_link = site_url( BP_MEMBERS_SLUG . '/' . $sender_ud->user_login . '/' . $bp->profile->slug );
	$picture_link = site_url( BP_MEMBERS_SLUG . '/' . $reciever_ud->user_login . '/' . $bp->album->slug . '/picture/'.$picture_id);
	$reciever_settings_link = site_url( BP_MEMBERS_SLUG . '/' . $reciever_ud->user_login . '/settings/notifications' );

	/* Set up and send the message */
	$to = $reciever_ud->user_email;
	$subject = '[' . get_blog_option( 1, 'blogname' ) . '] ' . sprintf( __( '%s has posted a wire on picture!', 'bp-album' ), $sender_name );

	$message = sprintf( __(
'%s has posted following wire on picture : %s
---------------------

%s

---------------------

To see %s\'s profile: %s
', 'bp-example' ), $sender_name, $picture_link, $content,$sender_name, $sender_profile_link);

	$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'bp-album' ), $reciever_settings_link );

	// Send it!
	wp_mail( $to, $subject, $message );
}
add_action('picture_new_wire_post','bp_picture_new_wire_post_notification', 10, 2);
?>
