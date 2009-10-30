<?php

/*
 * All classes for Pictures
 */

Class BP_Album_Picture {
	var $id;
	var $user_id;
	var $picture_id;
    var $picture_ids;
    var $date_uploaded;
    var $title;
    var $description;
    var $status;
    var $enable_wire;
	var $pic_org_path;
    var $pic_org_path_act;
	var $pic_mid_path;
    var $pic_mid_path_act;
	var $pic_small_path;
    var $pic_small_path_act;
    
	function bp_album_picture( $id = null ) {
		global $bp, $wpdb;

		if ( !$user_id )
			$user_id = $bp->displayed_user->id;

		if ( $id ) {
			$this->id = $id;
			$this->populate();
		}
	}

	function populate() {
		global $wpdb, $bp;

		$picture = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bp->album->table_picture_data} WHERE id = %d", $this->id ) );

		$this->user_id = $picture->user_id;
		$this->picture_id = $picture->id;
        $this->date_uploaded = strtotime($picture->date_uploaded);
        $this->title = stripslashes($picture->title);
        $this->description = stripslashes($picture->description);
        $this->status = $picture->status;
        $this->enable_wire = $picture->enable_wire;
        $this->pic_org_path = $picture->pic_org_path;
        $this->pic_org_path_act = $picture->pic_org_path_act;
        $this->pic_mid_path = $picture->pic_mid_path;
        $this->pic_mid_path_act = $picture->pic_mid_path_act;
        $this->pic_small_path = $picture->pic_small_path;
        $this->pic_small_path_act = $picture->pic_small_path_act;

	}

	function save() {
		global $wpdb, $bp;

		// Don't try and save if there is no user ID.
		if ( !$this->user_id)
			return false;

        if ( $this->id ) {
			// Update
			$sql = $wpdb->query( $wpdb->prepare(
					"UPDATE {$bp->album->table_picture_data} SET
						user_id = %d,
                        date_uploaded = FROM_UNIXTIME(%d),
                        title = %s,
                        description = %s,
                        status = %s,
                        enable_wire = %d,
						pic_org_path = %s,
                        pic_org_path_act =%s,
                        pic_mid_path = %s,
                        pic_mid_path_act =%s,
                        pic_small_path = %s,
                        pic_small_path_act =%s
					WHERE id = %d",
						$this->user_id,
                        $this->date_uploaded,
                        $this->title,
                        $this->description,
                        $this->status,
                        $this->enable_wire,
						$this->pic_org_path,
                        $this->pic_org_path_act,
						$this->pic_mid_path,
                        $this->pic_mid_path_act,
						$this->pic_small_path,
                        $this->pic_small_path_act
					) );
		} else {
			// Save
			$sql = $wpdb->query( $wpdb->prepare(
					"INSERT INTO {$bp->album->table_picture_data} (
						user_id,
                        date_uploaded,
                        title,
                        description,
                        status,
                        enable_wire,
						pic_org_path,
                        pic_org_path_act,
                        pic_mid_path,
                        pic_mid_path_act,
                        pic_small_path,
                        pic_small_path_act
					) VALUES (
						%d, FROM_UNIXTIME(%d), %s, %s, %s, %d, %s, %s, %s, %s, %s, %s
					)",
						$this->user_id,
                        $this->date_uploaded,
                        $this->title,
                        $this->description,
                        $this->status,
                        $this->enable_wire,
						$this->pic_org_path,
                        $this->pic_org_path_act,
						$this->pic_mid_path,
                        $this->pic_mid_path_act,
						$this->pic_small_path,
                        $this->pic_small_path_act
					) );
		}

		if (!$sql)
			return false;

		//do_action( 'bp_pictures_picture_after_save', $this );

		if ( $this->id )
			return $this->id;
		else
			return $wpdb->insert_id;


	}

    function delete() {
        
		global $wpdb, $bp;

		if ( function_exists('bp_wire_install') ) {
			BP_Wire_Post::delete_all_for_item( $this->id, $bp->album->table_name_wire );
		}

		if ( !$wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->album->table_picture_data} WHERE id = %d", $this->id ) ) )
			return false;

		return true;
	}

/*
 * Currently I an using this function
 */

	function get_pictures_for_user( $user_id = null ) {
		global $bp, $wpdb;

		if ( !$bp->album )
			bp_album_setup_globals();

		if ( !$user_id )
			$user_id = $bp->displayed_user->id;

		if ( !bp_is_home() )
			$picture_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp->album->table_picture_data} WHERE user_id = %d ORDER BY id DESC", $user_id) );
		else
			$picture_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp->album->table_picture_data} WHERE user_id = %d ORDER BY id DESC", $user_id) );

		$total_picture_count = BP_Album_Picture::total_picture_count( $user_id );

		return array( 'pictures' => $picture_data, 'count' => $total_picture_count );
	}


	function total_picture_count( $user_id = null ) {
		global $bp, $wpdb;

		if ( !$bp->album )
			bp_album_setup_globals();

		if ( !$user_id )
			$user_id = $bp->displayed_user->id;

		// If the user is logged in return the picture count including their hidden pictures.
		if ( !bp_is_home() )
			return $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(id) FROM {$bp->album->table_picture_data} WHERE user_id = %d", $user_id) );
		else
			return $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(id) FROM {$bp->album->table_picture_data} WHERE user_id = %d", $user_id) );
	}

}


function get_adjacent_picture($previous = true) {
	global $single_pic_template, $wpdb, $bp;

	$current_picture_id = $single_pic_template->id;
    $user_id = $bp->displayed_user->id;

	$adjacent = $previous ? 'previous' : 'next';
	$op = $previous ? '<' : '>';
	$order = $previous ? 'DESC' : 'ASC';

	$where = $wpdb->prepare("WHERE id $op %d and user_id=%d", $current_picture_id,$user_id);
	$sort  = "ORDER BY id $order LIMIT 1";

	return $wpdb->get_row("SELECT * FROM {$bp->album->table_picture_data} $where $sort");
}



?>