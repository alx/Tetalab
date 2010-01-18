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

	// API endpoint
	$api_endpoint = 'http://www.vimeo.com/api/v2/user593108';

	// Load clips
	$videos = simplexml_load_string(curl_get($api_endpoint.'/videos.xml'));
	
	?>
	
	<ul class="hfeed posts-<?php echo arras_get_option('featured_display') ?> clearfix">
	<?php foreach ($videos->video as $video): ?>
	<li <?php arras_post_class() ?>>
		<?php arras_newsheader("featured") ?>
		<div class="entry-summary">
			<a href=""><img src="http://ats.vimeo.com/400/652/40065219_200.jpg" width="125" height="94" alt=""></a>
		</div>
		<?php arras_newsfooter("featured") ?>		
	</li>
	<?php endforeach; ?>
	</ul>
	<?php
}

?>