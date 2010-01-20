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

function convertVimeoTime($vimeo_time) {
  extract(strptime($vimeo_time,'%Y-%m-%d %H:%M:%S'));
  return strftime('%a, %d %b %Y %H:%M:%S %z',mktime(
                                intval($tm_hour),
                                intval($tm_min),
                                intval($tm_sec),
                                intval($tm_mon)+1,
                                intval($tm_mday),
                                intval($tm_year)+1900
                              ));
}

function get_video_posts($format = 'post') {
	
	$output = '';
	$num_of_videos = 4;
	$vimeo_call = 'http://vimeo.com/api/v2/group/tetalab/videos.json';
	
	$response = wp_remote_get($vimeo_call, array('timeout' => 60));
	
	if (! is_wp_error($response) ) {
		$ret = json_decode($response["body"], true);
		
		for($i = 0; $i < sizeof($ret) && $i < $num_of_videos; $i++){
			
			switch ($format) {
				case 'rss':
					$output .= '<item><title>'.htmlspecialchars($ret[$i]['title']).'</title>';
					$output .= '<link>'.$ret[$i]['url'].'</link>';
					$output .= '<comments>'.$ret[$i]['url'].'#comment</comments>';
					$output .= '<pubDate>'.convertVimeoTime($ret[$i]['upload_date']).'</pubDate>';
					$output .= '<dc:creator>'.$ret[$i]['author_name'].'</dc:creator>';
					$output .= '<guid isPermaLink="false">'.$ret[$i]['url'].'</guid>';
					$output .= '<description><![CDATA['.$ret[$i]['description'].']]></description></item>';
					break;
				
				default:
					$output .= '<li class="post hentry clearfix">';
					$output .= '<div class="entry-thumbnails"><a class="entry-thumbnails-link" href="'.$ret[$i]['url'].'">';
					$output .= '<img src="'.$ret[$i]['thumbnail_medium'].'" ';
					$output .= 'alt="'.htmlspecialchars($ret[$i]['title']).'" ';
					$output .= 'title="'.htmlspecialchars($ret[$i]['title']).'" width="200px" height="150px">';
					$output .= '</a></div><h3 class="entry-title">';
					$output .= '<a href="'.$ret[$i]['url'].'" rel="bookmark">';
					$output .= htmlspecialchars($ret[$i]['title']).'</a></h3></li>';
					break;
			}
		}
		
		if($format == 'post') {
			$output = '<ul class="hfeed posts-'.arras_get_option('featured_display').' clearfix">'.$output.'</ul>';
		}
	}
	
	echo $output;
}

function get_mailing_list() {
	
	$num_of_mails = 10;
	$month_ml = date("Y-F");
	$base_ml = 'http://lists.tetalab.org/pipermail/tetalab/';
	
	// Get HTML tmpfile into string.
	$fileHandle = fopen('/var/lib/mailman/archives/private/tetalab/'.$month_ml.'/date.html', 'r');
	$html = fread($fileHandle,'1000000');

	// Get rid of data outside of (and including) the <BODY> tags.
	$html = preg_replace("/.*<body[^>]*>(.*)<\/body>.*/i","\$1",$html);
	
	$regexp = '<LI><A HREF="(.*)">(.*)\n<\/A><A NAME="(\d+)">.*<\/A>\n<I>(.*)';
	
	if(preg_match_all("/$regexp/", $html, $matches, PREG_SET_ORDER) > 0) {
		
		echo '<ul class="hfeed posts-line clearfix">';
		for($i = 0; $i < sizeof($matches) && $i < $num_of_mails; $i++){
			echo '<li class="post hentry clearfix">';
			echo '<span class="entry-cat">'.$matches[$i][3].'</span>';
			echo '<h3 class="entry-title"><a rel="bookmark" href="'.$base_ml.$month_ml.'/'.$matches[$i][1].'"';
			echo 'title="">'.str_ireplace("[tetalab]", "", htmlspecialchars($matches[$i][2])).'</a></h3>';
			echo '<span class="entry-comments">'.htmlspecialchars($matches[$i][4]).'</span>';
			echo '</li>';
		}
		echo '</ul>';
	}
	
	// Close HTML tmpfile.
	fclose($fileHandle);
}

function wpmu_link(){
	
	global $post;
	
	$link = get_post_meta($post->ID, "wpmu-link", true);
	if(strlen($link) == 0){
		$link = get_permalink();
	}
	
	return $link;
}

//Add a feed image
function include_video_in_rss() {
	get_video_posts('rss');
}
add_action('rss2_head', 'include_video_in_rss');

?>