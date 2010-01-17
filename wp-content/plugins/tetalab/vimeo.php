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

function read_lucas_vimeo() {

	// API endpoint
	$api_endpoint = 'http://www.vimeo.com/api/v2/user593108';

	// Load clips
	$videos = simplexml_load_string(curl_get($api_endpoint.'/videos.xml'));
	
	foreach ($videos->video as $video):
	
		$content = "";
		
		if(bp_activity_check_exists_by_content($content)):
			bp_activity_add( array( 'user_id' => false, 
									'content' => $content, 
									'primary_link' => $primary_link, 
									'component_name' => $component_name, 
									'component_action' => $component_action, 
									'item_id' => $item_id, 
									'secondary_item_id' => $secondary_item_id, 
									'recorded_time' => $recorded_time) );
		endif;
		
	endforeach;
}

?>