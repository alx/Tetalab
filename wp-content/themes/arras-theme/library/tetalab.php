<?php

// Curl helper function
function curl_get($url) {
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	$return = curl_exec($curl);
	curl_close($curl);
	return $return;
}

function get_video_posts() {
	
	$num_of_videos = 4;
	$vimeo_call = 'http://vimeo.com/api/v2/group/tetalab/videos.json';
	
	$response = wp_remote_get($vimeo_call, array('timeout' => 60));
	
	if (! is_wp_error($response) ) {
		$ret = json_decode($response["body"], true);
		
		echo '<ul class="hfeed posts-'.arras_get_option('featured_display').' clearfix">';
		for($i = 0; $i <= sizeof($ret) || $i <= $num_of_videos; $i++){
			echo '<li class="post hentry clearfix">';
			echo '<div class="entry-thumbnails"><a class="entry-thumbnails-link" href="'.$ret[$i]['url'].'">';
			echo '<img src="'.$ret[$i]['thumbnail_medium'].'" ';
			echo 'alt="'.htmlspecialchars($ret[$i]['title']).'" ';
			echo 'title="'.htmlspecialchars($ret[$i]['title']).'">';
			echo '</a></div><h3 class="entry-title">';
			echo '<a href="'.$ret[$i]['url'].'" rel="bookmark">'.htmlspecialchars($ret[$i]['title']).'</a></h3>';
			echo '<div class="entry-summary">'.htmlspecialchars($ret[$i]['description']);
			echo '</div></li>';
		}
		echo '</ul>';
	}
}

?>