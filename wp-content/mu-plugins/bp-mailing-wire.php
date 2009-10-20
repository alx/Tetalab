<?php
/*
Plugin Name: bp-mailing-list-wire
Plugin URI: http://tetalab.org/
Description: Adds mails from mailing list inside a group wire
Author: Alexandre Girard - Arne
Version: 1.0
Author URI: http://alexgirard.com/
*/

define ( 'BP_MAILING_GROUP_ID', '18' );

require ( WPMU_PLUGIN_DIR . '/bp-mailing-wire/email.php' );

function update_wire() {
	$content = "";
	#groups_new_wire_post( BP_MAILING_GROUP_ID, $content )
}

?>
