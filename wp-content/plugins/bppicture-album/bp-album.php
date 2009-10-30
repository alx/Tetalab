<?php
/*
Plugin Name: bpPicture Album
Description: You can upload pictures to your album and post wire messages on pictures.
Version: 0.023
Requires at least: WPMU 2.7.1, BuddyPress 1
Tested up to: WPMU 2.7.1, BuddyPress 1
License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: Manoj Kumar
Author URI: http://manojkumar.org
Site Wide Only: true
*/

define ( 'BP_ALBUM_IS_INSTALLED', 1 );
define ( 'BP_ALBUM_VERSION', '0.001' );
define ( 'BP_ALBUM_DB_VERSION', '0.001' );

if ( !defined( 'BP_ALBUM_SLUG' ) )
	define ( 'BP_ALBUM_SLUG', 'album' );

require ( 'bp-album/bp-album-classes.php' );
require ( 'bp-album/bp-album-cssjs.php' );
require ( 'bp-album/bp-album-templatetags.php' );
require ( 'bp-album/bp-album-notifications.php' );

require_once( ABSPATH . '/wp-admin/includes/image.php' );
require_once( ABSPATH . '/wp-admin/includes/file.php' );


function bp_album_install() {
	global $wpdb, $bp;

    if ( ! empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	if ( ! empty($wpdb->collate) )
		$charset_collate .= " COLLATE $wpdb->collate";
	
    $sql[] = "CREATE TABLE {$bp->album->table_picture_data} (
                            id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            user_id bigint(20) NOT NULL,
                            date_uploaded datetime NOT NULL,
                            title varchar(250) NOT NULL,
                            description longtext NOT NULL,
                            status varchar(10) NOT NULL default 'public',
                            enable_wire tinyint(1) NOT NULL default '1',
                            pic_org_path varchar(250) NOT NULL,
                            pic_org_path_act varchar(250) NOT NULL,
                            pic_mid_path varchar(250) NOT NULL,
                            pic_mid_path_act varchar(250) NOT NULL,
                            pic_small_path varchar(250) NOT NULL,
                            pic_small_path_act varchar(250) NOT NULL,
                            KEY user_id (user_id),
                            KEY status (status)
                            ) {$charset_collate};";

    
	require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );
    dbDelta($sql);

    if ( function_exists('bp_wire_install') )
		picture_wire_install();


	update_site_option( 'bp-album-db-version', BP_ALBUM_DB_VERSION );
}

function picture_wire_install() {
	global $wpdb, $bp;

	if ( !empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

	$sql[] = "CREATE TABLE {$bp->album->table_name_wire} (
	  		id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			item_id bigint(20) NOT NULL,
			user_id bigint(20) NOT NULL,
			content longtext NOT NULL,
			date_posted datetime NOT NULL,
			KEY item_id (item_id),
			KEY user_id (user_id)
	 	   ) {$charset_collate};";

	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	dbDelta($sql);
}

/* Load the language file */
if ( file_exists( dirname(__FILE__) . '/bp-album/langs/bp-album-' . get_locale() . '.mo' ) )
	load_textdomain( 'bp-album', dirname(__FILE__) . '/bp-album/langs/bp-album-' . get_locale() . '.mo' );

function bp_album_setup_globals() {
	global $bp, $wpdb;

	$bp->album->table_picture_data = $wpdb->base_prefix . 'bp_picture_data';
	$bp->album->image_base = WP_PLUGIN_URL . '/bp-album/images';
	$bp->album->format_activity_function = 'bp_album_format_activity';
	$bp->album->format_notification_function = 'bp_album_format_notifications';

    if ( function_exists('bp_wire_install') )
		$bp->album->table_name_wire = $wpdb->base_prefix . 'bp_picture_wire';

	$bp->album->slug = BP_ALBUM_SLUG;
    $bp->version_numbers->album = BP_ALBUM_VERSION;
}
add_action( 'plugins_loaded', 'bp_album_setup_globals', 5 );
add_action( 'admin_menu', 'bp_album_setup_globals', 1 );



function bp_album_check_installed() {
	global $wpdb, $bp;

	if ( !is_site_admin() )
		return false;

    require ( 'bp-album/bp-album-admin.php' );

    if ( get_site_option('bp-album-db-version') < BP_ALBUM_DB_VERSION )
		bp_album_install();
}
add_action( 'admin_menu', 'bp_album_check_installed' );


function bp_album_add_admin_menu() {
	global $wpdb, $bp;

	if ( !is_site_admin() )
		return false;
    add_submenu_page( 'bp-core.php', __( 'Album Admin', 'bp-album' ), __( 'Album Admin', 'bp-album' ), 1, "bp-album/bp-album-admin.php", "bp_album_admin" );
}
add_action( 'admin_menu', 'bp_album_add_admin_menu' );


function bp_album_setup_nav() {
	global $bp;

        bp_core_new_nav_item(
		__( 'Album', 'bp-album' ), /* The display name */
		$bp->album->slug /* The slug */
        );

        /* Set a specific sub nav item as the default when the top level item is clicked */
        bp_core_new_nav_default(
            $bp->album->slug, /* The slug of the parent nav item */
            'bp_album_picture', /* The function to run when clicked */
            'picture' /* The slug of the sub nav item to make default */
        );

        $album_link = $bp->loggedin_user->domain . $bp->album->slug . '/';

        /* Create two sub nav items for this component */
        bp_core_new_subnav_item(
            $bp->album->slug, /* The slug of the parent */
            'picture', /* The slug for the sub nav item */
            __( 'Pictures', 'bp-album' ), /* The display name for the sub nav item */
            $album_link, /* The URL of the parent */
            'bp_album_picture' /* The function to run when clicked */
        );

        bp_core_new_subnav_item( $bp->album->slug, 'upload', __('Upload Picture', 'bp-album'), $album_link, 'picture_upload_screen', false, bp_is_home() );

        if ( $bp->current_component == $bp->album->slug ) {
            if ( bp_is_home() ) {
                /* If the user is viewing their own profile area set the title to "My Example" */
                $bp->bp_options_title = __( 'My Album', 'bp-album' );
            } else {
                /* If the user is viewing someone elses profile area, set the title to "[user fullname]" */
                $bp->bp_options_avatar = bp_core_get_avatar( $bp->displayed_user->id, 1 );
                $bp->bp_options_title = $bp->displayed_user->fullname;
            }
	}
    
}
add_action( 'wp', 'bp_album_setup_nav', 2 );
add_action( 'admin_menu', 'bp_album_setup_nav', 2 );


function bp_album_picture() {
	global $bp;

    
	do_action( 'bp_album_picture' );

    /**
	 * Finally, load the template file.
	 */

    if ($bp->current_component == $bp->album->slug && $bp->current_action=='picture' && !$bp->action_variables)
        bp_core_load_template( 'album/pictures' );

    if ($bp->current_component == $bp->album->slug && $bp->current_action=='picture' && $bp->action_variables[0]=='post-wire'){

            global $single_pic_template;

            if ( !picture_new_wire_post( $_POST['pic_id'], $_POST['wire-post-textarea'] ) ) {
				bp_core_add_message( __('Wire message could not be posted.', 'bp-album'), 'error' );
			} else {
				bp_core_add_message( __('Wire message successfully posted.', 'bp-album') );
			}
        	bp_core_redirect( $_SERVER['HTTP_REFERER'] );
    }

    if ($bp->current_component == $bp->album->slug && $bp->current_action=='picture' && $bp->action_variables[0]=='delete-wire'){
             if ( check_admin_referer( 'bp_picture_wire_delete_link' ) ){

                $wire_message_id = $bp->action_variables[1];

                if ( !picture_delete_wire_post( $wire_message_id, $bp->album->table_name_wire ) ) {
                    bp_core_add_message( __('There was an error deleting the wire message.', 'bp-album'), 'error' );
                } else {
                    bp_core_add_message( __('Wire message successfully deleted.', 'bp-album') );
                }

                bp_core_redirect( $_SERVER['HTTP_REFERER'] );
            }
    }

    if ($bp->current_component == $bp->album->slug && $bp->current_action=='picture' && $bp->action_variables[0]=='delete-picture'){
             if ( check_admin_referer( 'bp_single_pic_delete_link' ) ){

                $picture_id = $bp->action_variables[1];

                if ( !album_delete_picture( $picture_id ) ) {
                    bp_core_add_message( __('There was an error deleting the picture.', 'bp-album'), 'error' );
                } else {
                    bp_core_add_message( __('Picture successfully deleted.', 'bp-album') );
                }

                bp_core_redirect( $_SERVER['HTTP_REFERER'] );
            }
    }

    if ($bp->current_component == $bp->album->slug && $bp->current_action=='picture' && $bp->action_variables)
        bp_core_load_template( 'album/single' );

	 
}




function picture_upload_screen(){

    global $bp, $current_user, $bp_picture_updated, $pass_error, $srcurl,$message,$type,$wp_upload_error;

	if ( isset( $_POST['submit'] ) && check_admin_referer('bp-picture-upload') ) {

            if (isset( $_POST['title']) && $_POST['title'] !=''){

                $tile_check = true;

            }else{

                $tile_check = false;
                $type = 'error';
                $message = __('Picture Title can not be blank.', 'bp-album');

            }

            if (isset( $_POST['description']) && $_POST['description'] !='' && $tile_check){
                
                $description_check = true;

            }elseif ($tile_check){

                $description_check = false;
                $type = 'error';
                $message = __('Picture Description can not be blank.', 'bp-album');
            }

            
                // Set friendly error feedback.
                $uploadErrors = array(
                        0 => __("There is no error, the file uploaded with success", 'bp-album'),
                        1 => __("Your image was bigger than the maximum allowed file size of: ", 'bp-album') . size_format(CORE_MAX_FILE_SIZE),
                        2 => __("Your image was bigger than the maximum allowed file size of: ", 'bp-album') . size_format(CORE_MAX_FILE_SIZE),
                        3 => __("The uploaded file was only partially uploaded", 'bp-album'),
                        6 => __("Missing a temporary folder", 'bp-album')
                );

            if ( isset($_FILES['file']) && $description_check && $tile_check) {


                if ( 4 !== $_FILES['file']['error'] ) {
                    if ( !$checked_upload = bp_core_check_avatar_upload($_FILES) ) {
                        $avatar_error = true;
                        $avatar_error_msg = $uploadErrors[$_FILES['file']['error']];
                    }

                    if ( $checked_upload && !$checked_size = bp_core_check_avatar_size($_FILES) ) {
                        $avatar_error = true;
                        $avatar_size = size_format(CORE_MAX_FILE_SIZE);
                        $avatar_error_msg = sprintf( __('The file you uploaded is too big. Please upload a file under %s', 'bp-album'), $avatar_size);
                    }

                    if ( $checked_upload && $checked_size && !$checked_type = bp_core_check_avatar_type($_FILES) ) {
                        $avatar_error = true;
                        $avatar_error_msg = __('Please upload only JPG, GIF or PNG photos.', 'bp-album');
                    }

                    // "Handle" upload into temporary location
                    if ( $checked_upload && $checked_size && $checked_type && !$pic = bp_picture_handle_upload($_FILES)) {
                        $avatar_error = true;
                        $avatar_error_msg = sprintf( __('Upload Failed! Error was: %s', 'bp-album'), $wp_upload_error );
                    }
                    
                    
                    if (!$avatar_error){
                        //Original Picture
                        //$pic = wp_handle_upload( $_FILES['file'], array('action'=>'picture_upload') );

                        $pic_org_act = $pic['file'];
                        $pic_org_url = str_replace(array(ABSPATH),'/',$pic['file']);


                        //Mid Size Picture

                        if ( !$mid_size )
                            $mid_size = 600;

                        $mid_size_pic = wp_create_thumbnail( $pic['file'], $mid_size );

                        if ($mid_size_pic){
                            $mid_size_pic = str_replace( '//', '/', $mid_size_pic );
                            $pic_mid_act = $mid_size_pic; //str_replace( array(ABSPATH), array(site_url() . '/'), $mid_size_pic );
                            $pic_mid_url = str_replace(array(ABSPATH),'/',$mid_size_pic);

                        }else{
                            // If Original Picture's Size is less than $mid_size
                            $mid_size_pic = $pic_org_act;
                            $pic_mid_act = $mid_size_pic; //str_replace( array(ABSPATH), array(site_url() . '/'), $mid_size_pic );
                            $pic_mid_url = str_replace(array(ABSPATH),'/',$mid_size_pic);
                        }

                        //Small Size Picture - Will be used for thumbnails

                        if ( !$small_size )
                            $small_size = 150;

                        $small_size_pic = wp_create_thumbnail( $pic['file'], $small_size );

                        if ($small_size_pic){
                            $small_size_pic = str_replace( '//', '/', $small_size_pic );
                            $pic_small_act = $small_size_pic;//str_replace( array(ABSPATH), array(site_url() . '/'), $small_size_pic );
                            $pic_small_url = str_replace(array(ABSPATH),'/',$small_size_pic);

                        }else{
                            // If Original Picture's Size is less than $small_size_pic
                            $small_size_pic = $pic_org_act;
                            $pic_small_act = $small_size_pic;//str_replace( array(ABSPATH), array(site_url() . '/'), $small_size_pic );
                            $pic_small_url = str_replace(array(ABSPATH),'/',$small_size_pic);
                        }


                        $canvas = str_replace( '//', '/', $mid_size_pic );

                        $srcurl = str_replace( array(ABSPATH), array(site_url() . '/'), $canvas );

                        $imagedetails = wp_read_image_metadata($res['file']);

                        $pic_data = new BP_Album_Picture;
                        $pic_data->user_id = $bp->loggedin_user->id;
                        $pic_data->date_uploaded = time();
                        $pic_data->title = $_POST['title'];
                        $pic_data->description = $_POST['description'];
                        $pic_data->status = 'public';
                        $pic_data->enable_wire = 1;
                        $pic_data->pic_org_path = $pic_org_url;
                        $pic_data->pic_org_path_act = $pic_org_act;
                        $pic_data->pic_mid_path = $pic_mid_url;
                        $pic_data->pic_mid_path_act = $pic_mid_act;
                        $pic_data->pic_small_path = $pic_small_url;
                        $pic_data->pic_small_path_act = $pic_small_act;

                        if( !$pic_data->save() ) {
                                    $type = 'error';
                                    $message = __('There were problems saving your information.', 'bp-album');

                                    }else {
                                    $type = 'success';
                                    $message = __('Changes saved.', 'bp-album');

                                };

                        }else{
                            $type = 'error';
                            $message = $avatar_error_msg;

                        }
                }else{
                        $type = 'error';
                        $message = __('There was an error while uploading file', 'bp-album');
                }
                
            }


            



        $bp_picture_updated = true; // This means that all checks are completed
	}


     do_action( 'bp_before_picture_upload' );

     add_action( 'bp_template_content_header', 'bp_picture_upload_header' );
	 add_action( 'bp_template_title', 'bp_picture_upload_title' );
	 add_action( 'bp_template_content', 'bp_picture_upload_content' );

     do_action( 'bp_after_picture_upload' );

	 /* Finally load the plugin template file. */
	 bp_core_load_template( 'plugin-template' );


}


function bp_picture_handle_upload($file) {
	global $wp_upload_error;

	// Change the upload file location to /album/user_id
	add_filter( 'upload_dir', 'bp_picture_upload_dir' );

	$res = wp_handle_upload( $file['file'], array('action'=>'picture_upload') );

	if ( !in_array('error', array_keys($res) ) ) {
		return $res;
	} else {
		$wp_upload_error = $res['error'];
		return false;
	}
}

function bp_picture_upload_dir( $upload, $user_id = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->loggedin_user->id;

	$path  = get_blog_option( 1, 'upload_path' );
	$newdir = path_join( ABSPATH, $path );
	$newdir .= '/album/' . $user_id;

	$newbdir = $newdir;

	@wp_mkdir_p( $newdir );

	$newurl = trailingslashit( get_blog_option( 1, 'siteurl' ) ) . '/album/' . $user_id;
	$newburl = $newurl;
	$newsubdir = '/album/' . $user_id;

	return apply_filters( 'bp_picture_upload_dir', array( 'path' => $newdir, 'url' => $newurl, 'subdir' => $newsubdir, 'basedir' => $newbdir, 'baseurl' => $newburl, 'error' => false ) );
}
    function bp_picture_upload_header() {
		_e( 'Start Uploading your pictures...', 'bp-album' );
	}

	function bp_picture_upload_title() {
		_e( 'Upload a new picture', 'bp-album' );
	}

    function bp_picture_upload_content() {
		global $bp, $bp_picture_updated, $srcurl,$message,$type;
        
	?>
		<?php do_action( 'template_notices' ); // (error/success feedback)

         
        ?>

		<?php if ( $bp_picture_updated ) { ?>

            <?php
			if ( $message != '' ) {
				$type = ( 'error' == $type ) ? 'error' : 'updated';
		?>
			<div id="message" class="<?php echo $type; ?> fade">
				<p><?php echo $message; ?></p>
			</div>
		<?php } ?>

            
            <p><img src='<?php echo $srcurl ?>'/></p>
		<?php } ?>

		    <form method="post" enctype="multipart/form-data" name="bp-album-upload-form" id="bp-album-upload-form" class="standard-form">
       
                <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo CORE_MAX_FILE_SIZE; ?>" />
                <input type="hidden" name="action" value="picture_upload" />
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('picture_upload'); ?>" />
                
                <p>
                    <label><?php _e('Picture Title *', 'bp-album') ?><br />
                    <input type="text" name="title" id="title" size="100" tabindex="10" /></label>
                </p>
                <p>
                    <label><?php _e('Picture Description *', 'bp-album') ?><br />
                    <textarea name="description" id="description" rows="15" tabindex="11" cols="40"></textarea></label>
                </p>
                <p>
                    <label><?php _e('Select Picture to Upload *', 'bp-album') ?><br />
                    <input type="file" name="file" id="file" tabindex="12" /></label>
                </p>
                <input type="submit" tabindex="13" name="submit" id="submit" value="<?php _e( 'Upload Photo', 'bp-album' ) ?>"/>



			<?php
			/* This is very important, don't leave it out. */
			wp_nonce_field( 'bp-picture-upload' );
			?>
		</form>

    <?php
	}

/*
 * Wire Functions
 *
 */


function picture_new_wire_post( $picture_id, $content ) {
	global $bp;

	/* Check the nonce first. */
	if ( !check_admin_referer( 'bp_wire_post' ) )
		return false;

	$private = false;

	if ( $wire_post_id = bp_wire_new_post( $picture_id, $content, $bp->album->slug, $private ) ) {

        bp_core_add_notification( $picture_id, $bp->displayed_user->id, $bp->album->slug, 'picture_new_wire_post' );

        bp_picture_record_activity(
                    array(
                        'item_id' => $wire_post_id,
                        'user_id' => $bp->loggedin_user->id,
                        'component_name' => $bp->album->slug,
                        'component_action' => 'picture_new_wire_post',
                        'is_private' => 0
                    )
                );
        do_action( 'picture_new_wire_post', $picture_id, $content );

		return true;

	}

	return false;
}

function picture_delete_wire_post( $wire_post_id, $table_name ) {
	global $bp;
	
	/* Check the nonce first. */
	if ( !check_admin_referer( 'bp_picture_wire_delete_link' ) )
		return false;

	if ( bp_wire_delete_post( $wire_post_id, $bp->album->slug, $table_name ) ) {

        bp_picture_delete_activity( array(
                                            'item_id' => $wire_post_id,
                                            'component_name' => $bp->album->slug,
                                            'component_action' => 'picture_new_wire_post',
                                            'user_id' => $bp->loggedin_user->id
                                    )
                                );
        
		do_action( 'picture_deleted_wire_post', $wire_post_id );
		return true;
	}

	return false;
}


function bp_picture_record_activity( $args ) {
	if ( function_exists('bp_activity_record') ) {
		extract( (array)$args );
		bp_activity_record( $item_id, $component_name, $component_action, $is_private, $secondary_item_id, $user_id, $secondary_user_id, $recorded_time );
	}
}

function bp_picture_delete_activity( $args ) {
	if ( function_exists('bp_activity_delete') ) {
		extract( (array)$args );
		bp_activity_delete( $item_id, $component_name, $component_action, $user_id, $secondary_item_id );
	}
}


function bp_album_format_activity( $item_id, $user_id, $action, $secondary_item_id = false, $for_secondary_user = false ) {
	global $bp;

	/* $action is the 'component_action' variable set in the record function. */
	switch( $action ) {
		case 'picture_new_wire_post':

            $wire_post = new BP_Wire_Post( $bp->album->table_name_wire, $item_id );

			if (!$wire_post || !$wire_post->content )
				return false;

			$user_link = bp_core_get_userlink( $user_id );
			$picture_link = bp_get_picture_permalink( $wire_post->item_id);
			$post_excerpt = bp_create_excerpt( $wire_post->content );

			$content = sprintf ( __('%s wrote on the wire of %s', 'bp-album'), $user_link, '<a href="'.$picture_link.'">'.__("picture",'bp-album').'</a>' ) . ' <span class="time-since">%s</span>';
			$content .= '<blockquote>' . $post_excerpt . '</blockquote>';

			$content = apply_filters( 'bp_picture_wire_post_activity', $content, $user_link, $post_excerpt );

			return array(
				'primary_link' => $group_link,
				'content' => $content
			);
		break;
	}

	/* By adding a do_action here, people can extend your component with new activity items. */
	do_action( 'bp_album_format_activity', $action, $item_id, $user_id, $action, $secondary_item_id, $for_secondary_user );

	return false;
}


function bp_album_screen_notification_settings() {
	global $current_user;
	?>
	<table class="notification-settings" id="bp-album-notification-settings">
		<tr>
			<th class="icon"></th>
			<th class="title"><?php _e( 'Picture Album', 'bp-album' ) ?></th>
			<th class="yes"><?php _e( 'Yes', 'bp-album' ) ?></th>
			<th class="no"><?php _e( 'No', 'bp-album' )?></th>
		</tr>
		<tr>
			<td></td>
			<td><?php _e( 'A member can post wire on picture', 'bp-album' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[notification_wire_post_picture]" value="yes" <?php if ( !get_usermeta( $current_user->id,'notification_wire_post_picture') || 'yes' == get_usermeta( $current_user->id,'notification_wire_post_picture') ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[notification_wire_post_picture]" value="no" <?php if ( get_usermeta( $current_user->id,'notification_wire_post_picture') == 'no' ) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<?php do_action( 'bp_album_notification_settings' ); ?>
	</table>
<?php
}
add_action( 'bp_notification_settings', 'bp_album_screen_notification_settings' );




function bp_album_format_notifications( $action, $item_id, $secondary_item_id, $total_items ) {
	global $bp;

	switch ( $action ) {
		case 'picture_new_wire_post':

			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_album_picture_new_wire_post_notification', '<a href="' . $bp->loggedin_user->domain . $bp->album->slug . '/picture/"' . '">' . sprintf( __( '%d New Wire Posts on Picture', 'bp-album' ), (int)$total_items ) . '</a>', $total_items );
			} else {
				return apply_filters( 'bp_album_picture_new_wire_post_notification', '<a href="' . $bp->loggedin_user->domain . $bp->album->slug .'/picture/'.$item_id.'">' . __( 'New Wire Post on Picture', 'bp-album' ). '</a>', $item_id);
			}
		break;
	}

	do_action( 'bp_album_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

	return false;
}


function album_delete_picture( $picture_id ) {
	global $bp;

	
	if (($bp->displayed_user->id != $bp->loggedin_user->id)){
        if (!is_site_admin()) return false;
    }


	$pic = new BP_Album_Picture($picture_id);

	if ( !$pic->delete() )
		return false;

	return true;

}

function bp_album_remove_screen_notifications() {
	global $bp;
	bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, $bp->album->slug, 'picture_new_wire_post' );
}
add_action( 'bp_album_picture', 'bp_album_remove_screen_notifications' );



/*
 * More functions to retrive pictures
 */

function bp_pictures_get_pictures_for_user( $user_id ) {
	return BP_Album_Picture::get_pictures_for_user( $user_id );
}

?>
