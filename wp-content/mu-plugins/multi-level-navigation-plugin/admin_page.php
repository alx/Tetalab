<?php

// Adds a new submenu under Options in the admin panel:
add_action('admin_menu', 'show_suckerfish_options');
function show_suckerfish_options() {
	// Adds various options for admin page menu
	$page = add_options_page('Multi-level Navigation Plugin Options', 'Multi-level Navigation', 8, 'multileveloptions', 'suckerfish_options');
	add_action( "admin_print_scripts-$page", 'suckerfish_adminhead' );
	add_option('suckerfish_css', '#suckerfishnav {background:#1F3E9F url("../multi-level-navigation-plugin/images/suckerfish_blue.png") repeat-x;font-size:18px;font-family:verdana,sans-serif;font-weight:bold;	width:100%;}#suckerfishnav, #suckerfishnav ul {float:left;list-style:none;line-height:40px;padding:0;border:1px solid #aaa;margin:0;	width:100%;}#suckerfishnav a {display:block;color:#dddddd;text-decoration:none;padding:0px 10px;}#suckerfishnav li {float:left;padding:0;}#suckerfishnav ul {position:absolute;left:-999em;height:auto;	width:151px;font-weight:normal;margin:0;line-height:1;	border:0;border-top:1px solid #666666;	}#suckerfishnav li li {	width:149px;border-bottom:1px solid #666666;border-left:1px solid #666666;border-right:1px solid #666666;font-weight:bold;font-family:verdana,sans-serif;}#suckerfishnav li li a {padding:4px 10px;	width:130px;font-size:12px;color:#dddddd;}#suckerfishnav li ul ul {margin:-21px 0 0 150px;}#suckerfishnav li li:hover {background:#1F3E9F;}#suckerfishnav li ul li:hover a, #suckerfishnav li ul li li:hover a, #suckerfishnav li ul li li li:hover a, #suckerfishnav li ul li li li:hover a  {color:#dddddd;}#suckerfishnav li:hover a, #suckerfishnav li.sfhover a {color:#dddddd;}#suckerfishnav li:hover li a, #suckerfishnav li li:hover li a, #suckerfishnav li li li:hover li a, #suckerfishnav li li li li:hover li a {color:#dddddd;}#suckerfishnav li:hover ul ul, #suckerfishnav li:hover ul ul ul, #suckerfishnav li:hover ul ul ul ul, #suckerfishnav li.sfhover ul ul, #suckerfishnav li.sfhover ul ul ul, #suckerfishnav li.sfhover ul ul ul ul  {left:-999em;}#suckerfishnav li:hover ul, #suckerfishnav li li:hover ul, #suckerfishnav li li li:hover ul, #suckerfishnav li li li li:hover ul, #suckerfishnav li.sfhover ul, #suckerfishnav li li.sfhover ul, #suckerfishnav li li li.sfhover ul, #suckerfishnav li li li li.sfhover ul  {left:auto;background:#444444;}#suckerfishnav li:hover, #suckerfishnav li.sfhover {background:#5E7AD3;}');
	add_option('suckerfish_superfish', '');
	add_option('suckerfish_superfish_speed', 'normal');
	add_option('suckerfish_superfish_time', '800');
	add_option('suckerfish_superfish_timeout', '100');
	add_option('suckerfish_menuitem1', 'Home');
	add_option('suckerfish_menuitem2', 'Pages');
	add_option('suckerfish_menuitem3', 'Categories (single dropdown)');
	add_option('suckerfish_menuitem4', 'Archives - months (single dropdown)');
	add_option('suckerfish_menuitem5', 'Links - no categories (single dropdown)');
	add_option('suckerfish_menuitem6', 'None');
	add_option('suckerfish_menuitem7', 'None');
	add_option('suckerfish_menuitem8', 'None');
	add_option('suckerfish_menuitem9', 'None');
	add_option('suckerfish_menuitem10', 'None');
	add_option('suckerfish_hometitle', 'Home');
	add_option('suckerfish_pagestitle', 'Pages');
	add_option('suckerfish_categoriestitle', 'Categories');
	add_option('suckerfish_archivestitle', 'Archives');
	add_option('suckerfish_blogrolltitle', 'Links');
	add_option('suckerfish_recentcommentstitle', 'Recent Comments');
	add_option('suckerfish_recentpoststitle', 'Recent Posts');
	add_option('suckerfish_keyboard', '');
	add_option('suckerfish_disablecss', '');
	add_option('suckerfish_inlinecss', '');
	add_option('suckerfish_superfish_delaymouseover', '200');
	add_option('suckerfish_superfish_sensitivity', 'high');
	add_option('suckerfish_maintenance', '');
	add_option('suckerfish_2_css', ''); // Intentionally blank to stop second CSS file displaying by default
	add_option('suckerfish_2_menuitem1', 'Home');
	add_option('suckerfish_2_menuitem2', 'Pages');
	add_option('suckerfish_2_menuitem3', 'Categories (single dropdown)');
	add_option('suckerfish_2_menuitem4', 'Archives - months (single dropdown)');
	add_option('suckerfish_2_menuitem5', 'Links (single dropdown)');
	add_option('suckerfish_2_menuitem6', 'None');
	add_option('suckerfish_2_menuitem7', 'None');
	add_option('suckerfish_2_menuitem8', 'None');
	add_option('suckerfish_2_menuitem9', 'None');
	add_option('suckerfish_2_menuitem10', 'None');
	add_option('suckerfish_categoryorder', 'Ascending Name');
	if (get_option('suckerfish_categoryorder') == 'Decending Name') {update_option('suckerfish_categoryorder','Descending Name');}
	if (get_option('suckerfish_categoryorder') == 'Decending ID #') {update_option('suckerfish_categoryorder','Descending ID #');}
	add_option('suckerfish_categorycount', '');
	add_option('suckerfish_titletags', '');
	add_option('suckerfish_recentpostsnumber', '10');
	add_option('suckerfish_recentcommentsnumber', '10');
	if (get_option('suckerfish_includeexcludecategories') == 'Exclude') {update_option('suckerfish_includeexcludecategories','exclude');}
	if (get_option('suckerfish_delay') == 'on') {update_option('suckerfish_delay','600');}
	if (get_option('suckerfish_delay') == '') {update_option('suckerfish_delay','0');}

	// Register Settings - needed for WP Mu support
	$settings_list = array(
		'suckerfish_css',
		'suckerfish_superfish',
		'suckerfish_superfish_speed',
		'suckerfish_superfish_time',
		'suckerfish_superfish_timeout',
		'suckerfish_menuitem1',
		'suckerfish_menuitem2',
		'suckerfish_menuitem3',
		'suckerfish_menuitem4',
		'suckerfish_menuitem5',
		'suckerfish_menuitem6',
		'suckerfish_menuitem7',
		'suckerfish_menuitem8',
		'suckerfish_menuitem9',
		'suckerfish_menuitem10',
		'suckerfish_pagestitle',
		'suckerfish_keyboard',
		'suckerfish_excludepages',
		'suckerfish_excludecategories',
		'suckerfish_hometitle',
		'suckerfish_pagestitle',
		'suckerfish_categoriestitle',
		'suckerfish_archivestitle',
		'suckerfish_blogrolltitle',
		'suckerfish_recentcommentstitle',
		'suckerfish_recentpoststitle',
		'suckerfish_disablecss',
		'suckerfish_custommenu',
		'suckerfish_custommenu2',
		'suckerfish_custommenu3',
		'suckerfish_custommenu4',
		'suckerfish_inlinecss',
		'suckerfish_includeexcludepages',
		'suckerfish_2_css',
		'suckerfish_2_menuitem1',
		'suckerfish_2_menuitem2',
		'suckerfish_2_menuitem3',
		'suckerfish_2_menuitem4',
		'suckerfish_2_menuitem5',
		'suckerfish_2_menuitem6',
		'suckerfish_2_menuitem7',
		'suckerfish_2_menuitem8',
		'suckerfish_2_menuitem9',
		'suckerfish_2_menuitem10',
		'suckerfish_generator',
		'suckerfish_delay',
		'suckerfish_superfish_shadows',
		'suckerfish_superfish_arrows',
		'suckerfish_showdelay',
		'suckerfish_displaycss',
		'suckerfish_secondmenu',
		'osort_order',
		'suckerfish_superfish_delaymouseover',
		'suckerfish_superfish_hoverintent',
		'suckerfish_superfish_sensitivity',
		'suckerfish_maintenance',
		'suckerfish_categoryorder',
		'suckerfish_includeexcludecategories',
		'suckerfish_homeurl',
		'suckerfish_pagesurl',
		'suckerfish_categoriesurl',
		'suckerfish_archivesurl',
		'suckerfish_blogrollurl',
		'suckerfish_recentcommentsurl',
		'suckerfish_recentpostsurl',
		'suckerfish_depthcategories',
		'suckerfish_depthpages',
		'suckerfish_categorycount',
		'suckerfish_categoryshowempty',
		'suckerfish_titletags',
		'suckerfish_recentpostsnumber',
		'suckerfish_recentcommentsnumber',
	);
	foreach ( $settings_list as $setting_item ) {
		register_setting('multilevelnavigation', $setting_item);
	}
}


// Sets up enqueue scripts | enqueue prevents clashes between plugins which use the same script
wp_register_script('tabber-init', $javascript_location.'tabber-init.js','', '1.0');
wp_register_script('tabber', $javascript_location.'tabber-minimized.js', array('tabber-init'), '1.9');

// Creating the admin page
function suckerfish_options() {
	global $suckerfish_menuitem;
	global $suckerfish_2_menuitem;
	// Adjusts menu options to new naming scheme for latest plugin version. Should only be called when in admin panel to reduce page loads
	require('upgrader.php');






	// Contains the admin page content itself
?>
<?php $pixo_test = 'off'; ?>

<div class="wrap">
<form method="post" action="options.php" id="options">
<?php settings_fields('multilevelnavigation'); ?>
<h2>PixoPoint Multi-level Navigation Plugin</h2>
<div style="clear:both;padding-top:5px;"></div>
<div class="tabber" id="mytabber1">


<?php /* Home tab */ ?>
<div class="tabbertab">
<h2><?php _e('Home','pixopoint_mln'); ?></h2>

<div class="clear"></div>
<table class="widefat" cellspacing="0" id="active-plugins-table">
	<thead>
	<tr>
		<th scope="col"><?php _e('Introduction','pixopoint_mln'); ?></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th scope="col"><?php _e('Thanks for using our plugin :)','pixopoint_mln'); ?></th>
	</tr>
	</tfoot>
	<tbody class="plugins">
		<tr class="inactive">
			<td class="desc">
				<p><?php _e('The Multi-level Navigation Plugin creates a dropdown, flyout or slider menu for your WordPress site based on the <a href="http://www.htmldog.com/articles/suckerfish/ target="_blank">Son of Suckerfish technique</a>. If you have any comments, questions or suggestions about this plugin, please visit the <a href="http://pixopoint.com/forum/index.php?board=4.0">PixoPoint multi-level navigation forum</a>.','pixopoint_mln'); ?></p>
				<h4><?php _e('Installation','pixopoint_mln'); ?></h4>
				<p><?php _e('Add the following code wherever you want the dropdown to appear in your theme (usually header.php)','pixopoint_mln'); ?></p>
				<p><code><?php _e('&lt;?php if (function_exists(\'pixopoint_menu\')) {pixopoint_menu();} ?&gt;','pixopoint_mln'); ?></code></p>
				<p><?php _e('To style your menu, please visit the <a href="http://pixopoint.com/suckerfish_css/">Multi-level Navigation CSS Generator</a> page to obtain your CSS and enter it into the "Appearance" tab.','pixopoint_mln'); ?></p>
			</td>
		</tr>
	</tbody>
</table>

<div class="clear"></div>
<table class="widefat" cellspacing="0" id="active-plugins-table">
	<thead>
	<tr>
		<th scope="col"><?php _e('Help','pixopoint_mln'); ?></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th scope="col"><?php _e('Please do not email us directly or via our <a href="http://pixopoint.com/contact/">contact form</a> for unpaid support. We prefer to offer support in our <a href="http://pixopoint.com/forum/">forum</a> so that others may learn from the advice','pixopoint_mln'); ?></th>
	</tr>
	</tfoot>
	<tbody class="plugins">
		<tr class="inactive">
			<td class="desc">
				<?php
				// Only added if main menu isn't already specified in theme - no point telling them to do something they're already doing
				if (!function_exists('pixopoint_mainmenu')) {?>
				<h4><?php _e('The easy way ... ','pixopoint_mln'); ?></h4>
				<p><?php _e('The easiest way to setup the PixoPoint Multi-level Navigation Plugin is to use it with a theme specifically designed to support the plugin. All themes exported from the <a href="http://pixopoint.com/generator/">PixoPoint Template Generator</a> (which have dropdown menus) support this by default. Simply activate your theme, then activate the plugin and the new menu will appear instantly. Visit the <a href="http://pixopoint.com/generator/">PixoPoint Template Generator</a> to get your own pre-supported theme.','pixopoint_mln'); ?></p>
				<p><?php _e('If you don\'t take this route then read on for simple instructions on how to integrate it into other themes ...','pixopoint_mln'); ?></p>
				<?php } ?>
				<h4><?php _e('Paid support','pixopoint_mln'); ?></h4>
				<p><?php _e('For direct help via the plugin (and CSS generator) author, please sign up for our Premium Support service ... <strong><a href="http://pixopoint.com/premium-support/">http://pixopoint.com/premium-support/</a></strong>','pixopoint_mln'); ?></p>
				<p><?php _e('Our Premium Support option is ideal if you have insufficient time to fix any problems you may have or simply don\'t know much about coding, we not only offer techinical support and access to our latest betas but we will also install the plugin and set it up on your site if needed. We also do customisations of the plugin for some premium members, although we recommend contacting us via our <a href="http://pixopoint.com/contact/">contact form</a> for such requests as some customisations may require extra payment depending on their complexity.','pixopoint_mln'); ?></p>
				<p><?php _e('If you have any questions about our premium services or are looking for other help with other WordPress or (X)HTML/CSS projects including theme development please do not hesitate to contact us via our <a href="http://pixopoint.com/contact/">contact form</a>. More information about our premium services is available on our site ... <strong><a href="http://pixopoint.com/premium-support/">http://pixopoint.com/premium-support/</a></strong>','pixopoint_mln'); ?></p>
				<h4><?php _e('FAQ','pixopoint_mln'); ?></h4>
				<p><?php _e('<strong>Q:</strong><em> Your plugin doesn\'t work in IE, why don\'t you fix it?</em> <br /><strong>A:</strong> The plugin does work with IE, you just haven\'t integrated it correctly. See \'Free support\' below for some tips on how to get it working with IE.','pixopoint_mln'); ?></p>
				<p><?php _e('<strong>Q:</strong><em> How do I change the menu contents?</em> <br /><strong>A:</strong> See the big tab at the top of the screen right now which says "Menu Contents"? Click that ...','pixopoint_mln'); ?></p>
				<p><?php _e('<strong>Q:</strong><em> How do I change the colour/font/whatever in my menu?</em> <br /><strong>A:</strong> Visit the <a href="http://pixopoint.com/suckerfish_css/">CSS generator</a>.','pixopoint_mln'); ?></p>
				<p><?php _e('<strong>Q:</strong><em> How do I get a fully customised version?</em> <br /><strong>A:</strong> Leave a message on the PixoPoint <a href="http://pixopoint.com/contact/">Contact Page</a> with your requirements and we will get back to you ASAP with pricing information. Alternatively you can sign up for our <a href="http://pixopoint.com/premium-support/">Premium Support</a> option which gives you access to our new dropdown, flyout and slider menu CSS generator, plus access to our premium support forum.','pixopoint_mln'); ?></p>
				<p><?php _e('<strong>Q:</strong><em> Why can\'t the plugin do X, Y or Z?</em> <br /><strong>A:</strong> It probably can, we just haven\'t supplied instructions on how to do it. If you have any requests, then please leave them in the <a href="http://pixopoint.com/forum/index.php?board=4.0">PixoPoint dropdown menu support board</a>. We often update the plugin with new functionality and we\'re far more likely to include the functionality you want if we know there is a demand for it already.','pixopoint_mln'); ?></p>
				<h4><?php _e('Free support','pixopoint_mln'); ?></h4>
				<p><?php _e('If you follow all of the instructions here, activate the plugin and find the menu is appearing on your site but looks all messed up, then the problem is probably caused by a clash between your themes CSS and plugins CSS. These problems can usually be remedied by removing the wrapper tags which surround the menu in your theme. For example, most themes will have some HTML such as <code>&lt;div id="nav"&gt;&lt;?php wp_list_pages(); ?&gt;&lt;/div&gt;</code> which contains the existing themes menu. By placing the <code>pixopoint_menu()</code> function between those DIV tags, the menu will often interact with that DIV tag. The solution is to either remove the DIV tag or to alter it\'s CSS so that it doesn\'t interact with the menu.','pixopoint_mln'); ?></p>
				<p><?php _e('If you require further help with the plugin, please visit the <a href="http://pixopoint.com/multi-level-navigation/">Multi-level Navigation Plugin page</a> or the PixoPoint <a href="http://pixopoint.com/forum/index.php?board=4.0">multi-level navigation support board</a>.','pixopoint_mln'); ?></p>
				<p><?php _e('We are happy to answer questions but we\'ve been noticing lately that more time is being spent teaching people what to ask rather than actually answering anything. So before posting questions on the PixoPoint support forum, please read the following tips to help us answer your questions faster.','pixopoint_mln'); ?></p>
				<ul>
					<li><?php _e('Where is your CSS?','pixopoint_mln'); ?></li>
					<li><?php _e('What modifications have you made to the CSS?','pixopoint_mln'); ?></li>
					<li><?php _e('What browsers are you having problems with?','pixopoint_mln'); ?></li>
					<li><?php _e('What is the URL for your site?','pixopoint_mln'); ?></li>
					<li><?php _e('Provide a link to the problem. Most problems can not be answered without actually seeing your site. If you don\'t want to install the plugin on your live site and don\'t have a test site to show us, then view the source code in your browser when you do have the plugin installed, save it to an HTML file and upload that somewhere so that we can see what the page looks like.','pixopoint_mln'); ?></li>
					<li><?php _e('Do not bother providing us with HTML and/or CSS code snippets (without a link). There is very little we can do without seeing the entire page as most problems are caused by an obscure piece of CSS somewhere else on the page.','pixopoint_mln'); ?></li>
					<li><?php _e('Let us know if you have modified the CSS. If it is modified beyond what is available in the <a href="http://pixopoint.com/suckerfish_css/">CSS generator</a> we are unlikely to offer support for free. Rummaging through other peoples code is too time consuming sorry.','pixopoint_mln'); ?></li>
					<li><?php _e('If you didn\'t paste your CSS into the WP plugins settings page, let us know which <strong>exact</strong> file it is in. Searching through a dozen CSS files in your theme trying to find your menu code is not fun.','pixopoint_mln'); ?></li>
				</ul>
				<h4><?php _e('Other plugins we recommend','pixopoint_mln'); ?></h4>
				<p><?php _e('The <a href="http://wordpress.org/extend/plugins/my-page-order/">My Page Order plugin</a>  by <a href="http://www.geekyweekly.com/">froman118</a> allows you to set the order of \'WordPress Pages\' through a drag and drop interface. This replaces the need to use the clumsy default method of setting page orders built into WordPress. Another plugin recommended for this purpose is the <a href="http://joelstarnes.co.uk/blog/pagemash/">Page Mash Plugin</a> by Joel Starnes.','pixopoint_mln'); ?></p>
				<p><?php _e('If you know of any other plugins which work well with the \'Multi-level Navigation plugin\' then please us know about them.','pixopoint_mln'); ?></p>
			</td>
		</tr>
	</tbody>
</table>
<div style="clear:both"></div>
</div>


<?php /* Appearance tab */ ?>
<div class="tabbertab">
<h2><?php _e('Appearance','pixopoint_mln'); ?></h2>
<div class="clear"></div>
<?php
	// Adds a comment about choosing between the built in theme CSS and the plugins CSS
	if (function_exists('pixopoint_mainmenu') || function_exists('pixopoint_secondmenu')) {
		if (get_option('suckerfish_generator') == '') {update_option('suckerfish_generator', 'Theme CSS');}
	}
	// Checks if second menu is present in theme
	if (function_exists('pixopoint_secondmenu')) {update_option('suckerfish_secondmenu', 'on');}
	if (function_exists('pixopoint_mainmenu') || function_exists('pixopoint_secondmenu')) { ?>
<h4><?php _e('Built in theme CSS?','pixopoint_mln'); ?></h4>
<p><?php _e('You are currently using a theme designed to integrate directly with the Multi-level Navigation Plugin. You can either keep the existing menu design by using the themes CSS, or change to the plugins CSS to override the theme styling. To use the second menu option the ID of each CSS option will need to be manually changed from <code>suckerfishnav</code> to <code>suckerfishnav_2</code>.','pixopoint_mln'); ?></p>
<p>
	<select name="suckerfish_generator">
	<?php
	$suckerfish_generator = get_option('suckerfish_generator');
	switch ($suckerfish_generator){
		case "Theme CSS":echo '<option>Theme CSS</option><option>Plugin CSS</option>';break;
		case "Plugin CSS":echo '<option>Plugin CSS</option><option>Theme CSS</option>';break;
		case "":echo '<option>Theme CSS</option><option>Plugin CSS</option>';break;
		}
	?>
	</select>
</p>
<?php } ?>
<p><?php _e('To change the appearance of your menu, please visit the <a href="http://pixopoint.com/suckerfish_css/">PixoPoint Multi-level CSS Generator</a> to obtain CSS. Paste your new CSS into the main menu box below.','pixopoint_mln'); ?></p>
<div class="clear"></div>
<table class="widefat" cellspacing="0" id="active-plugins-table">
	<thead>
	<tr>
		<th scope="col"><?php _e('Enter the CSS for your main menu here','pixopoint_mln'); ?></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th scope="col"><?php _e('To obtain new CSS, please visit the <a href="http://pixopoint.com/suckerfish_css/">PixoPoint CSS generator</a>','pixopoint_mln'); ?></th>
	</tr>
	</tfoot>
	<tbody class="plugins">
		<tr class="inactive">
			<td class="desc"><p><textarea name="suckerfish_css" style="width:100%;border:none" value="" rows="10"><?php echo get_option('suckerfish_css'); ?></textarea></p></td>
		</tr>
	</tbody>
</table>

<?php if (get_option('suckerfish_secondmenu') == 'on') {?>
<div class="clear"></div>
<table class="widefat" cellspacing="0" id="active-plugins-table">
	<thead>
	<tr>
		<th scope="col"><?php _e('Enter the CSS for your second menu here. Note: the ID of these menu items must be suckerfishnav_2','pixopoint_mln'); ?></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th scope="col"><?php _e('As this is the second menu, the CSS ID\'s must be different from the first. The ID of these menu items must be suckerfishnav_2 which is not the default format from the <a href="http://pixopoint.com/suckerfish_css/">CSS generator</a> so if you want to use the <a href="http://pixopoint.com/suckerfish_css/">CSS generator</a> for this option you will need to \'search and replace\' (in a text editor) <strong>suckerfishnav</strong> to <strong>suckerfishnav_2</strong>.','pixopoint_mln'); ?></th>
	</tr>
	</tfoot>
	<tbody class="plugins">
		<tr class="inactive">
			<td class="desc">
				<p><textarea name="suckerfish_2_css" style="width:100%;border:none" value="" rows="10"><?php echo get_option('suckerfish_2_css'); ?></textarea></p>
			</td>
		</tr>
	</tbody>
</table>
<?php } ?>
</div>

<?php /* Menu contents */ ?>
<?php if ($pixo_test != 'on') {?>
<div class="tabbertab">
  <h2><?php _e('Menu contents','pixopoint_mln'); ?></h2>
<div class="clear"></div>
<table class="widefat" cellspacing="0" id="active-plugins-table">
	<thead>
	<tr>
		<th scope="col"><?php _e('Main menu contents','pixopoint_mln'); ?></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th scope="col"><?php _e('Modify the contents of your main menu via the options above.','pixopoint_mln'); ?></th>
	</tr>
	</tfoot>
	<tbody class="plugins">
		<tr class="inactive">
			<td class="desc">
				<p>
				<?php
					function contents($name) {
						$options = array('None','Home','Pages','Pages (single dropdown)','Categories','Categories (single dropdown)','Archives - months','Archives - months (single dropdown)','Archives - years','Archives - years (single dropdown)','Links - no categories','Links - no categories (single dropdown)','Links - with categories','Links - with categories (single dropdown)','Recent Comments (single dropdown)','Recent Posts (single dropdown)','Custom 1','Custom 2','Custom 3','Custom 4');
						$ret = '<option>'.$name.'</option>';
						foreach($options as $option) {if($name != $option) {$ret .= '<option>'.$option.'</option>';}}
						return $ret;
					}
					foreach($suckerfish_menuitem as $key => $menuitem) {
						echo '
						<div class="menuitems">
							<label>Menu Item #'.($key+1).'</label>
							<select name="suckerfish_menuitem'.($key+1).'">
								'.contents($menuitem).'
							</select>
						</div>';
					}
				?>
				</p>
			</td>
		</tr>
	</tbody>
</table>

<div class="clear"></div>
<table class="widefat" cellspacing="0" id="active-plugins-table">
	<thead>
	<tr>
		<th scope="col"><?php _e('Second menu contents','pixopoint_mln'); ?></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th scope="col"><?php _e('Modify the contents of your second menu via the options above.','pixopoint_mln'); ?></th>
	</tr>
	</tfoot>
	<tbody class="plugins">
		<tr class="inactive">
			<td class="desc">
				<p>
				<?php
					function contents_2($name) {
						$options = array('None','Home','Pages','Pages (single dropdown)','Categories','Categories (single dropdown)','Archives - months','Archives - months (single dropdown)','Archives - years','Archives - years (single dropdown)','Links - no categories','Links - no categories (single dropdown)','Links - with categories','Links - with categories (single dropdown)','Recent Comments (single dropdown)','Recent Posts (single dropdown)','Custom 1','Custom 2','Custom 3','Custom 4');
						$ret = '<option>'.$name.'</option>';
						foreach($options as $option) {if($name != $option) {$ret .= '<option>'.$option.'</option>';}}
						return $ret;
					}
					foreach($suckerfish_2_menuitem as $key => $menuitem) {
						echo '
						<div class="menuitems">
							<label>Menu Item #'.($key+1).'</label>
							<select name="suckerfish_2_menuitem'.($key+1).'">
								'.contents_2($menuitem).'
							</select>
						</div>';
					}
				?>
			</p>
		</td>
	</tr>	</tbody>
</table>

<table class="widefat" cellspacing="0" id="active-plugins-table">
	<thead>
	<tr>
		<th scope="col"><?php _e('Modifications','pixopoint_mln'); ?></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th scope="col"><?php _e('To change the text displayed in the top level menu items for Pages, Categories etc. or to exclude or include specific Pages or Categories modify the above options.','pixopoint_mln'); ?></th>
	</tr>
	</tfoot>
	<tbody class="plugins">
		<tr class="inactive">
			<td class="desc">
				<h4><?php _e('Titles','pixopoint_mln'); ?></h4>
 			 	<div class="menuitems2">
					<p>
						<label><?php _e('Home','pixopoint_mln'); ?></label>
							<input type="text" name="suckerfish_hometitle" value="<?php echo get_option('suckerfish_hometitle'); ?>" />
					</p>
				</div>
		  	<div class="menuitems2">
					<p>
						<label><?php _e('Pages','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_pagestitle" value="<?php echo get_option('suckerfish_pagestitle'); ?>" />
					</p>
				</div>
		  	<div class="menuitems2">
					<p>
						<label><?php _e('Categories','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_categoriestitle" value="<?php echo get_option('suckerfish_categoriestitle'); ?>" />
					</p>
				</div>
		  	<div class="menuitems2">
					<p>
						<label><?php _e('Archives','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_archivestitle" value="<?php echo get_option('suckerfish_archivestitle'); ?>" />
					</p>
				</div>
		  	<div class="menuitems2">
					<p>
						<label><?php _e('Links','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_blogrolltitle" value="<?php echo get_option('suckerfish_blogrolltitle'); ?>" />
					</p>
				</div>
		  	<div class="menuitems2">
					<p>
						<label><?php _e('Recent Comments','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_recentcommentstitle" value="<?php echo get_option('suckerfish_recentcommentstitle'); ?>" />
					</p>
				</div>
		  	<div class="menuitems2">
					<p>
						<label><?php _e('Recent Posts','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_recentpoststitle" value="<?php echo get_option('suckerfish_recentpoststitle'); ?>" />
					</p>
				</div>
				<div class="clear"></div>
				<h4><?php _e('Title URL\'s','pixopoint_mln'); ?></h4>
				<p><?php _e('If a URL is not specified, then a default option will be used.','pixopoint_mln'); ?></p>
 			 	<div class="menuitems2">
					<p>
						<label><?php _e('Home','pixopoint_mln'); ?></label>
							<input type="text" name="suckerfish_homeurl" value="<?php echo get_option('suckerfish_homeurl'); ?>" />
					</p>
				</div>
		  	<div class="menuitems2">
					<p>
						<label><?php _e('Pages','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_pagesurl" value="<?php echo get_option('suckerfish_pagesurl'); ?>" />
					</p>
				</div>
		  	<div class="menuitems2">
					<p>
						<label><?php _e('Categories','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_categoriesurl" value="<?php echo get_option('suckerfish_categoriesurl'); ?>" />
					</p>
				</div>
		  	<div class="menuitems2">
					<p>
						<label><?php _e('Archives','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_archivesurl" value="<?php echo get_option('suckerfish_archivesurl'); ?>" />
					</p>
				</div>
		  	<div class="menuitems2">
					<p>
						<label><?php _e('Links','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_blogrollurl" value="<?php echo get_option('suckerfish_blogrollurl'); ?>" />
					</p>
				</div>
		  	<div class="menuitems2">
					<p>
						<label><?php _e('Recent Comments','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_recentcommentsurl" value="<?php echo get_option('suckerfish_recentcommentsurl'); ?>" />
					</p>
				</div>
		  	<div class="menuitems2">
					<p>
						<label><?php _e('Recent Posts','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_recentpostsurl" value="<?php echo get_option('suckerfish_recentpostsurl'); ?>" />
					</p>
				</div>
				<div class="clear"></div>
				<h4><?php _e('Pages/categories to exclude','pixopoint_mln'); ?></h4>
				<p><?php _e('If no pages or categories are specified then all of them will be included','pixopoint_mln'); ?></p>
			  <div class="includeexclude">
					<p>
						<label><?php _e('Pages to include or exclude in the main menu','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_excludepages" value="<?php echo get_option('suckerfish_excludepages'); ?>" />
					</p>
					<select name="suckerfish_includeexcludepages">
						<?php
						$suckerfish_includeexcludepages = get_option('suckerfish_includeexcludepages');
						switch ($suckerfish_includeexcludepages){
							case "include":echo '<option>include</option><option>exclude</option>';break;
							case "exclude":echo '<option>exclude</option><option>include</option>';break;
							case "":echo '<option>include</option><option>exclude</option>';break;
						}
						?>
					</select>
				</div>
		 	 	<div class="includeexclude">
					<p>
						<label><?php _e('Categories to include or exclude','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_excludecategories" value="<?php echo get_option('suckerfish_excludecategories'); ?>" />
					</p>
					<select name="suckerfish_includeexcludecategories">
					<?php
						$suckerfish_includeexcludecategories = get_option('suckerfish_includeexcludecategories');
						switch ($suckerfish_includeexcludecategories){
							case "include":echo '<option>include</option><option>exclude</option>';break;
							case "exclude":echo '<option>exclude</option><option>include</option>';break;
							case "":echo '<option>include</option><option>exclude</option>';break;
							}
					?>
					</select>
				</div>
				<div class="clear"></div>
				<h4><?php _e('Pages/categories depth','pixopoint_mln'); ?></h4>
				<p><?php _e('Controls the depth of the menu. \'No nesting\' means that all the available menu items will be displayed in a flat list with no children.','pixopoint_mln'); ?></p>
			  <div class="includeexclude">
					<p>
						<label><?php _e('Pages depth','pixopoint_mln'); ?></label>
					</p>
					<select name="suckerfish_depthpages">
						<?php
						$suckerfish_depthpages = get_option('suckerfish_depthpages');
						switch ($suckerfish_depthpages){
							case "Top level only":echo '<option>Top level only</option><option>No nesting</option><option>1 level of children</option><option>2 levels of children</option><option>Infinite</option>';break;
							case "No nesting":echo '<option>No nesting</option><option>Top level only</option><option>1 level of children</option><option>2 levels of children</option><option>Infinite</option>';break;
							case "1 level of children":echo '<option>1 level of children</option><option>Top level only</option><option>No nesting</option><option>2 levels of children</option><option>Infinite</option>';break;
							case "2 levels of children":echo '<option>2 levels of children</option><option>Top level only</option><option>No nesting</option><option>1 level of children</option><option>Infinite</option>';break;
							case "Infinite":echo '<option>Infinite</option><option>Top level only</option><option>No nesting</option><option>1 level of children</option><option>2 levels of children</option>';break;
							case "":echo '<option>Infinite</option><option>Top level only</option><option>No nesting</option><option>1 level of children</option><option>2 levels of children</option>';break;
						}
						?>
					</select>
				</div>
		 	 	<div class="includeexclude">
					<p>
						<label><?php _e('Categories depth','pixopoint_mln'); ?></label>
					</p>
					<select name="suckerfish_depthcategories">
					<?php
						$suckerfish_depthecategories = get_option('suckerfish_depthcategories');
						switch ($suckerfish_depthecategories){
							case "Top level only":echo '<option>Top level only</option><option>No nesting</option><option>1 level of children</option><option>2 levels of children</option><option>Infinite</option>';break;
							case "No nesting":echo '<option>No nesting</option><option>Top level only</option><option>1 level of children</option><option>2 levels of children</option><option>Infinite</option>';break;
							case "1 level of children":echo '<option>1 level of children</option><option>Top level only</option><option>No nesting</option><option>2 levels of children</option><option>Infinite</option>';break;
							case "2 levels of children":echo '<option>2 levels of children</option><option>Top level only</option><option>No nesting</option><option>1 level of children</option><option>Infinite</option>';break;
							case "Infinite":echo '<option>Infinite</option><option>Top level only</option><option>No nesting</option><option>1 level of children</option><option>2 levels of children</option>';break;
							case "":echo '<option>Infinite</option><option>Top level only</option><option>No nesting</option><option>1 level of children</option><option>2 levels of children</option>';break;
							}
					?>
					</select>
				</div>
				<div class="clear"></div>
				<h4><?php _e('Categories settings','pixopoint_mln'); ?></h4>
				<p><?php _e('You may order your categories by Ascending ID #, Descending ID # or alphabetically, Ascending Name or Descending Name.','pixopoint_mln'); ?></p>
			  <div class="includeexclude">
					<p>
						<label><?php _e('Category order','pixopoint_mln'); ?></label>
					</p>
					<select name="suckerfish_categoryorder">
						<?php
						$suckerfish_categoryorder = get_option('suckerfish_categoryorder');
						switch ($suckerfish_categoryorder){
							case "Ascending ID #":echo '<option>Ascending ID #</option><option>Descending ID #</option><option>Ascending Name</option><option>Descending Name</option>';break;
							case "Descending ID #":echo '<option>Descending ID #</option><option>Ascending ID #</option><option>Ascending Name</option><option>Descending Name</option>';break;
							case "Ascending Name":echo '<option>Ascending Name</option><option>Descending Name</option><option>Descending ID #</option><option>Ascending ID #</option>';break;
							case "Descending Name":echo '<option>Descending Name</option><option>Ascending Name</option><option>Descending ID #</option><option>Ascending ID #</option>';break;
							case "":echo '<option>Ascending Name</option><option>Descending Name</option><option>Descending ID #</option><option>Ascending ID #</option>';break;
						}
						?>
					</select>
				</div>
			  <div class="includeexclude">
					<p style="margin-top:10px">
						<label><?php _e('Show empty categories','pixopoint_mln'); ?></label>
					</p>
					<?php
						if (get_option('suckerfish_categoryshowempty') == 'on') {echo '<input type="checkbox" name="suckerfish_categoryshowempty" checked="yes" />';}
						else {echo '<input type="checkbox" name="suckerfish_categoryshowempty" />';}
						?>
				</div>
				<div class="clear"></div>
				<h4><?php _e('Number of recent posts and comments','pixopoint_mln'); ?></h4>
				<p><?php _e('Controls the number of recent posts and comments shown when using the \'Recent Posts\' or \'Recent Comments\' menu option.','pixopoint_mln'); ?></p>
			  <div class="includeexclude">
					<p>
						<label><?php _e('Number of recent posts','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_recentpostsnumber" value="<?php echo get_option('suckerfish_recentpostsnumber'); ?>" />
					</p>
				</div>
			  <div class="includeexclude">
					<p>
						<label><?php _e('Number of recent comments','pixopoint_mln'); ?></label>
						<input type="text" name="suckerfish_recentcommentsnumber" value="<?php echo get_option('suckerfish_recentcommentsnumber'); ?>" />
					</p>
				</div>


<?php /*
				<p><?php _e('The number of posts in each category may be displayed alongside the category name.','pixopoint_mln'); ?></p>
			  <div class="includeexclude">
					<p style="margin-top:10px">
						<label><?php _e('Show post counts','pixopoint_mln'); ?></label>
					</p>
					<?php
						if (get_option('suckerfish_categorycount') == 'on') {echo '<input type="checkbox" name="suckerfish_categorycount" checked="yes" />';}
						else {echo '<input type="checkbox" name="suckerfish_categorycount" />';}
						?>
					</div>
*/ ?>
			</td>
		</tr>
	</tbody>
</table>

<style type="text/css">
	.csstooltip {display:inline-block}
	.csstooltip div {display:none}
	.csstooltip:hover div {width:55em;display:block;position:absolute;margin:-12em 0 0 0em;border:1px solid #ccc;background:#fff;font-family:sans-serif;color:#666;font-weight:normal;padding:10px 15px;font-size:12px}
	.csstooltip:hover div code {background:#fff}
	.csstooltip:hover div li,	.csstooltip:hover div li li,	.csstooltip:hover div li li li,	.csstooltip:hover div li li li li {font-size:12px;}
	.csstooltip:hover div a {color:#666}
</style>

<div class="clear"></div>
<table class="widefat" cellspacing="0" id="active-plugins-table">
	<thead>
		<tr>
			<th scope="col">
				<?php _e('Custom HTML code','pixopoint_mln'); ?> (<div class="csstooltip"><?php _e('example','pixopoint_mln'); ?><div>
				<?php _e('Note: You can have multiple top level menu items in one custom code entry. The following example will display a menu with links to \'Home\', \'Categories\' and \'Pages\', the \'Categories\' and \'Pages\' links would have dropdowns and the \'Page 1\' link in the \'Pages\' dropdown would contain another further level.','pixopoint_mln'); ?>
				<br />
				<code><br />
&lt;li&gt;&lt;a href="http://pixopoint.com/">Home&lt;/a&gt;&lt;/li&gt;<br />
&lt;li&gt;&lt;a href=""&gt;Categories&lt;/a&gt;<br />
&nbsp;&nbsp;&lt;ul&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;a href="http://pixopoint.com/categories/templates/"&gt;Templates&lt;/a&gt;&lt;/li&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;a href="http://pixopoint.com/categories/plugins/"&gt;Plugins&lt;/a&gt;&lt;/li&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;a href="http://pixopoint.com/categories/plugins/"&gt;WordPress&lt;/a&gt;&lt;/li&gt;<br />
&nbsp;&nbsp;&lt;/ul&gt;<br />
&lt;/li&gt;<br />
&lt;li&gt;&lt;a href=""&gt;Pages&lt;/a&gt;<br />
&nbsp;&nbsp;&lt;ul&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;a href="http://pixopoint.com/page1/">Page 1&lt;/a&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;ul&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;a href="http://pixopoint.com/page1/flyout/">Flyout&lt;/a&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;ul&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;a href="http://pixopoint.com/page1/flyout/test1/">Test 1&lt;/a&gt;&lt;/li&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;a href="http://pixopoint.com/page1/flyout/test2/">Test 2&lt;/a&gt;&lt;/li&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;a href="http://pixopoint.com/page1/flyout/test3/">Test 3&lt;/a&gt;&lt;/li&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/ul&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/li&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;a href="http://pixopoint.com/page1/nested1/">Nested 1&lt;/a&gt;&lt;/li&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;a href="http://pixopoint.com/page1/nested2/">Nested 2&lt;/a&gt;&lt;/li&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/ul&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/li&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;a href="http://pixopoint.com/page2/">Page 2&lt;/a&gt;&lt;/li&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;a href="http://pixopoint.com/page3/">Page 3&lt;/a&gt;&lt;/li&gt;<br />
&nbsp;&nbsp;&lt;/ul&gt;<br />
&lt;/li&gt;
				</code></div></div>)
			</th>
		</tr>
	</thead>
	<tfoot>
	<tr>
		<th scope="col"><?php _e('Enter the HTML for the \'Custom code 1\' and \'Custom code 2\' menu options above, add your code to the appropriate box above. <strong>Note:</strong> The menu uses an unordered list to format the menu. You will need to know some HTML to use this option. The menu is already wrapped in UL tags.','pixopoint_mln'); ?></th>
	</tr>
	</tfoot>
	<tbody class="plugins">
		<tr class="inactive">
			<td class="desc">
				<div style="float:left;width:48%">
					<h4><?php _e('Custom code 1','pixopoint_mln'); ?></h4>
					<p><textarea name="suckerfish_custommenu" style="height:200px;width:100%;border:1px solid #ddd" value=""><?php echo get_option('suckerfish_custommenu'); ?></textarea></p>
				</div>
				<div style="float:right;width:48%">
					<h4><?php _e('Custom code 2','pixopoint_mln'); ?></h4>
					<p><textarea name="suckerfish_custommenu2" style="height:200px;width:100%;border:1px solid #ddd" value=""><?php echo get_option('suckerfish_custommenu2'); ?></textarea></p>
				</div>
				<div style="float:left;width:48%">
					<h4><?php _e('Custom code 3','pixopoint_mln'); ?></h4>
					<p><textarea name="suckerfish_custommenu3" style="height:200px;width:100%;border:1px solid #ddd" value=""><?php echo get_option('suckerfish_custommenu3'); ?></textarea></p>
				</div>
				<div style="float:right;width:48%">
					<h4><?php _e('Custom code 4','pixopoint_mln'); ?></h4>
					<p><textarea name="suckerfish_custommenu4" style="height:200px;width:100%;border:1px solid #ddd" value=""><?php echo get_option('suckerfish_custommenu4'); ?></textarea></p>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<div style="clear:both"></div>
</div>
<?php } ?>


<?php /* Settings tab */ ?>
<div class="tabbertab">
<h2><?php _e('Settings','pixopoint_mln'); ?></h2>
<div class="clear"></div>

<table class="widefat" cellspacing="0" id="inactive-plugins-table">
	<thead>
		<tr>
			<th scope="col" colspan="2"><?php _e('Setting','pixopoint_mln'); ?></th>
			<th scope="col"><?php _e('Description','pixopoint_mln'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th scope="col" colspan="3"><?php _e('Use the various options above to control some of the advanced settings of the plugin','pixopoint_mln'); ?></th>
		</tr>
	</tfoot>
	<tbody class="plugins">
		<tr class='inactive'>
			<th scope='row' class='check-column'>
				<select name="suckerfish_superfish_speed">
				<?php
					$suckerfish_superfish_speed = get_option('suckerfish_superfish_speed');
					switch ($suckerfish_superfish_speed){
						case "slow":echo '<option>slow</option><option>normal</option><option>fast</option><option>instant</option>';break;
						case "normal":echo '<option>normal</option><option>slow</option><option>fast</option><option>instant</option>';break;
						case "fast":echo '<option>fast</option><option>slow</option><option>normal</option><option>instant</option>';break;
						case "instant":echo '<option>instant</option><option>normal</option><option>slow</option><option>fast</option>';break;
						case "":echo '<option>instant</option><option>normal</option><option>slow</option><option>fast</option>';break;
						}
				?>
				</select>
			</th>
			<td class='name'><?php _e('Speed of fade-in effect','pixopoint_mln'); ?></td>
			<td class='desc'>
				<p><?php _e('This option enhances the behaviour of the dropdown by creating an animated fade-in effect. The script which powers this part of the plugin is called <a href="http://users.tpg.com.au/j_birch/plugins/superfish/">\'Superfish\'</a> and was created by Joel Birch. This option utilizes <a href="http://jquery.com/">jQuery</a>.','pixopoint_mln'); ?></p>
			</td>
		</tr>
		<tr class='inactive'>
			<th scope='row' class='check-column'>
				<input style="width:60px" name="suckerfish_superfish_delaymouseover" type="text" value="<?php echo get_option('suckerfish_superfish_delaymouseover'); ?>" />
			</th>
			<td class='name'><?php _e('Mouseover delay (milliseconds)','pixopoint_mln'); ?></td>
			<td class='desc'>
				<p><?php _e('This option adds a delay time before the dropdown/flyout appears. This option is controlled by the <a href="http://users.tpg.com.au/j_birch/plugins/superfish/">\'Superfish plugin\'</a> for jQuery.','pixopoint_mln'); ?></p>
			</td>
		</tr>
		<tr class='inactive'>
			<th scope='row' class='check-column'>
				<input style="width:60px" name="suckerfish_delay" type="text" value="<?php echo get_option('suckerfish_delay'); ?>" />
			</th>
			<td class='name'><?php _e('Hide delay time (milliseconds)','pixopoint_mln'); ?></td>
			<td class='desc'>
				<p><?php _e('This option adds a delay before the dropdown disappears. This option is particularly suitable for small menus where users may accidentally hover off of the menu. The script is powered by the <a href="http://users.tpg.com.au/j_birch/plugins/superfish/">\'Superfish plugin\'</a> for <a href="http://jquery.com/">jQuery</a>','pixopoint_mln'); ?></p>
			</td>
		</tr>
		<tr class='inactive'>
			<th scope='row' class='check-column'>
				<select name="suckerfish_superfish_sensitivity">
				<?php
					$suckerfish_superfish_sensitivity = get_option('suckerfish_superfish_sensitivity');
					switch ($suckerfish_superfish_sensitivity){
						case "high":echo '<option>high</option><option>average</option><option>low</option>';break;
						case "average":echo '<option>average</option><option>high</option><option>low</option>';break;
						case "low":echo '<option>low</option><option>high</option><option>average</option>';break;
						case "":echo '<option>high</option><option>average</option><option>low</option>';break;
						}
				?>
				</select>
			</th>
			<td class='name'><?php _e('Sensitivity','pixopoint_mln'); ?></td>
			<td class='desc'>
				<p><?php _e('When this option is enabled, the menu will attempt to determine the user\'s intent. On low sensitivity, instead of immediately displaying the dropdown/flyout menu on mouseover, the menu will wait until the user\'s mouse slows down before displaying it.','pixopoint_mln'); ?></p>
			</td>
		</tr>
		<tr class='inactive'>
			<th scope='row' class='check-column'>
				<?php
					if (get_option('suckerfish_keyboard') == 'on') {echo '<input type="checkbox" name="suckerfish_keyboard" checked="yes" />';}
					else {echo '<input type="checkbox" name="suckerfish_keyboard" />';}
				?>
			</th>
			<td class='name'><?php _e('Enable keyboard accessible menu?','pixopoint_mln'); ?></td>
			<td class='desc'>
				<p><em><?php _e('This option may not work correctly as it contains bugs.','pixopoint_mln'); ?></em></p>
				<p><?php _e('This option enables users to access your menu via the tab key on their keyboard rather than the mouse. Thanks to <a href="http://www.transientmonkey.com/">malcalevak</a> for writing the script. This option utilizes <a href="http://jquery.com/">jQuery</a>.','pixopoint_mln'); ?></p>
			</td>
		</tr>
<!--	<tr class='inactive'>
		<th scope='row' class='check-column'>
			<?php
				if (get_option('suckerfish_superfish') == 'on') {echo '<input type="checkbox" name="suckerfish_superfish" checked="yes" />';}
				else {echo '<input type="checkbox" name="suckerfish_superfish" />';}
			?>
		</th>
		<td class='name'>Enable animations?</td>
		<td class='desc' rowspan="2">
			<p>
				This option enhances the behaviour of the dropdown by creating an animated fade-in effect. Each page download will be ~32 kB
				larger when this option is activated. The menu will still be accessible before the script
				has fully loaded. The script which powers this part of the plugin is called
				<a href="http://users.tpg.com.au/j_birch/plugins/superfish/">'Superfish'</a> and was created by Joel Birch. This option may not
				be compatible with some plugins, particularly those which are incompatible with the jQuery javascript framework.
			</p>
		</td>
	</tr>-->
		<tr class='inactive'>
			<th scope='row' class='check-column'>
					<?php
					if (get_option('suckerfish_superfish_arrows') == 'on') {echo '<input type="checkbox" name="suckerfish_superfish_arrows" checked="yes" />';}
					else {echo '<input type="checkbox" name="suckerfish_superfish_arrows" />';}
				?>
			</th>
			<td class='name'><?php _e('Enable arrow mark-up?','pixopoint_mln'); ?></td>
			<td class='desc'>
				<p><?php _e('This option adds a small arrow to any menu option which contains children. Thanks to <a href="http://transientmonkey.com/">malcalevak</a> for help with implementing this feature. This option utilizes <a href="http://jquery.com/">jQuery</a>.','pixopoint_mln'); ?></p>
			</td>
		</tr>
<?php /********************* REMOVED BECAUSE IT SHOULD BE DONE WITH CSS, NOT JAVASCRIPT. PLUS IT ISN'T COMPATIBLE WITH THE CSS GENERATOR ANYWAY
	<tr class='inactive'>
		<th scope='row' class='check-column'>
			<?php
				if (get_option('suckerfish_superfish_shadows') == 'on') {echo '<input type="checkbox" name="suckerfish_superfish_shadows" checked="yes" />';}
				else {echo '<input type="checkbox" name="suckerfish_superfish_shadows" />';}
			?>
		</th>
		<td class='name'>Enable drop shadows?</td>
		<td class='desc'>
			<p>
				<em>This option is under development and may not work correctly.</em>
			</p>
			<p>
				This option adds shadows to the dropdown/flyout menus. Thanks to <a href="http://transientmonkey.com/">malcalevak</a>
				for help with implementing this feature. This option utilizes <a href="http://jquery.com/">jQuery</a>.
			</p>
		</td>
	</tr>
*/ ?>
		<tr class="<?php if (!function_exists('pixopoint_secondmenu')) {echo 'in';} ?>active">
			<th scope='row' class='check-column'>
				<?php
					// Only added if second menu isn't already specified in theme
					if (!function_exists('pixopoint_secondmenu')) {
						if (get_option('suckerfish_secondmenu') == 'on') {echo '<input type="checkbox" name="suckerfish_secondmenu" checked="yes" />';}
						else {echo '<input type="checkbox" name="suckerfish_secondmenu" />';}
					}
					else {echo '<label style="width:15px">&nbsp;&nbsp;X</label>';}
				?>
			</th>
			<td class='name'><?php _e('Add a second menu?','pixopoint_mln'); ?></td>
			<td class='desc'>
				<?php
				// Only added if second menu isn't already specified in theme
				if (function_exists('pixopoint_secondmenu')) {?>
				<p><strong><?php _e('Note: You can not turn this option off as your theme has been indicated that it has a second menu.','pixopoint_mln'); ?></strong></p>
				<?php } ?>
				<p><?php _e('You may add a second menu to your site. This is particularly common with magazine style layouts. For a second menu, add the following code to your theme <code>&lt;?php if (function_exists(\'pixopoint_menu\')) {pixopoint_menu(2);} ?&gt;</code>. The <a href="http://pixopoint.com/suckerfish_css/">PixoPoint CSS generator</a> does not currently support a second menu by default, but if you do a search and replace (in a text editor) for <code>suckerfishnav</code> to <code>suckerfishnav_2</code> in the CSS you will be able to adapt the standard CSS for the second menu.','pixopoint_mln'); ?></p>
			</td>
		</tr>
		<tr class="inactive">
			<th scope='row' class='check-column'>
				<?php
					if (get_option('suckerfish_titletags') == 'on') {echo '<input type="checkbox" name="suckerfish_titletags" checked="yes" />';}
					else {echo '<input type="checkbox" name="suckerfish_titletags" />';}
				?>
			</th>
			<td class='name'><?php _e('Remove title attribute?','pixopoint_mln'); ?></td>
			<td class='desc'>
				<p><?php _e('This removes the title attributes from the links in the menu. The title attributes display in most browsers as a small tool tip on hover over the links.','pixopoint_mln'); ?></p>
			</td>
		</tr>
		<tr class="inactive">
			<th scope='row' class='check-column'>
				<?php
					if (get_option('suckerfish_maintenance') == 'on') {echo '<input type="checkbox" name="suckerfish_maintenance" checked="yes" />';}
					else {echo '<input type="checkbox" name="suckerfish_maintenance" />';}
				?>
			</th>
			<td class='name'><?php _e('Maintenance mode?','pixopoint_mln'); ?></td>
			<td class='desc'>
				<p><?php _e('When in maintenance mode, the plugin can only be seen when the user adds ?mln=on to the site URL (ie: <a href="http://pixopoint.com/?mln=on">http://pixopoint.com/?mln=on</a>). This is useful for when testing the menu. If the menu does not look correct on your site, you may place the menu into maintenance mode so that your regular site visitors can not see it.','pixopoint_mln'); ?></p>
			</td>
		</tr>
		<tr class='inactive'>
			<th scope='row' class='check-column'>
				<select name="suckerfish_displaycss">
				<?php
					$suckerfish_displaycss = get_option('suckerfish_displaycss');
					switch ($suckerfish_displaycss){
						case "Inline":echo '<option>Inline</option><option>Disable</option><option>Normal</option>';break;
						case "Disable":echo '<option>Disable</option><option>Inline</option><option>Normal</option>';break;
						case "Normal":echo '<option>Normal</option><option>Inline</option><option>Disable</option>';break;
						case "":echo '<option>Normal</option><option>Inline</option><option>Disable</option>';break;
						}
				?>
				</select>
			</th>
			<td class='name'><?php _e('Style sheet','pixopoint_mln'); ?></td>
			<td class='desc'>
				<p><?php _e('The plugin includes it\'s own built in stylesheet. However many site owners wish to use their themes built in stylesheet (good idea if you want to reduce the HTML in your page) or wish to specify their CSS inline between their head tags (not recommended).','pixopoint_mln'); ?></p>
			</td>
		</tr>
	</tbody>
</table>

</div>
</div>


<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="suckerfish_css, suckerfish_superfish, suckerfish_superfish_speed, suckerfish_superfish_time, suckerfish_superfish_timeout, suckerfish_menuitem1, suckerfish_menuitem2, suckerfish_menuitem3, suckerfish_menuitem4, suckerfish_menuitem5, suckerfish_menuitem6, suckerfish_menuitem7, suckerfish_menuitem8, suckerfish_menuitem9, suckerfish_menuitem10, suckerfish_pagestitle, suckerfish_keyboard, suckerfish_excludepages, suckerfish_excludecategories, suckerfish_hometitle, suckerfish_pagestitle, suckerfish_categoriestitle, suckerfish_archivestitle, suckerfish_blogrolltitle, suckerfish_recentcommentstitle, suckerfish_recentpoststitle, suckerfish_disablecss, suckerfish_custommenu, suckerfish_custommenu2, suckerfish_custommenu3, suckerfish_custommenu4, suckerfish_inlinecss, suckerfish_includeexcludepages, suckerfish_2_css, suckerfish_2_menuitem1, suckerfish_2_menuitem2, suckerfish_2_menuitem3, suckerfish_2_menuitem4, suckerfish_2_menuitem5, suckerfish_2_menuitem6, suckerfish_2_menuitem7, suckerfish_2_menuitem8, suckerfish_2_menuitem9, suckerfish_2_menuitem10, suckerfish_generator, suckerfish_delay, suckerfish_superfish_shadows, suckerfish_superfish_arrows, suckerfish_showdelay, suckerfish_displaycss, suckerfish_secondmenu, osort_order, suckerfish_superfish_delaymouseover,	suckerfish_superfish_hoverintent, suckerfish_superfish_sensitivity, suckerfish_maintenance, suckerfish_categoryorder, suckerfish_includeexcludecategories, suckerfish_homeurl, suckerfish_pagesurl, suckerfish_categoriesurl, suckerfish_archivesurl, suckerfish_blogrollurl, suckerfish_recentcommentsurl, suckerfish_recentpostsurl, suckerfish_depthcategories, suckerfish_depthpages, suckerfish_categorycount, suckerfish_categoryshowempty, suckerfish_titletags, suckerfish_recentpostsnumber, suckerfish_recentcommentsnumber" />
<div style="clear:both;padding-top:20px;"></div>
	<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options','pixopoint_mln') ?>" /></p>
<div style="clear:both;padding-top:20px;"></div>
<input type="hidden" name="option_page" value="multilevelnavigationops" />

<?php wp_nonce_field('multilevelnavigationops-options'); ?>
</form>
</div>
<?php

}








// Adds content to HEAD section for admin pages
function suckerfish_adminhead() {
	echo '<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/multi-level-navigation-plugin/admin.css" type="text/css" media="screen" />
';
	wp_enqueue_script('tabber');
	//wp_enqueue_script('sortable_lists');
	//wp_enqueue_script('animatedcollapse');
}






/**
 * Whitelist the options for options.php's checks
 */
function pmp_whitelist($whitelist) {
	$whitelist['multilevelnavigationops'] = array(
		'suckerfish_css',
		'suckerfish_superfish',
		'suckerfish_superfish_speed',
		'suckerfish_superfish_time',
		'suckerfish_superfish_timeout',
		'suckerfish_menuitem1',
		'suckerfish_menuitem2',
		'suckerfish_menuitem3',
		'suckerfish_menuitem4',
		'suckerfish_menuitem5',
		'suckerfish_menuitem6',
		'suckerfish_menuitem7',
		'suckerfish_menuitem8',
		'suckerfish_menuitem9',
		'suckerfish_menuitem10',
		'suckerfish_pagestitle',
		'suckerfish_keyboard',
		'suckerfish_excludepages',
		'suckerfish_excludecategories',
		'suckerfish_hometitle',
		'suckerfish_pagestitle',
		'suckerfish_categoriestitle',
		'suckerfish_archivestitle',
		'suckerfish_blogrolltitle',
		'suckerfish_recentcommentstitle',
		'suckerfish_recentpoststitle',
		'suckerfish_disablecss',
		'suckerfish_custommenu',
		'suckerfish_custommenu2',
		'suckerfish_custommenu3',
		'suckerfish_custommenu4',
		'suckerfish_inlinecss',
		'suckerfish_includeexcludepages',
		'suckerfish_2_css',
		'suckerfish_2_menuitem1',
		'suckerfish_2_menuitem2',
		'suckerfish_2_menuitem3',
		'suckerfish_2_menuitem4',
		'suckerfish_2_menuitem5',
		'suckerfish_2_menuitem6',
		'suckerfish_2_menuitem7',
		'suckerfish_2_menuitem8',
		'suckerfish_2_menuitem9',
		'suckerfish_2_menuitem10',
		'suckerfish_generator',
		'suckerfish_delay',
		'suckerfish_superfish_shadows',
		'suckerfish_superfish_arrows',
		'suckerfish_showdelay',
		'suckerfish_displaycss',
		'suckerfish_secondmenu',
		'osort_order',
		'suckerfish_superfish_delaymouseover',
		'suckerfish_superfish_hoverintent',
		'suckerfish_superfish_sensitivity',
		'suckerfish_maintenance',
		'suckerfish_categoryorder',
		'suckerfish_includeexcludecategories',
		'suckerfish_homeurl',
		'suckerfish_pagesurl',
		'suckerfish_categoriesurl',
		'suckerfish_archivesurl',
		'suckerfish_blogrollurl',
		'suckerfish_recentcommentsurl',
		'suckerfish_recentpostsurl',
		'suckerfish_depthcategories',
		'suckerfish_depthpages',
		'suckerfish_categorycount',
		'suckerfish_categoryshowempty',
		'suckerfish_titletags',
		'suckerfish_recentpostsnumber',
		'suckerfish_recentcommentsnumber',
	);
	return $whitelist;
}
add_filter('whitelist_options', 'pmp_whitelist');
?>
