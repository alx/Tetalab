<?php

// Forum for tetalab committers!
//   alx: hello world :)

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

function get_video_posts($source = 'group', $format = 'post') {
	
	$output = '';
	$num_of_videos = 4;
	
	switch($source){
		case 'channel':
			$vimeo_call = 'http://vimeo.com/api/v2/channel/tetaglobule/videos.json';
		default:
			$vimeo_call = 'http://vimeo.com/api/v2/group/tetalab/videos.json';
	}
	
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
		
		
		echo '<div class="home-title">Mailing List</div><ul class="hfeed posts-line clearfix">';
		for($i = sizeof($matches) - 1; (sizeof($matches) - $i) < $num_of_mails; $i--){
			echo '<li class="post hentry clearfix">';
			echo '<span class="entry-cat">'.$matches[$i][3].'</span>';
			echo '<h3 class="entry-title"><a rel="bookmark" href="'.$base_ml.$month_ml.'/'.$matches[$i][1].'"';
			echo 'title="">'.str_ireplace("[tetalab]", "", htmlspecialchars(iconv("ISO-8859-1", "UTF-8", $matches[$i][2]))).'</a></h3>';
			echo '<span class="entry-comments">'.htmlspecialchars(iconv("ISO-8859-1", "UTF-8", $matches[$i][4])).'</span>';
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

function url_grab_title($rss_url) {
	$contents = file_get_contents($rss_url, TRUE, NULL, 0, 3072);
	$contents = preg_replace("/(\n|\r)/", '', $contents);
	preg_match('/<title>(.*?)<\/title>/i', $contents, $matches);
	return $matches[1];
}

//Add a feed image
function include_video_in_rss() {
	
	// $TMP_ROOT = "tmp/";
	// $DOMAIN_NAME = "http://tetalab.org/";
	// $SITE_TITLE = "tetalab";
	// $SITE_DESRIPTION = "tetalab feeds";
	// $SITE_AUTHOR = "tetalab";
	// $RSS_DIR = "../";
	// 
	//  $array = array(
	//  "http://blog.thydzik.com/feed/",
	//  "http://sonyaandtravis.com/feed/"
	//  );
	//  $num = 10;
	//  $showfullfeed = FALSE;
	// 
	//  define('MAGPIE_DIR', $RSS_DIR.'feed/');
	//  define('MAGPIE_CACHE_DIR','/'.$TMP_ROOT.'rsscache');
	// 
	//  /* include required files */
	//  @require_once(MAGPIE_DIR.'rss_fetch.inc');
	//  @include(MAGPIE_DIR.'feedcreator.class.php');
	// 
	//  /* Set RSS properties */
	//  $rss = new UniversalFeedCreator();
	//  $rss->useCached();
	//  $rss->title = $SITE_TITLE;
	//  $rss->description = $SITE_DESRIPTION;
	//  $rss->link = $DOMAIN_NAME;
	//  $rss->syndicationURL = $DOMAIN_NAME."feed/index.php";
	// 
	//  $i_temp = 0; //temp i variable
	//  $j_temp = 0; //temp j variable
	//  $total_temp = 0; //temp total number of posts in all rss feeds
	// 
	//  $array_count = count($array); //number of rss feeds
	// 
	//  /* code to determine which post to display */
	//  for ($i = 0; $i < $array_count; $i++) {
	//  $rss1 = fetch_rss($array[$i]);
	//  if ($rss1) {
	//  $array_temp[$i]['page_title'] = url_grab_title($array[$i]);
	//  $items = array_slice($rss1->items, 0);
	//  $array_temp[$i]['rss_data'] = $items;
	//  $total_temp += count($items);
	//  $array_temp[$i]['rss_pointer'] = 0;
	//  preg_match('@^(?:http://)?([^/]+)@i', $array[$i], $matches);
	//  $array_temp[$i]['site_url'] = $matches[0];
	//  }
	//  }
	//  while ($total_temp <> 0 && $num > 0){// loop while there are remaining posts to process
	//  $date_timestamp_temp = 0; //initialise to 0
	//  for ($i = 0; $i < $array_count; $i++) {
	//  $date_timestamp_temp = max($date_timestamp_temp, $array_temp[$i]['rss_data'][$array_temp[$i]['rss_pointer']]['date_timestamp']); //determine latest post from rss feeds
	//  if ($date_timestamp_temp == $array_temp[$i]['rss_data'][$array_temp[$i]['rss_pointer']]['date_timestamp']) { //latest post is found so save where it came from
	//  $i_temp = $i;
	//  $j_temp = $array_temp[$i]['rss_pointer'];
	//  }
	//  }
	//  $total_temp --; //decrement total remaining posts to process
	//  $num --; //decrement number of posts to display
	//  $array_temp[$i_temp]['rss_pointer'] ++; //increment post index of used post rss
	// 
	//  /* code to display post */
	//  $item = $array_temp[$i_temp]['rss_data'][$j_temp];
	//  $href = $item['link'];
	//  $title = $item['title'];
	//  if (!$showfullfeed) {
	//  $desc = $item['description'];
	//  }else{
	//  $desc =  $item['content']['encoded'];
	//  }
	//  $desc .=  '
	// Copyright &copy; <a href="'.$array_temp[$i_temp]['site_url'].'">'.$array_temp[$i_temp]['page_title'].'</a>.  All Rights Reserved.
	// ';
	//  $pdate = $item['pubdate'];
	//  $item = new FeedItem();
	//  $item->title = $title;
	//  $item->link = $href;
	//  $item->description = $desc;
	//  $item->date = $pdate;
	//  $item->source = $DOMAIN_NAME;
	//  $item->author = $SITE_AUTHOR;
	//  $rss->addItem($item);
	//  }
	// 
	// 
	// 
	//  // get your news items from other feed and display back
	//  $rss->saveFeed("RSS2.0", '/'.$TMP_ROOT."rsscache/feed.xml");
}

add_action('rss2_head', 'include_video_in_rss');

?>
