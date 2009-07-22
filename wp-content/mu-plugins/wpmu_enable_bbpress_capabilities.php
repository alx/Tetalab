<?php
/*
Plugin Name: WPMU Enable bbPress Capabilities
Version: 0.1
Plugin URI: http://www.michaeleagan.com/plugins/wpmu-enable-bbpress-capabilities/
Author: Michael Eagan
Author URI: http://www.michaeleagan.com/
Description: Enables bbPress member capabilities when a user is created within WPMU. This allows immediate login as a 'member' after a user is created in WPMU.
*/

function enable_bbpress_forum_capabilities( $user_id )  {
	update_usermeta($user_id, 'bb_capabilities', array('member' => true));
	return;
}
add_action('wpmu_new_user', 'enable_bbpress_forum_capabilities');

?>
