<?php

/***
 * This file is used to add site administration menus to the WordPress backend.
 *
 * If you need to provide configuration options for your component that can only
 * be modified by a site administrator, this is the best place to do it.
 *
 * However, if your component has settings that need to be configured on a user
 * by user basis - it's best to hook into the front end "Settings" menu.
 */

/**
 * bp_album_admin()
 *
 * Checks for form submission, saves component settings and outputs admin screen HTML.
 */
function bp_album_admin() {
	global $bp, $bbpress_live;

    ?>
	<div class="wrap">
		<h2><?php _e( 'Album Admin', 'bp-album' ) ?></h2>
		<br />
        <p><?php _e("Placeholder for admin options for Album settings",'bp-album') ?></p>
	</div>
<?php
}
?>