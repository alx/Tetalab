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
	$vimeo_call = 'http://vimeo.com/api/v2/channel/tetalab/videos.json';
	
	$response = wp_remote_get($vimeo_call, array('timeout' => 60));
	echo var_dump($response);
	
	if (! is_wp_error($response) ) {
		$ret = json_decode($response, true);
		
		if(sizeof($ret)){
			echo '<ul class="hfeed posts-'.arras_get_option('featured_display').' clearfix">';
			for($i = 0; $i == sizeof($ret) || $i == $num_of_videos; $i++){
				echo '<li '.arras_post_class().'>';
				echo arras_newsheader("featured");
				echo '<div class="entry-summary">';
				echo '<a href="'.$ret[$i]['url'].'">';
				echo '<img src="'.$ret[$i][$thumbnail_size].'" alt="'.htmlspecialchars($ret[$i]['title']).'"></a>';
				echo '</div>';
				echo arras_newsfooter("featured");
				echo '</li>';
			}
			echo '</ul>';
		}
	}
}

?>