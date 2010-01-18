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
		for($i = 0; $i < sizeof($ret) && $i < $num_of_videos; $i++){
			echo '<li class="post hentry clearfix">';
			echo '<div class="entry-thumbnails"><a class="entry-thumbnails-link" href="'.$ret[$i]['url'].'">';
			echo '<img src="'.$ret[$i]['thumbnail_medium'].'" ';
			echo 'alt="'.htmlspecialchars($ret[$i]['title']).'" ';
			echo 'title="'.htmlspecialchars($ret[$i]['title']).'" width="200px" height="150px">';
			echo '</a></div><h3 class="entry-title">';
			echo '<a href="'.$ret[$i]['url'].'" rel="bookmark">'.htmlspecialchars($ret[$i]['title']).'</a></h3>';
			echo '</li>';
		}
		echo '</ul>';
	}
}

function get_mailing_list() {
	
	$num_of_mails = 10;
	
	$base_url = "http://lists.tetalab.org/pipermail/tetalab/";
	$url = $base_url."2010-January/date.html";
	$input = @file_get_contents($url) or die('Could not access file: $url');
	$regexp = "<li><a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a><a \s[^>]*name=(\"??)([^\" >]*?)\\1[^>]*(.*)</a><i>(.*)</i></li>";
	
	// <li><a href="000584.html">[Tetalab] Cette semaine au tetalab
	// 	</a><a name="584">&nbsp;</a><i>Thomas Barandon</i></li>
	
	if(preg_match_all("/$regexp/siU", $input, $matches, PREG_SET_ORDER)) {
		
		echo '<ul class="hfeed posts-line clearfix">';
		for($i = 0; $i < sizeof($matches) && $i < $num_of_mails; $i++){
			echo '<li class="post hentry clearfix">';
			echo '<span class="entry-cat">'.$matches[$i][2].'</span>';
			echo '<h3 class="entry-title"><a rel="bookmark" href="'.$matches[$i][1].'" title="">'.htmlspecialchars($matches[$i][3]).'</a></h3>';
			echo '<span class="entry-comments">'.htmlspecialchars($matches[$i][4])).'</span>';
			echo '</li>';
		}
		echo '</ul>';
	}
}

?>