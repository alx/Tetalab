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

function fetch_ml_archives() {
	// Change this to your Mailman list.
	$ch = curl_init ('http://lists.tetalab.org/admin/tetalab/');
	$curlFile = tmpfile();
	$cookiefp = tempnam("/tmp", "cookie");
	$curlCookieSaveFile = fopen($cookiefp, "w+");
	curl_setopt ($ch, CURLOPT_WRITEHEADER, $curlCookieSaveFile);
	curl_setopt ($ch, CURLOPT_FILE, $curlFile);
	curl_setopt ($ch, CURLOPT_POST, 1);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $query);
	curl_exec ($ch);
	curl_close ($ch);
	
	// Set HTML tmpfile pointer to the beginning of the file (for reading).
	fseek($curlFile, 0);

	// Get HTML tmpfile into string.
	$html = fread($curlFile,'1000000');

	// Get rid of newlines in string for purposes of chopping things up.
	$html = preg_replace("/\n/","__NEWLINE__",$html);

	// Get rid of data outside of (and including) the <BODY> tags.
	$html = preg_replace("/.*<body[^>]*>(.*)<\/body>.*/i","\$1",$html);

	// Replace all anchor tags with extra data for cURL.
	$html = preg_replace("/(<a[^>]*href=\"?)([^\" >]*)(\"?[^>]*>)/i","\$1$thisHREF?tempname=$cookiefp&formAction=\$2&curPage=$thisFile&pid=$pid\$3", $html);

	// Re-replace anchor tags with mailto, ftp, or http protocols heading them off.
	$html = preg_replace("/(<a[^>]*href=\"?)[^>]+formAction=((mailto|http|ftp)[^>&\"]+)[^>\"]*(\"?[^>]*>)/i","\$1\$2\$4",$html);

	// Replace forms with extra inputs for cURL.
	$html = preg_replace("/(<form[^>]*action=\"?)([^\" >]*)(\"?[^>]*>)/i","\$1$thisHREF\$3__NEWLINE__<input type=\"hidden\" name=\"formAction\" value=\"\$2\">__NEWLINE__<input type=\"hidden\" name=\"tempname\" value=\"$cookiefp\">__NEWLINE__<input type=\"hidden\" name=\"curPage\" value=\"$thisFile\">__NEWLINE__<input type=\"hidden\" name=\"pid\" value=\"$pid\">__NEWLINE__",$html);

	// Put back newlines, for source legibility.
	$html = preg_replace("/__NEWLINE__/","\n",$html);

	// Display the document.
	print $html;

	// Close HTML tmpfile.
	fclose($curlFile);
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
			echo '<span class="entry-comments">'.htmlspecialchars($matches[$i][4]).'</span>';
			echo '</li>';
		}
		echo '</ul>';
	}
}

function wpmu_link(){
	
	global $post;
	
	$link = get_post_meta($post->ID, "wpmu-link", true);
	if(strlen($link) == 0){
		$link = get_permalink();
	}
	
	return $link;
}

?>