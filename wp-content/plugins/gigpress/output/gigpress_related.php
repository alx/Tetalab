<?php

function gigpress_show_related($content) {
	
	global $is_excerpt, $wpdb, $gpo, $post;
	if( $is_excerpt == TRUE || !is_object($post) ) {
		$is_excerpt = FALSE; return $content;
	} else {
	
		$shows = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM " . GIGPRESS_ARTISTS . " AS a, " . GIGPRESS_VENUES . " as v, " . GIGPRESS_SHOWS ." AS s LEFT JOIN  " . GIGPRESS_TOURS . " AS t ON s.show_tour_id = t.tour_id WHERE show_related = %d AND show_status != 'deleted' AND s.show_artist_id = a.artist_id AND s.show_venue_id = v.venue_id ORDER BY show_date ASC,show_time ASC", $post->ID)
		);
	
		if($shows != FALSE) {
			
			ob_start();
				
				$count = 1;
				$total_shows = count($shows);
				foreach ($shows as $show) {
					$showdata = gigpress_prepare($show, 'related');						
					include gigpress_template('related');
					$count++;
				}
			
			$giginfo = ob_get_clean();
			
			if ( $gpo['related_position'] == "before" ) {
				$output = $giginfo . $content;
			} else {
				$output = $content . $giginfo;
			}
			
			return $output;
							
		} else {
		
			return $content;
		}
	}
}