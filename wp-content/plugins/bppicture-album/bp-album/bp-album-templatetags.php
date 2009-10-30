<?php
class BP_User_Picture_Template {
	var $current_picture = -1;
	var $picture_count;
	var $pictures;
	var $picture;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_picture_count;

	function bp_user_picture_template( $user_id, $per_page, $max ) {
		global $bp;

		if ( !$user_id )
			$user_id = $bp->displayed_user->id;

		$this->pag_page = isset( $_GET['fpage'] ) ? intval( $_GET['fpage'] ) : 1;
		$this->pag_num = isset( $_GET['num'] ) ? intval( $_GET['num'] ) : $per_page;


		if ( !$this->pictures = wp_cache_get( 'bp_pictures_for_user_' . $user_id, 'bp' ) ) {
			$this->pictures = bp_pictures_get_pictures_for_user( $user_id );
			wp_cache_set( 'bp_pictures_for_user_' . $user_id, $this->pictures, 'bp' );
		}

        
        

		if ( !$max )
			$this->total_picture_count = (int)$this->pictures['count'];
		else
			$this->total_picture_count = (int)$max;

		$this->pictures = array_slice( (array)$this->pictures['pictures'], intval( ( $this->pag_page - 1 ) * $this->pag_num), intval( $this->pag_num ) );

		if ( $max ) {
			if ( $max >= count($this->pictures) )
				$this->picture_count = count($this->pictures);
			else
				$this->picture_count = (int)$max;
		} else {
			$this->picture_count = count($this->pictures);
		}

		$this->pag_links = paginate_links( array(
			'base' => add_query_arg( 'fpage', '%#%' ),
			'format' => '',
			'total' => ceil($this->total_picture_count / $this->pag_num),
			'current' => $this->pag_page,
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'mid_size' => 1
		));
    
	}

	function has_pictures() {
		if ( $this->picture_count )
			return true;

		return false;
	}

	function next_picture() {
		$this->current_picture++;
		$this->picture = $this->pictures[$this->current_picture];

		return $this->picture;
	}

	function rewind_pictures() {
		$this->current_picture = -1;
		if ( $this->picture_count > 0 ) {
			$this->picture = $this->pictures[0];
		}
	}

	function user_pictures() {
		if ( $this->current_picture + 1 < $this->picture_count ) {
			return true;
		} elseif ( $this->current_picture + 1 == $this->picture_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_pictures();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_picture() {
		global $picture;

		$this->in_the_loop = true;
		$picture = $this->next_picture();

		if ( 0 == $this->current_picture ) // loop has just started
			do_action('loop_start');
	}
}

function bp_has_pictures( $args = '' ) {
	global $pictures_template;

	$defaults = array(
		'user_id' => false,
		'per_page' => 9,
		'max' => false
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$pictures_template = new BP_User_Picture_Template( $user_id, $per_page, $max );
	return $pictures_template->has_pictures();
}

function bp_pictures() {
	global $pictures_template;
	return $pictures_template->user_pictures();
}

function bp_the_picture() {
	global $pictures_template;
	return $pictures_template->the_picture();
}

function bp_pictures_pagination_count() {
	global $bp, $pictures_template;

	$from_num = intval( ( $pictures_template->pag_page - 1 ) * $pictures_template->pag_num ) + 1;
	$to_num = ( $from_num + ( $pictures_template->pag_num - 1 ) > $pictures_template->total_picture_count ) ? $pictures_template->total_picture_count : $from_num + ( $pictures_template->pag_num - 1 ) ;

	echo sprintf( __( 'Viewing picture %d to %d (of %d pictures)', 'bp-album' ), $from_num, $to_num, $pictures_template->total_picture_count ); ?> &nbsp;
	<img id="ajax-loader-pictures" src="<?php echo $bp->core->image_base ?>/ajax-loader.gif" height="7" alt="<?php _e( "Loading", 'bp-album' ) ?>" style="display: none;" /><?php
}

function bp_pictures_pagination_links() {
	echo bp_get_pictures_pagination_links();
}
	function bp_get_pictures_pagination_links() {
		global $pictures_template;

		return apply_filters( 'bp_get_pictures_pagination_links', $pictures_template->pag_links );
	}

function bp_picture_title() {
	echo bp_get_picture_title();
}
	function bp_get_picture_title() {
		global $pictures_template;

		return apply_filters( 'bp_get_picture_title', $pictures_template->picture->title);
	}


function bp_picture_description() {
	echo bp_get_picture_description;
}
	function bp_get_picture_description() {
		global $pictures_template;

		return apply_filters( 'bp_get_picture_description', $pictures_template->picture->description);
	}


function bp_picture_small_link() {
    global $bp,$pictures_template;

    echo site_url().$pictures_template->picture->pic_small_path;
    
}

/* Functions for Single picture View
 * 
 */



function bp_picture_view_link() {
	echo bp_get_picture_view_link();
}
	function bp_get_picture_view_link() {
		global $pictures_template, $bp;
		return apply_filters( 'bp_get_picture_view_link', $bp->displayed_user->domain . $bp->album->slug .'/picture/'.$pictures_template->picture->id );
	}

function bp_picture_view() {
	global $bp,$single_pic_template;

    echo site_url().$single_pic_template->pic_mid_path;

}

function bp_single_picture_title() {
	echo bp_get_single_picture_title();
}
	function bp_get_single_picture_title() {
		global $single_pic_template;

		return apply_filters( 'bp_get_single_picture_title', $single_pic_template->title);
	}


function bp_single_picture_description() {
	echo bp_get_single_picture_description();
}
	function bp_get_single_picture_description() {
		global $single_pic_template;

		return apply_filters( 'bp_get_single_picture_description', $single_pic_template->description);
	}

function bp_single_pic_exist(){
    global $bp,$single_pic_template;

    if (!$bp->action_variables[0])
        return flase;

    $pic_id=$bp->action_variables[0];

    $single_pic_template=new BP_Album_Picture($pic_id);

    if (!$single_pic_template->id)
        return false;

    return true;
}


function bp_single_pic_delete_link() {
	echo bp_get_single_pic_delete_link();
}
	function bp_get_single_pic_delete_link() {
		global $single_pic_template, $bp;

		if ( ( $single_pic_template->user_id == $bp->loggedin_user->id ) || is_site_admin() ) {
			if ($bp->current_component == $bp->album->slug) {
                return apply_filters( 'bp_get_single_pic_delete_link', '[<a href="' . wp_nonce_url( $bp->displayed_user->domain . $bp->album->slug . '/picture/delete-picture/' . $single_pic_template->id, 'bp_single_pic_delete_link' ) . '">' . __('Delete Picture', 'bp-album') . '</a> ]' );
            }
		}
	}


function bp_single_pic_check_owner(){
    global $bp,$single_pic_template;

    if ($bp->displayed_user->id == $single_pic_template->user_id)
        return true;
    
    return false;
}

function bp_single_pic_id(){
    global $single_pic_template;
    
    return $single_pic_template->id;
}

function bp_get_picture_permalink( $picture_id = false ) {
    global $bp;

    return apply_filters( 'bp_get_picture_permalink', $bp->displayed_user->domain . $bp->album->slug . '/picture/' . $picture_id );
}



/*
 * Wire Functions
 */

function bp_pic_wire_get_post_list( $item_id = null, $title = null, $empty_message = null, $can_post = true, $show_email_notify = false ) {
	global $bp_item_id, $bp_wire_header, $bp_wire_msg, $bp_wire_can_post, $bp_wire_show_email_notify;

	if ( !$item_id )
		return false;

	if ( !$message )
		$empty_message = __("There are currently no wire posts.", 'bp-album');

	if ( !$title )
		$title = __('Wire', 'bp-album');

	/* Pass them as globals, using the same name doesn't work. */
	$bp_item_id = $item_id;
	$bp_wire_header = $title;
	$bp_wire_msg = $empty_message;
	$bp_wire_can_post = $can_post;
	$bp_wire_show_email_notify = $show_email_notify;

	load_template( TEMPLATEPATH . '/album/post-list.php' );
}

function bp_picture_wire_get_post_form() {
	global $wire_posts_template;

	if ( is_user_logged_in() && $wire_posts_template->can_post )
		load_template( TEMPLATEPATH . '/album/post-form.php' );
}

function bp_picture_wire_get_action() {
	echo bp_get_picture_wire_get_action();
}
	function bp_get_picture_wire_get_action() {
		global $bp;

		if ( empty( $bp->current_item ) )
			$uri = $bp->current_action;
		else
			$uri = $bp->current_item;

		if ( $bp->current_component == $bp->wire->slug || $bp->current_component == $bp->profile->slug ) {
                return apply_filters( 'bp_get_picture_wire_get_action', $bp->displayed_user->domain . $bp->wire->slug . '/post/' );
		} else if ($bp->current_component == $bp->album->slug) {
            global $single_pic_template;
			return apply_filters( 'bp_get_picture_wire_get_action', $bp->displayed_user->domain . $bp->album->slug . '/picture/post-wire/' );
		} else {
			return apply_filters( 'bp_get_picture_wire_get_action', site_url() . '/' . $bp->{$bp->current_component}->slug . '/' . $uri . '/' . $bp->wire->slug . '/post/' );
		}
	}


function bp_picture_wire_delete_link() {
	echo bp_get_picture_wire_delete_link();
}
	function bp_get_picture_wire_delete_link() {
		global $wire_posts_template, $bp;

		if ( empty( $bp->current_item ) )
			$uri = $bp->current_action;
		else
			$uri = $bp->current_item;

		if ( ( $wire_posts_template->wire_post->user_id == $bp->loggedin_user->id ) || $bp->is_item_admin || is_site_admin() ) {
			if ( $bp->wire->slug == $bp->current_component || $bp->profile->slug == $bp->current_component ) {
				return apply_filters( 'bp_get_picture_wire_delete_link', '<a href="' . wp_nonce_url( $bp->displayed_user->domain . $bp->wire->slug . '/delete/' . $wire_posts_template->wire_post->id, 'bp_picture_wire_delete_link' ) . '">[' . __('Delete', 'bp-album') . ']</a>' );
			} else if ($bp->current_component == $bp->album->slug) {
                global $single_pic_template;
                return apply_filters( 'bp_get_picture_wire_delete_link', '<a href="' . wp_nonce_url( $bp->displayed_user->domain . $bp->album->slug . '/picture/delete-wire/' . $wire_posts_template->wire_post->id, 'bp_picture_wire_delete_link' ) . '">[' . __('Delete', 'bp-album') . ']</a>' );
            } else {
				return apply_filters( 'bp_get_picture_wire_delete_link', '<a href="' . wp_nonce_url( site_url( $bp->{$bp->current_component}->slug . '/' . $uri . '/wire/delete/' . $wire_posts_template->wire_post->id ), 'bp_picture_wire_delete_link' ) . '">[' . __('Delete', 'bp-album') . ']</a>' );
			}
		}
	}


/*
 * Function for Next and Previous Picture
 */
function next_picture_link($format='%link &raquo;', $link='%title') {
	adjacent_picture_link($format, $link, true);
}

function previous_picture_link($format='&laquo; %link', $link='%title') {
	adjacent_picture_link($format, $link, false);
}
function adjacent_picture_link($format, $link, $previous = true) {

    $picture = get_adjacent_picture($previous);

	if ( !$picture )
		return;

	$title = $picture->title;

	if ( empty($picture->title) )
		$title = $previous ? __('Previous') : __('Next');

	$title = $title;
	
	$string = '<a href="'.$picture->id.'">';
	$link = str_replace('%title', $title, $link);
	$link = $string . $link . '</a>';

	$format = str_replace('%link', $link, $format);

	$adjacent = $previous ? 'previous' : 'next';
	echo apply_filters( "picture_link", $format, $link );
}

?>
