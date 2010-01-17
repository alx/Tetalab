<?php

// Functions related to mobile.
require_once 'mobile.php';
$sexy_is_mobile = sexy_is_mobile();
$sexy_is_bot = sexy_is_bot();

//cURL, file get contents or nothing, used for short url
function sexy_nav_browse($url, $use_POST_method = false, $POST_data = null){
	if (function_exists('curl_init')) {
		// Use cURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		if($use_POST_method){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $POST_data);
		}
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		$source = trim(curl_exec($ch));
		curl_close($ch);
		
	} elseif (function_exists('file_get_contents')) { 
		// Use file_get_contents()
		$source = trim(file_get_contents($url));
	} else {
		$source = null;
	}
	return $source;
}

function sexy_get_fetch_url() {
	global $post, $sexy_plugopts; //globals
	
	//get link
	if($sexy_plugopts['position'] == 'manual') { $perms= 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING']; }
	else { $perms = get_permalink(); }
	$perms = trim($perms);
	
	//if is post, and post is not published then return permalink and go back
	if($post && get_post_status($post->ID) != 'publish'){
		return $perms;
	}
	
	//check if the link is already genereted or not, if yes, then return the link
	$fetch_url = trim(get_post_meta($post->ID, '_sexybookmarks_shortUrl', true));
	if(!is_null($fetch_url) && md5($perms) == trim(get_post_meta($post->ID, '_sexybookmarks_permaHash', true))){
		return $fetch_url;
	}else{
		//some vars to be used later, so better set null values before
		$url_more = "";
		$use_POST_method = false;
		$POST_data = null;
		
		// Which short url service should be used?
		if($sexy_plugopts['shorty'] == "e7t") {
			//e7t.us no longer exists, this only here for backwards compatibility
			//to prevent users from having to update their short URLs if they had e7t.us selected
			$first_url = "http://b2l.me/api.php?alias=&url=".$perms;
		} elseif($sexy_plugopts['shorty'] == "b2l") {
			$first_url = "http://b2l.me/api.php?alias=&url=".$perms;
		} elseif($sexy_plugopts['shorty'] == "tiny") {
			$first_url = "http://tinyurl.com/api-create.php?url=".$perms;
		} elseif($sexy_plugopts['shorty'] == "snip") {
			$first_url = "http://snipr.com/site/getsnip";
			$use_POST_method = true;
			$POST_data = "snipformat=simple&sniplink=".rawurlencode($perms)."&snipuser=".$sexy_plugopts['shortyapi']['snip']['user']."&snipapi=".$sexy_plugopts['shortyapi']['snip']['key'];
		} elseif($sexy_plugopts['shorty'] == "cligs") {
			$first_url = "http://cli.gs/api/v1/cligs/create?url=".urlencode($perms)."&appid=sexy";
			if($sexy_plugopts['shortyapi']['cligs']['chk'] == 1){ //if user custom options are set
				$first_url .= "&key=".$sexy_plugopts['shortyapi']['cligs']['key'];
			}
		} elseif($sexy_plugopts['shorty'] == "supr") {
			$first_url = "http://su.pr/api/simpleshorten?url=".$perms;
			if($sexy_plugopts['shortyapi']['supr']['chk'] == 1){ //if user custom options are set
				$first_url .= "&login=".$sexy_plugopts['shortyapi']['supr']['user']."&apiKey=".$sexy_plugopts['shortyapi']['supr']['key'];
			}
		} elseif($sexy_plugopts['shorty'] == "bitly") {
			$first_url = "http://api.bit.ly/shorten?version=2.0.1&longUrl=".$perms."&history=1&login=".$sexy_plugopts['shortyapi']['bitly']['user']."&apiKey=".$sexy_plugopts['shortyapi']['bitly']['key']."&format=json";
		} elseif($sexy_plugopts['shorty'] == "trim"){
			if($sexy_plugopts['shortyapi']['trim']['chk'] == 1){ //if user custom options are set
				$first_url = "http://api.tr.im/api/trim_url.json?url=".$perms."&username=".$sexy_plugopts['shortyapi']['trim']['user']."&password=".$sexy_plugopts['shortyapi']['trim']['pass'];
			}else{
				$first_url = "http://api.tr.im/api/trim_simple?url=".$perms;
			}
		} elseif($sexy_plugopts['shorty'] == "tinyarrow") {
			$first_url = "http://tinyarro.ws/api-create.php?";
			if($sexy_plugopts['shortyapi']['tinyarrow']['chk'] == 1){ //if user custom options are set
				$first_url .= "&userid=".$sexy_plugopts['shortyapi']['tinyarrow']['user'];
			}
			$first_url .= "&url=".$perms;
		} elseif($sexy_plugopts['shorty'] == "tflp" && function_exists('permalink_to_twitter_link')) {
			$fetch_url = permalink_to_twitter_link($perms);
		} elseif($sexy_plugopts['shorty'] == "slly") {
			$first_url = "http://sl.ly/?module=ShortURL&file=Add&mode=API&url=".$perms;
		} else { 
			//Default is b2l.me
			$first_url = "http://b2l.me/api.php?alias=&url=".$perms;
		}
		
		// Retrieve the shortened URL
		$fetch_url = trim(sexy_nav_browse($first_url, $use_POST_method, $POST_data)); //trim again
		
		//if trim or bitly, then decode the json string
		if($sexy_plugopts['shorty'] == "trim" && $sexy_plugopts['shortyapi']['trim']['chk'] == 1){
			$fetch_array = json_decode($fetch_url, true);
			$fetch_url = $fetch_array['url'];
		}
		if($sexy_plugopts['shorty'] == "bitly"){
			$fetch_array = json_decode($fetch_url, true);
			$fetch_url = $fetch_array['results'][$perms]['shortUrl'];
		}

		if (!empty($fetch_url)) {
			// Remote call made and was successful
			// Add/update values
			// Tries to update first, then add if field does not already exist
			if (!update_post_meta($post->ID, '_sexybookmarks_shortUrl', $fetch_url)) {
				add_post_meta($post->ID, '_sexybookmarks_shortUrl', $fetch_url);
			}
			if (!update_post_meta($post->ID, '_sexybookmarks_permaHash', md5($perms))) {
				add_post_meta($post->ID, '_sexybookmarks_permaHash', md5($perms));
			}
		} else {
			$fetch_url = $perms;
		}
	}
	return $fetch_url;
}


// Create an auto-insertion function
function sexy_position_menu($post_content) {
	global $post, $sexy_plugopts, $sexy_is_mobile, $sexy_is_bot;

	// If user selected manual positioning, get out.
	if ($sexy_plugopts['position']=='manual') {
		return $post_content;
	}

	// If user selected hide from mobile and is mobile, get out.
	elseif ($sexy_plugopts['mobile-hide']=='yes' && false!==$sexy_is_mobile || $sexy_plugopts['mobile-hide']=='yes' && false!==$sexy_is_bot) {
		return $post_content;
	}

	// Decide whether or not to generate the bookmarks.
	if ((is_single() && false!==strpos($sexy_plugopts['pageorpost'],"post")) ||
		(is_page() && false!==strpos($sexy_plugopts['pageorpost'],"page")) ||
		(is_home() && false!==strpos($sexy_plugopts['pageorpost'],"index")) ||
		(is_feed() && !empty($sexy_plugopts['feed']))
	) { // socials should be generated and added
		if(get_post_meta($post->ID, 'Hide SexyBookmarks')) {
			// Don't display SexyBookmarks
		}
		else {
			$socials=get_sexy();
		}
	}

	// Place of bookmarks and return w/ post content.
	if (empty($socials)) {
		return $post_content;
	} elseif ($sexy_plugopts['position']=='above') {
		return $socials.$post_content;
	} elseif ($sexy_plugopts['position']=='below') {
		return $post_content.$socials;
	} else { // some other unexpected error, don't do anything. return.
		error_log(__('an error occurred in SexyBookmarks', 'sexybookmarks'));
		return $post_content;
	}
}
// End sexy_position_menu...

function get_sexy() {
	global $sexy_plugopts, $wp_query, $post;

	$post = $wp_query->post;

	if($sexy_plugopts['position'] == 'manual') {

		//Check if outside the loop
		if(empty($post->post_title)) {
			$perms= 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING'];
			$title = get_bloginfo('name') . wp_title('-', false);
			$feedperms = strtolower('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING']);
			$mail_subject = urlencode(get_bloginfo('name') . wp_title('-', false));
		}

		//Otherwise, it must be inside the loop
		else {
			$perms = get_permalink($post->ID);
			$title = $post->post_title;
			$feedperms = strtolower($perms);
			$mail_subject = urlencode($post->post_title);
		}
	}

	//Check if index page...
	elseif(is_home() && false!==strpos($sexy_plugopts['pageorpost'],"index")) {

		//Check if outside the loop
		if(empty($post->post_title)) {
			$perms= 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING'];
			$title = get_bloginfo('name') . wp_title('-', false);
			$feedperms = strtolower('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING']);
			$mail_subject = urlencode(get_bloginfo('name') . wp_title('-', false));
		}

		//Otherwise, it must be inside the loop
		else {
			$perms = get_permalink($post->ID);
			$title = $post->post_title;
			$feedperms = strtolower($perms);
			$mail_subject = urlencode($post->post_title);
		}
	}
	//Apparently isn't on index page...
	else {
		$perms = get_permalink($post->ID);
		$title = $post->post_title;
		$feedperms = strtolower($perms);
		$mail_subject = urlencode($post->post_title);
	}


	//Determine how to handle post titles for Twitter
	if (strlen($title) >= 80) {
		$short_title = urlencode(substr($title, 0, 80)."[..]");
	}
	else {
		$short_title = urlencode($title);
	}

	$title=urlencode($title);
	$sexy_content = urlencode(substr(strip_tags(strip_shortcodes(get_the_content())),0,300));
	$sexy_content = str_replace('+','%20',$sexy_content);
	$sexy_content = str_replace("&#8217;","'",$sexy_content);
	$post_summary = stripslashes($sexy_content);
	$site_name = get_bloginfo('name');
	$mail_subject = str_replace('+','%20',$mail_subject);
	$mail_subject = str_replace("&#8217;","'",$mail_subject);
	$y_cat = $sexy_plugopts['ybuzzcat'];
	$y_med = $sexy_plugopts['ybuzzmed'];
	$t_cat = $sexy_plugopts['twittcat'];
	
	// Fix for faulty insertion of TFLP function above
	if($sexy_plugopts['shorty'] == "tflp" && function_exists('permalink_to_twitter_link')) {
		$fetch_url = permalink_to_twitter_link($perms);
	}
	else {
		$fetch_url = sexy_get_fetch_url();
	}


	// Grab post tags for Twittley tags. If there aren't any, use default tags set in plugin options page
	// This doesn't seem to be working anymore, but not confirmed yet...
	$getkeywords = get_the_tags(); if ($getkeywords) { foreach($getkeywords as $tag) { $keywords=$keywords.$tag->name.','; } }
	if (!empty($getkeywords)) {
		$d_tags=substr($d_tags, 0, count($d_tags)-2);
	}
	else {
		$d_tags = $sexy_plugopts['defaulttags'];
	}





	// Check permalink setup for proper feed link
	if (false !== strpos($feedperms,'?') || false !== strpos($feedperms,'.php',strlen($feedperms) - 4)) {
		$feedstructure = '&feed=comments-rss2';
	} else {
		if ('/' == $feedperms[strlen($feedperms) - 1]) {
			$feedstructure = 'feed';
		}
		else {
			$feedstructure = '/feed';
		}
	}


	// Compatibility fix for NextGen Gallery Plugin...
	if( (strpos($post_summary, '[') || strpos($post_summary, ']')) ) {
		$post_summary = "";
	}
	if( (strpos($sexy_content, '[') || strpos($sexy_content,']')) ) {
		$sexy_content = "";
	}

	// Select the background image
	if(!isset($sexy_plugopts['bgimg-yes'])) {
		$bgchosen = '';
	} elseif($sexy_plugopts['bgimg'] == 'sexy') {
		$bgchosen = ' sexy-bookmarks-bg-sexy';
	} elseif($sexy_plugopts['bgimg'] == 'caring') {
		$bgchosen = ' sexy-bookmarks-bg-caring';
	} elseif($sexy_plugopts['bgimg'] == 'care-old') {
		$bgchosen = ' sexy-bookmarks-bg-caring-old';
	} elseif($sexy_plugopts['bgimg'] == 'love') {
		$bgchosen = ' sexy-bookmarks-bg-love';
	} elseif($sexy_plugopts['bgimg'] == 'wealth') {
		$bgchosen = ' sexy-bookmarks-bg-wealth';
	} elseif($sexy_plugopts['bgimg'] == 'enjoy') {
		$bgchosen = ' sexy-bookmarks-bg-enjoy';
	} elseif($sexy_plugopts['bgimg'] == 'german') {
		$bgchosen = ' sexy-bookmarks-bg-german';
	}

	// Do not add inline styles to the feed.
	$style=($sexy_plugopts['autocenter'])?'':' style="'.__($sexy_plugopts['xtrastyle']).'"';
	if (is_feed()) $style='';
	$expand=$sexy_plugopts['expand']?' sexy-bookmarks-expand':'';
	if ($sexy_plugopts['autocenter']==1) {
		$autocenter=' sexy-bookmarks-center';
	} elseif ($sexy_plugopts['autocenter']==2) {
		$autocenter=' sexy-bookmarks-spaced';
	} else {
		$autocenter='';
	}

	//Write the sexybookmarks menu
	$socials = "\n\n".'<!-- Begin SexyBookmarks Menu Code -->'."\n";
	$socials .= '<div class="sexy-bookmarks'.$expand.$autocenter.$bgchosen.'"'.$style.'>'."\n".'<ul class="socials">'."\n";
	foreach ($sexy_plugopts['bookmark'] as $name) {
		if ($name=='sexy-twitter') {
			$socials.=bookmark_list_item($name, array(
				'post_by'=>(!empty($sexy_plugopts['twittid']))?"(via+@".$sexy_plugopts['twittid'].")":'',
				'short_title'=>$short_title,
				'fetch_url'=>$fetch_url,
			));
	    }
		elseif ($name=='sexy-blogengage') {
			$socials.=bookmark_list_item($name, array(
				'permalink'=>$perms,
			));
	    }
		elseif ($name=='sexy-identica') {
			$socials.=bookmark_list_item($name, array(
				'short_title'=>$short_title,
				'fetch_url'=>$fetch_url,
			));
	    }
		elseif ($name=='sexy-mail') {
		 		$socials.=bookmark_list_item($name, array(
		 			'title'=>$mail_subject,
		 			'post_summary'=>$post_summary,
		 			'permalink'=>$perms,
		 		));
		}
		elseif ($name=='sexy-tomuse') {
		 		$socials.=bookmark_list_item($name, array(
		 			'title'=>$mail_subject,
		 			'post_summary'=>$post_summary,
		 			'permalink'=>$perms,
		 		));
		}
		elseif ($name=='sexy-diigo') {
			$socials.=bookmark_list_item($name, array(
				'sexy_teaser'=>$sexy_content,
				'permalink'=>$perms,
				'title'=>$title,
			));
		}
		elseif ($name=='sexy-linkedin') {
			$socials.=bookmark_list_item($name, array(
				'post_summary'=>$post_summary,
				'site_name'=>$site_name,
				'permalink'=>$perms,
				'title'=>$title,
			));
		}
		elseif ($name=='sexy-devmarks') {
			$socials.=bookmark_list_item($name, array(
				'post_summary'=>$post_summary,
				'permalink'=>$perms,
				'title'=>$title,
			));
		}
		elseif ($name=='sexy-comfeed') {
			$socials.=bookmark_list_item($name, array(
				'permalink'=>urldecode($feedperms).$feedstructure,
			));
		}
		elseif ($name=='sexy-yahoobuzz') {
			$socials.=bookmark_list_item($name, array(
				'permalink'=>$perms,
				'title'=>$title,
				'yahooteaser'=>$sexy_content,
				'yahoocategory'=>$y_cat,
				'yahoomediatype'=>$y_med,
			));
		}
		elseif ($name=='sexy-twittley') {
			$socials.=bookmark_list_item($name, array(
				'permalink'=>urlencode($perms),
				'title'=>$title,
				'post_summary'=>$post_summary,
				'twitt_cat'=>$t_cat,
				'default_tags'=>$d_tags,
			));
		}
		elseif ($name=='sexy-designmoo') {
			$socials.=bookmark_list_item($name, array(
				'post_summary'=>$post_summary,
				'permalink'=>$perms,
				'title'=>$title,
			));
		}
		elseif ($name=='sexy-designbump') {
			$socials.=bookmark_list_item($name, array(
				'post_summary'=>$post_summary,
				'permalink'=>$perms,
				'title'=>$title,
			));
		}
		elseif ($name=='sexy-posterous') {
			$socials.=bookmark_list_item($name, array(
				'post_summary'=>$post_summary,
				'permalink'=>$perms,
				'title'=>$title,
			));
		}
		elseif ($name=='sexy-pingfm') {
			$socials.=bookmark_list_item($name, array(
				'post_summary'=>$post_summary,
				'permalink'=>$perms,
				'title'=>$title,
			));
		}
		elseif ($name=='sexy-nujij') {
			$socials.=bookmark_list_item($name, array(
				'post_summary'=>$post_summary,
				'permalink'=>$perms,
				'title'=>$title,
			));
		}
		elseif ($name=='sexy-ekudos') {
			$socials.=bookmark_list_item($name, array(
				'post_summary'=>$post_summary,
				'permalink'=>$perms,
				'title'=>$title,
			));
		}
		elseif ($name=='sexy-webblend') {
			$socials.=bookmark_list_item($name, array(
				'post_summary'=>$post_summary,
				'permalink'=>$perms,
				'title'=>$title,
			));
		}
		elseif ($name=='sexy-hyves') {
			$socials.=bookmark_list_item($name, array(
				'post_summary'=>$post_summary,
				'permalink'=>$perms,
				'title'=>$title,
			));
		}
		elseif ($name=='sexy-orkut') {
			$socials.=bookmark_list_item($name, array(
				'post_summary'=>$post_summary,
				'permalink'=>$perms,
				'title'=>$title,
			));
		}
		elseif ($name=='sexy-tumblr') {
			$socials.=bookmark_list_item($name, array(
				'permalink'=>urlencode($perms),
				'title'=>$title,
			));
		}
		else {
			$socials.=bookmark_list_item($name, array(
				'permalink'=>$perms,
				'title'=>$title,
			));
		}
	}
	$socials.='</ul>'."\n".'<div style="clear:both;"></div>'."\n".'</div>';
	$socials.="\n".'<!-- End SexyBookmarks Menu Code -->'."\n\n";

	return $socials;
}

// This function is what allows people to insert the menu wherever they please rather than above/below a post...
function selfserv_sexy() {
	global $post;

	if(get_post_meta($post->ID, 'Hide SexyBookmarks')) {
		// Don't display SexyBookmarks
	}
	else {
		echo get_sexy();
	}
}

// Write the <head> code only on pages that the menu is set to display
function sexy_publicStyles() {
	global $sexy_plugopts, $post;

	// If custom field is set, do not display sexybookmarks
	if(get_post_meta($post->ID, 'Hide SexyBookmarks')) {
		echo "\n\n".'<!-- '.__('SexyBookmarks has been disabled on this page', 'sexybookmarks').' -->'."\n\n";
	}

	// Else menu should be displayed
	else {
		echo "\n\n";
		// If custom mods option is checked, pull files from new location
		if ($sexy_plugopts['custom-mods'] == 'yes') {
			wp_enqueue_style('sexy-bookmarks', WP_CONTENT_URL.'/sexy-mods/css/style.css', false, SEXY_vNum, 'all');
		}

		// Else use original file locations
		else {
			wp_enqueue_style('sexy-bookmarks', SEXY_PLUGPATH.'css/style.css', false, SEXY_vNum, 'all');
		}
	}
}
function sexy_publicScripts() {
	global $sexy_plugopts, $post;
	// If any javascript dependent options are selected, load the scripts
	if ($sexy_plugopts['expand'] || $sexy_plugopts['autocenter'] || $sexy_plugopts['targetopt']=='_blank') {
		// If custom mods option is selected, pull files from new location
		if ($sexy_plugopts['custom-mods'] == 'yes') {
			// If jQuery compatibility fix is not selected, go ahead and load jQuery
			if (empty($sexy_plugopts['doNotIncludeJQuery'])) {
				wp_enqueue_script('sexy-bookmarks-public-js', WP_CONTENT_URL.'/sexy-mods/js/sexy-bookmarks-public.js', array('jquery'));
			}
			// Else do not load jQuery, probably due to the user's theme or additional plugins not loading it properly to begin with
			else {
				wp_enqueue_script('sexy-bookmarks-public-js', WP_CONTENT_URL.'/sexy-mods/js/sexy-bookmarks-public.js');
			}
		}
		// Custom mods not selected, load files from original location
		else {
			// If jQuery compatibility fix is not selected, go ahead and load jQuery
			if (empty($sexy_plugopts['doNotIncludeJQuery'])) {
				wp_enqueue_script('sexy-bookmarks-public-js', SEXY_PLUGPATH.'js/sexy-bookmarks-public.js', array('jquery'));
			}
			// Else do not load jQuery, probably due to the user's theme or additional plugins not loading it properly to begin with
			else {
				wp_enqueue_script('sexy-bookmarks-public-js', SEXY_PLUGPATH.'js/sexy-bookmarks-public.js');
			}
		}
	}
}
add_action('wp_print_styles', 'sexy_publicStyles');
add_action('wp_print_scripts', 'sexy_publicScripts');



// Hook the menu to "the_content"
add_filter('the_content', 'sexy_position_menu');
?>