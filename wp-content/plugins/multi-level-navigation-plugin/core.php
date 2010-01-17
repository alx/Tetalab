<?php
// This is the core of the plugin. The section which actually outputs stuff to your site visitors

// Sets up enqueue scripts | enqueue prevents clashes between plugins which use the same script
wp_register_script('hoverIntent', $javascript_location.'hoverIntent.js.php', array('jquery'), 'r5');
wp_register_script('superfish', $javascript_location.'superfish.js', array('hoverIntent'), '1.4.8');
wp_register_script('superfish_init', $javascript_location.'superfish_settings.js.php', array('superfish'), '1.0');
wp_register_script('sfdelay', $javascript_location.'sfdelay.js','', '1.0');
wp_register_script('sfkeyboard', $javascript_location.'suckerfish_keyboard.js', array('hoverIntent'), '1.0');


// Adds content to HEAD section
add_action('wp_print_scripts', 'suckerfish_mainhead');
function suckerfish_mainhead() {global $pixopoint_mln_version;

	// ******** CSS TO BE ADDED TO HEAD *******
	// Only load this if not on an admin page
	if (!is_admin()) {echo '
<!-- Multi-level Navigation Plugin v'.$pixopoint_mln_version.' by PixoPoint Web Development ... http://pixopoint.com/multi-level-navigation/ -->
';
		if ($_SESSION['mln'] == 'on') {echo '<!-- MAINTENANCE MODE IS ACTIVATED! -->
';}
		// Displays CSS inline
		if (get_option('suckerfish_displaycss') == 'Inline') {echo '
<!-- Inline CSS from Multi-level Navigation Plugin | this mode is activated via the admin page -->
<style type="text/css">
' . get_option('suckerfish_css') . '
' . get_option('suckerfish_2_css') . '
</style>
';
		}
		// If we're not using the theme generated CSS, we've disabled CSS or the test CSS is enabled the style.php file is loaded
		elseif (get_option('suckerfish_generator') != 'Theme CSS' AND get_option('suckerfish_displaycss') != 'Disable' AND $_SESSION['mln_testcss'] != 'on') {echo '<link rel="stylesheet" type="text/css" href="'.WP_PLUGIN_URL.'/multi-level-navigation-plugin/style.php" />
';
		}
		elseif ($_SESSION['mln_testcss'] == 'on') {echo '<!-- Test CSS for Multi-level Navigation Plugin v'.$pixopoint_mln_version.' maintenance mode -->
<link rel="stylesheet" type="text/css" href="'.WP_PLUGIN_URL.'/multi-level-navigation-plugin/test.css" />
';}


		// ******** SCRIPTS TO BE ADDED TO HEAD *******
		// Adds script for keyboard accessibility
		if (get_option('suckerfish_keyboard') == 'on') {wp_enqueue_script('sfkeyboard');}
		// Checks if Superfish mode is needed, if not then goes back to suckerfish approach
		if (get_option('suckerfish_delay') != '0' || get_option('suckerfish_delay') != '0' || get_option('suckerfish_superfish_arrows') == 'on' || get_option('suckerfish_superfish_speed') != 'instant') {wp_enqueue_script('superfish_init');}
		// Any ideas how to enqueue a script in IE conditional comments?
		else {echo '
<!--[if lte IE 7]><script type="text/javascript" src="'.WP_PLUGIN_URL.'/multi-level-navigation-plugin/scripts/suckerfish_ie.js"></script><![endif]-->
';
		}
	}
}

/*,suckerfish_homeurl,suckerfish_categoriesurl,suckerfish_archivesurl,suckerfish_blogrollurl,suckerfish_recentcommentsurl,suckerfish_recentpostsurl*/



// Functions for displaying various menu contents
function pages() {$suckerfish_depthpages = get_option('suckerfish_depthpages');switch ($suckerfish_depthpages){case "Top level only":$suckerfish_depthpagesecho = '&depth=1';break;case "No nesting":$suckerfish_depthpagesecho = '&depth=-1';break;case "1 level of children":$suckerfish_depthpagesecho = '&depth=2';break;case "2 levels of children":$suckerfish_depthpagesecho = '&depth=3';break;case "Infinite":$suckerfish_depthpagesecho = '&depth=0';break;case "":$suckerfish_depthpagesecho = '&depth=0';break;} echo '', ereg_replace("\"><a [/\?a-zA-Z0-9\-\.\:\"\=\_ >]+</a>([\t\n]+)<ul"," haschildren\\0",wp_list_pages('title_li=&'.get_option('suckerfish_includeexcludepages').'='. get_option('suckerfish_excludepages').'&echo=0'.$suckerfish_depthpagesecho)) , '';}
function pagesdropdown() {$suckerfish_depthpages = get_option('suckerfish_depthpages');switch ($suckerfish_depthpages){case "Top level only":$suckerfish_depthpagesecho = '&depth=1';break;case "No nesting":$suckerfish_depthpagesecho = '&depth=-1';break;case "1 level of children":$suckerfish_depthpagesecho = '&depth=2';break;case "2 levels of children":$suckerfish_depthpagesecho = '&depth=3';break;case "Infinite":$suckerfish_depthpagesecho = '&depth=0';break;case "":$suckerfish_depthpagesecho = '&depth=0';break;} if (is_page()) $class=' class="current_page_parent current_page_item"'; echo '<li'.$class.'><a href="'; if (get_option('suckerfish_pagesurl') != '') {echo get_option('suckerfish_pagesurl');} echo '">' . get_option('suckerfish_pagestitle') . '</a><ul>', ereg_replace("\"><a [/\?a-zA-Z0-9\-\.\:\"\=\_ >]+</a>([\t\n]+)<ul"," haschildren\\0",wp_list_pages('title_li=&'.get_option('suckerfish_includeexcludepages').'='. get_option('suckerfish_excludepages').'&echo=0'.$suckerfish_depthpagesecho)) , "</ul></li>\n";}
// Gregs function pagesdropdown() {if (is_page()) $class=' class="current_page_parent current_page_item"'; echo '<li'.$class.'><a href="">' . get_option('suckerfish_pagestitle') . '</a><ul>', ereg_replace("\"><a [/\?a-zA-Z0-9\-\.\:\"\=\_ >]+</a>([\t\n]+)<ul"," haschildren\\0",wp_list_pages('title_li=&exclude='. get_option('suckerfish_excludepages').'&echo=0')) , "</ul></li>\n";}
function category() {
	if (get_option('suckerfish_categorycount') == 'on') {$suckerfish_categorycount = 'show_count=1';}
	if (get_option('suckerfish_categoryshowempty') == 'on') {$suckerfish_categoryshowempty = '&hide_empty=0';}
	$suckerfish_depthcategories = get_option('suckerfish_depthcategories');switch ($suckerfish_depthcategories){case "Top level only":$suckerfish_depthcategoriesecho = '&depth=1';break;case "No nesting":$suckerfish_depthcategoriesecho = '&depth=-1';break;case "1 level of children":$suckerfish_depthcategoriesecho = '&depth=2';break;case "2 levels of children":$suckerfish_depthcategoriesecho = '&depth=3';break;case "Infinite":$suckerfish_depthcategoriesecho = '&depth=0';break;case "":$suckerfish_depthcategoriesecho = '&depth=0';break;}
	$suckerfish_categoryorder=get_option('suckerfish_categoryorder');switch ($suckerfish_categoryorder){case "Ascending ID #":$suckerfish_categoryorderecho = '&orderby=id&order=ASC';break;case "Decending ID #":$suckerfish_categoryorderecho = '&orderby=id&order=DESC';break;case "Ascending Name":$suckerfish_categoryorderecho = '&orderby=name&order=ASC';break;case "Decending Name":$suckerfish_categoryorderecho = '&orderby=name&order=DESC';break;case "":$suckerfish_categoryorderecho = '&orderby=name&order=DESC';break;}
	wp_list_categories('title_li=&'.$suckerfish_categorycount.$suckerfish_categoryshowempty.'&'.get_option('suckerfish_includeexcludecategories').'='.get_option('suckerfish_excludecategories').$suckerfish_depthcategoriesecho);
	}
function categoriesdropdown() {





/* CODE FROM @itsanderson

function list_categories_posts($args=null){
	$categories = get_categories($args);
	$content = '';
	foreach ($categories as $cat){
		$content .= "<li id='cat-{$cat->cat_ID}' class='cat'><a href='" . get_category_link($cat->cat_ID) . "'>{$cat->name}</a></li>";
		$post_query = new WP_Query("cat={$cat->cat_ID}");
		$posts = $post_query->get_posts();
		if(count($posts)){
			$content .= '<ul>';
			foreach ($posts as $post){
				$content .= '<li class="post"><a href="' . get_permalink($post->ID) . '">' . $post->post_title . '</a></li>';
			}
			$content .= '</ul>';
		}
		$content .= '</li>';
	}
	echo $content;
}
list_categories_posts();

*/

	if (get_option('suckerfish_categorycount') == 'on') {$suckerfish_categorycount = 'show_count=1';}
	if (get_option('suckerfish_categoryshowempty') == 'on') {$suckerfish_categoryshowempty = '&hide_empty=0';}
	$suckerfish_depthcategories = get_option('suckerfish_depthcategories');switch ($suckerfish_depthcategories){case "Top level only":$suckerfish_depthcategoriesecho = '&depth=1';break;case "No nesting":$suckerfish_depthcategoriesecho = '&depth=-1';break;case "1 level of children":$suckerfish_depthcategoriesecho = '&depth=2';break;case "2 levels of children":$suckerfish_depthcategoriesecho = '&depth=3';break;case "Infinite":$suckerfish_depthcategoriesecho = '&depth=0';break;case "":$suckerfish_depthcategoriesecho = '&depth=0';break;}
	$suckerfish_categoryorder=get_option('suckerfish_categoryorder');switch ($suckerfish_categoryorder){case "Ascending ID #":$suckerfish_categoryorderecho = '&orderby=id&order=ASC';break;case "Decending ID #":$suckerfish_categoryorderecho = '&orderby=id&order=DESC';break;case "Ascending Name":$suckerfish_categoryorderecho = '&orderby=name&order=ASC';break;case "Decending Name":$suckerfish_categoryorderecho = '&orderby=name&order=DESC';break;case "":$suckerfish_categoryorderecho = '&orderby=name&order=DESC';break;}
	if (is_category()) {$suckerfish_class=' class="categories haschildren current_page_parent current_page_item"';}
	else {$suckerfish_class=' class="categories haschildren"';}
	echo '<li'.$suckerfish_class.'><a href="'; if (get_option('suckerfish_categoriesurl') != '') {echo get_option('suckerfish_categoriesurl');} echo '">' . get_option('suckerfish_categoriestitle') . '</a><ul>' , implode("</a>\n<ul",explode("</a>\n<ul",str_replace("\t",'',wp_list_categories('title_li='.$suckerfish_categoryshowempty.'&'.$suckerfish_categorycount.'&'.get_option('suckerfish_includeexcludecategories').'='. get_option('suckerfish_excludecategories').'&echo=0'.$suckerfish_categoryorderecho.$suckerfish_depthcategoriesecho)))) , "</ul></li>\n";
	}
// Gregs function categoriesdropdown() {if (is_category()) {$suckerfish_class=' class="current_page_parent current_page_item"';} echo '<li'.$suckerfish_class.'><a href="">' . get_option('suckerfish_categoriestitle') . '</a><ul>' , implode("Z</a>\n<ul",explode("</a>\n<ul",str_replace("\t",'',wp_list_categories('title_li=&exclude='. get_option('suckerfish_excludecategories').'&echo=0')))) , "</ul></li>\n";}
function home() {if (is_home()) {$suckerfish_class=' class="current_page_item"';} echo '<li'.$suckerfish_class.'><a href="'; if (get_option('suckerfish_homeurl') != '') {echo get_option('suckerfish_homeurl');} else {echo bloginfo('url').'/';} echo '">' . get_option('suckerfish_hometitle') . '</a></li>';}
function blogroll() {wp_list_bookmarks('title_li=&categorize=0');}
function blogrolldropdown() {echo '<li><a href="'; if (get_option('suckerfish_blogrollurl') != '') {echo get_option('suckerfish_blogrollurl');} echo '">' . get_option('suckerfish_blogrolltitle') . '</a> <ul>' , wp_list_bookmarks('title_li=&categorize=0') , '</ul></li>';}
function blogrollcategories() {wp_list_bookmarks('title_li=&title_before=<a href="">&title_after=</a>&categorize=1&before=<li>&after=</li>&show_images=0&show_description=0&orderby=url');}
function blogrollcategoriesdropdown() {echo '<li><a href="'; if (get_option('suckerfish_blogrollurl') != '') {echo get_option('suckerfish_blogrollurl');} echo '">' . get_option('suckerfish_blogrolltitle') . '</a> <ul>' , wp_list_bookmarks('title_li=&title_before=<a href="">&title_after=</a>&categorize=1&before=<li>&after=</li>&show_images=0&show_description=0&orderby=url') , '</ul></li>';}
function archivesmonths() {wp_get_archives('type=monthly');}
function archivesyears() {wp_get_archives('type=yearly');}
function archivesmonthsdropdown() {if (is_month()) {$suckerfish_class=' class="current_page_parent current_page_item"';}echo '<li'.$suckerfish_class.'><a href="'; if (get_option('suckerfish_archivesurl') != '') {echo get_option('suckerfish_archivesurl');} echo '">' . get_option('suckerfish_archivestitle') . '</a><ul>' , wp_get_archives('type=monthly') , '</ul></li>';}
function archivesyearsdropdown() {if (is_year()) {$suckerfish_class=' class="current_page_parent current_page_item"';}echo '<li'.$suckerfish_class.'><a href="'; if (get_option('suckerfish_archivesurl') != '') {echo get_option('suckerfish_archivesurl');} echo '">' . get_option('suckerfish_archivestitle') . '</a><ul>' , wp_get_archives('type=yearly') , '</ul></li>';}
function recentcomments() {echo '<li><a href="'; if (get_option('suckerfish_recentcommentsurl') != '') {echo get_option('suckerfish_recentcommentsurl');} echo '">' . get_option('suckerfish_recentcommentstitle') . '</a>'; global $wpdb; $sql = "SELECT DISTINCT ID, post_title, post_password, comment_ID, comment_post_ID, comment_author, comment_date_gmt, comment_approved, comment_type,comment_author_url, SUBSTRING(comment_content,1,30) AS com_excerpt FROM $wpdb->comments LEFT OUTER JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID) WHERE comment_approved = '1' AND comment_type = '' AND post_password = '' ORDER BY comment_date_gmt DESC LIMIT ".get_option('suckerfish_recentcommentsnumber'); $comments = $wpdb->get_results($sql); $output = $pre_HTML; $output .= "\n<ul>"; foreach ($comments as $comment) {$output .= "\n<li><a href=\"" . get_permalink($comment->ID) . "#comment-" . $comment->comment_ID . "\" title=\"on " . $comment->post_title . "\">".strip_tags($comment->comment_author) .":" . " " . strip_tags($comment->com_excerpt) ."</a></li>"; } $output .= "\n</ul>"; $output .= $post_HTML; echo $output; echo '</li>';}
function custom() {echo get_option('suckerfish_custommenu');}
function custom2() {echo get_option('suckerfish_custommenu2');}
function custom3() {echo get_option('suckerfish_custommenu3');}
function custom4() {echo get_option('suckerfish_custommenu4');}
function recentposts() {echo '<li><a href="'; if (get_option('suckerfish_recentpostsurl') != '') {echo get_option('suckerfish_recentpostsurl');} echo '">' . get_option('suckerfish_recentpoststitle') . '</a><ul>';query_posts('showposts='.get_option('suckerfish_recentpostsnumber'));?><?php while (have_posts()) : the_post(); ?><?php echo '<li><a href="'; the_permalink(); echo '">'; the_title(); echo '</a></li>'; ?><?php endwhile;?><?php wp_reset_query(); ?><?php echo '</ul>';}
function pages_excludechildren() {$args = array('post_type' => 'page','post_parent' => get_option('suckerfish_excludepages'), /*any parent*/); $suckerfish_excludepageschildren .= get_option('suckerfish_excludepages').','; if(get_option('suckerfish_excludepages') != ''){$attachments = get_children($args);} if ($attachments) {foreach ($attachments as $post) {$suckerfish_excludepageschildren .= $post->ID.',';} } echo '', ereg_replace("\"><a [/\?a-zA-Z0-9\-\.\:\"\=\_ >]+</a>([\t\n]+)<ul"," haschildren\\0",wp_list_pages('title_li=&exclude='.$suckerfish_excludepageschildren.'&echo=0')) , '';}
function pagesdropdown_excludechildren() {$args = array('post_type' => 'page','post_parent' => get_option('suckerfish_excludepages'), /*any parent*/); $suckerfish_excludepageschildren .= get_option('suckerfish_excludepages').','; if(get_option('suckerfish_excludepages') != ''){$attachments = get_children($args);} if ($attachments) {foreach ($attachments as $post) {$suckerfish_excludepageschildren .= $post->ID.',';} } if (is_page()) $class=' class="current_page_parent current_page_item"'; echo '<li'.$class.'><a href="'; if (get_option('suckerfish_pagesurl') != '') {echo get_option('suckerfish_pagesurl');} echo '">' . get_option('suckerfish_pagestitle') . '</a><ul>', ereg_replace("\"><a [/\?a-zA-Z0-9\-\.\:\"\=\_ >]+</a>([\t\n]+)<ul"," haschildren\\0",wp_list_pages('title_li=&exclude='.$suckerfish_excludepageschildren.'&echo=0')) , "</ul></li>\n"; }


// Support for function built into theme
function pixopoint_menu($pixo_which=1) {echo '
<!-- Multi-level Navigational Plugin by PixoPoint Web Development ... http://pixopoint.com/multi-level-navigation/ -->
';
// Checks to see if theme CSS should be used and serves different HTML if isn't - so that theme CSS won't mess up plugin CSS
if (get_option('suckerfish_generator') == 'Theme CSS') {echo '
<div id="menu_wrapper'.$pixo_which.'">
	<div id="menu'.$pixo_which.'">
';}
else {echo '
<div id="pixopoint_menu_wrapper'.$pixo_which.'">
	<div id="pixopoint_menu'.$pixo_which.'">
';}

if (get_option('suckerfish_titletags') == 'on') {
	ob_start();
}

// Main menu option
if ($pixo_which == '1') {?>
		<ul class="sf-menu" id="suckerfishnav"><?php
	global $suckerfish_menuitem;
	foreach($suckerfish_menuitem as $key=> $menuitem) {
		switch ($menuitem){
			case "Pages":pages();break;
			case "Pages (single dropdown)":pagesdropdown();break;
			case "Categories":category();break;
			case "Categories (single dropdown)":categoriesdropdown();break;
			case "Home":home();break;
			case "Links - no categories":blogroll();break;
			case "Links - no categories (single dropdown)":blogrolldropdown();break;
			case "Links - with categories":blogrollcategories();break;
			case "Links - with categories (single dropdown)":blogrollcategoriesdropdown();break;
			case "Archives - months":archivesmonths();break;
			case "Archives - years":archivesyears();break;
			case "Archives - months (single dropdown)":archivesmonthsdropdown();break;
			case "Archives - years (single dropdown)":archivesyearsdropdown();break;
			case "Recent Comments (single dropdown)":recentcomments();break;
			case "Custom 1":custom();break;
			case "Custom 2":custom2();break;
			case "Custom 3":custom3();break;
			case "Custom 4":custom4();break;
			case "Recent Posts (single dropdown)":recentposts();break;
			}
	}
}
// Second menu option
if ($pixo_which == '2') {?>
		<ul class="sf-menu" id="suckerfishnav_2"><?php
	global $suckerfish_2_menuitem;
	foreach($suckerfish_2_menuitem as $key=> $menuitem) {
		switch ($menuitem){
			case "Pages":pages();break;
			case "Pages (single dropdown)":pagesdropdown();break;
			case "Categories":category();break;
			case "Categories (single dropdown)":categoriesdropdown();break;
			case "Home":home();break;
			case "Links - no categories":blogroll();break;
			case "Links - no categories (single dropdown)":blogrolldropdown();break;
			case "Links - with categories":blogrollcategories();break;
			case "Links - with categories (single dropdown)":blogrollcategoriesdropdown();break;
			case "Archives - months":archivesmonths();break;
			case "Archives - years":archivesyears();break;
			case "Archives - months (single dropdown)":archivesmonthsdropdown();break;
			case "Archives - years (single dropdown)":archivesyearsdropdown();break;
			case "Recent Comments (single dropdown)":recentcomments();break;
			case "Custom 1":custom();break;
			case "Custom 2":custom2();break;
			case "Custom 3":custom3();break;
			case "Custom 4":custom4();break;
			case "Recent Posts (single dropdown)":recentposts();break;
			}
	}
}

if (get_option('suckerfish_titletags') == 'on') {
	$pixo_menucontents = ob_get_contents();
	ob_end_clean();
	$pixo_menucontents = preg_replace('/title=\"(.*?)\"/','',$pixo_menucontents);
	$pixo_menucontents = preg_replace('/title=\'(.*?)\'/','',$pixo_menucontents);
	echo $pixo_menucontents;
}

	?></ul>
	</div>
</div>
<?php }

// Backwards support for old function (also strips out comment tags to allow inserting this function inside IE conditional comments
function suckerfish() {
	ob_start();
	pixopoint_menu(1);
	$suckerfish_html = ob_get_contents();
	ob_end_clean();
	$suckerfish_html = str_replace('<!-- Multi-level Navigational Plugin by PixoPoint Web Development ... http://pixopoint.com/multi-level-navigation/ -->', '', $suckerfish_html);
	echo $suckerfish_html;
}


// Old functions for REALLY OLD  versions of the plugin ... why do some people not want to use the new way of handling menu contents?
function suckerfish1() {echo '<ul id="suckerfishnav">'.wp_list_pages('title_li=').'</ul>';}
function suckerfish2() {echo '<ul id="suckerfishnav"><li><a href="'.bloginfo('url').'/">Home</a></li>'.wp_list_pages('title_li=').'</ul>';}
function suckerfish3() {echo '<ul id="suckerfishnav"><li><a href="#">Pages</a><ul>'/wp_list_pages('title_li=') , '</ul></li><li><a href="#">Archives</a><ul>'.wp_get_archives().'</ul></li><li><a href="#">Categories</a><ul>'.wp_list_categories('title_li=').'</ul></li><li><a href="#">Links</a> <ul>'.wp_list_bookmarks('title_li=&categorize=0').'</ul></li></ul>';}
function suckerfish4() {echo '<ul id="suckerfishnav">'.wp_list_pages('title_li=').'<li><a href="#">Archives</a><ul>'.wp_get_archives().'</ul></li><li><a href="#">Categories</a><ul>'.wp_list_categories('title_li=').'</ul></li></ul>';}
function suckerfish5() {echo '<ul id="suckerfishnav"><li><a href="'.bloginfo('url').'/">Home</a></li>'.wp_list_pages('title_li=').'<li><a href="#">Archives</a><ul>'.wp_get_archives().'</ul></li><li><a href="#">Categories</a><ul>'.wp_list_categories('title_li=').'</ul></li></ul>';}


?>
