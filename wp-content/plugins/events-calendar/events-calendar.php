<?php
/*
Plugin Name: WP Events Calendar
Plugin URI: http://www.wp-eventscalendar.com
Description: There are options under the widget options to specify the view of the calendar in the sidebar. The widget can be a list for upcoming events or a calendar. If you do not have a widget ready theme then you can place '&lt;?php SidebarEventsCalendar();?&gt;'?, or '&lt;?php SidebarEventsList();?&gt;' for an event list, in the sidebar.php file of your theme. If you want to display a large calendar in a post or a page, simply place "[[EventsCalendarLarge]]" in the html of the post or page. Make sure to leave off the quotes.
Version: 6.6-beta
Author: Luke Howell, Brad Bodine, Rene Malka, Louis Lapointe
Author URI: http://www.lukehowell.com
*/
/**
 * This file contains WP Events Calendar plugin.
 *
 * This is the main WPEC file.
 * @internal			Complete the description.
 *
 * @package			WP-Events-Calendar
 * @since			1.0
 * 
 * @autbor			Luke Howell <luke@wp-eventscalendar.com>
 * @author			Brad Bodine <brad@wp-eventscalendar.com>
 * @author			René MALKA <heirem@wp-eventscalendar.com>
 * @author			Louis Lapointe <laplix@wp-eventscalendar.com>
 *
 * @copyright			Copyright (c) 2007-2009 Luke Howell
 * @copyright			Copyright (c) 2007-2009 Brad Bodine
 * @copyright			Copyright (c) 2008-2009 René Malka
 * @copyright			Copyright (c) 2009      Louis Lapointe
 *
 * @license			GPLv3 {@link http://www.gnu.org/licenses/gpl}
 * @filesource
 */
/*
--------------------------------------------------------------------------
$Id$
--------------------------------------------------------------------------
This file is part of the WordPress Events Calendar plugin project.

For questions, help, comments, discussion, etc., please join our
forum at {@link http://www.wp-eventscalendar.com/forum}. You can
also go to Luke's ({@link http://www.lukehowelll.com}) and
Heirem's ({@link http://heirem.fr}) blogs.

WP Events Calendar is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.   See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
*/

/** Events-Calendar version */
define('EVENTSCALENDARVERS', 'Version: 6.6');

/** using native directory separator for paths */
if (!defined('DS'))
	define ('DS', DIRECTORY_SEPARATOR);

// Paths
define('EVENTSCALENDARPATH', ABSPATH.'wp-content'.DS.'plugins'.DS.'events-calendar');
define('EVENTSCALENDARCLASSPATH', EVENTSCALENDARPATH);
define('ABSWPINCLUDE', ABSPATH.WPINC);

// URLS
define('EVENTSCALENDARURL', get_option('siteurl').'/wp-content/plugins/events-calendar');
define('EVENTSCALENDARJSURL', EVENTSCALENDARURL.'/js');
define('EVENTSCALENDARCSSURL', EVENTSCALENDARURL.'/css');
define('EVENTSCALENDARIMAGESURL', EVENTSCALENDARURL.'/images');


require_once(EVENTSCALENDARCLASSPATH.DS.'ec_day.class.php');
require_once(EVENTSCALENDARCLASSPATH.DS.'ec_calendar.class.php');
require_once(EVENTSCALENDARCLASSPATH.DS.'ec_db.class.php');
require_once(EVENTSCALENDARCLASSPATH.DS.'ec_widget.class.php');
require_once(EVENTSCALENDARCLASSPATH.DS.'ec_management.class.php');

/** Init Localisation */
load_default_textdomain();
require_once(ABSWPINCLUDE.'/locale.php');
load_plugin_textdomain('events-calendar',PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/lang');

/** DatePicker localisation */
$locale = get_locale();
if (in_array($locale, array('pt_BR','zh_TW','zh_CN'))) {
	$loc_lang=str_replace('_','-',$locale);
}
else {
	$loc_lang = explode("_",$locale); $loc_lang = $loc_lang[0];
	if (!in_array($loc_lang, array('ar','bg','ca','cs','da','de','es','fi','fr','he','hu','hy','id','is','it','ja','ko','lt','lv','nl','no','pl','ro','ru','sk','sv','th','tr','uk')))
		
	$loc_lang='en';
}


if(isset($_GET['EC_view']) && $_GET['EC_view'] == 'day') {
	EC_send_headers();
	$EC_date = date('Y-m-d', mktime(0, 0, 0, $_GET['EC_month'], $_GET['EC_day'], $_GET['EC_year']));
	$day = new EC_Day();
	$day->display($EC_date);
	exit();
}

// called from the widget through ajax. we need to send a header to
// make sure we respect the blog charset.
if(isset($_GET['EC_action']) && $_GET['EC_action'] == 'switchMonth') {
	EC_send_headers();
	$calendar = new EC_Calendar();
	$calendar->displayWidget($_GET['EC_year'], $_GET['EC_month']);
	exit();
}

// called from the large clendar through ajax. we need to send a header to
// make sure we respect the blog charset.
if(isset($_GET['EC_action']) && $_GET['EC_action'] == 'switchMonthLarge') {
	EC_send_headers();
	$calendar = new EC_Calendar();
	$calendar->displayLarge($_GET['EC_year'], $_GET['EC_month']);
	exit();
}

if(isset($_GET['EC_action']) && $_GET['EC_action'] == 'ajaxDelete') {
  $db = new EC_DB();
  $db->deleteEvent($_GET['EC_id']);
  exit();
}

/**
 * sends headers needed when ajax i used.
 */
function EC_send_headers() {
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
	header("Pragma: no-cache");                          // HTTP/1.0
	header("Content-Type: text/html; charset=".get_option('blog_charset'));
}

/**
 * Initializes the Events Calendar plugin.
 *
 * The function first check to see if we are in the admin panel.
 * If we're not, it enqueues the jQuery plugins needed by WPEC:
 * bgiframe, dimensions, tooltip and thinkbox.
 * Then it registers the widget and the widget control with WordPress.
 * @uses EC_Widget
 * @uses EC_Management
 */
function EventsCalendarINIT() {
	$inadmin = strstr($svr_uri, 'wp-admin');
	if (!$inadmin) {
		wp_enqueue_script('jquerybgiframe', '/wp-content/plugins/events-calendar/js/jquery.bgiframe.js', array('jquery'), '2.1');
		wp_enqueue_script('jquerydimensions', '/wp-content/plugins/events-calendar/js/jquery.dimensions.js', array('jquery'), '1.0b2');
		wp_enqueue_script('jquerytooltip', '/wp-content/plugins/events-calendar/js/jquery.tooltip.min.js', array('jquery'), '1.3');
		wp_enqueue_script('thickbox');
	}

	// registers the widget and the widget control.
	// FIXME I think these should only be registered as needed, e.g.
	// widget when not in admin and widget control when in admin.
	// that would relieve WP burden. Not really sure about that though.
	// --laplix
	if ((!$inadmin) OR (($inadmin) && ((strstr($svr_uri, 'widget'))))) {
		$widget = new EC_Widget();
		$management = new EC_Management();

		if(!function_exists('register_sidebar_widget'))
			return;

		register_sidebar_widget(__('Events Calendar','events-calendar'), array(&$widget, 'display'));
		register_widget_control(__('Events Calendar','events-calendar'), array(&$management, 'widgetControl'));
	}
}

/**
 * Initializes the Events Calendar admin panel.
 * The function creates a new menu and enqueues a few jquery plugins:
 * bgiframe, dimensions, tooltip, ui.core, ui.datepicker and its language file,
 * clockpicker,
 *
 * @uses EC_Management
 * FIXME Need to check if dimensions is still needed. I seem to remember that
 * 		it was incorprated into jQuery core a while ago...
 * 		Ok just went and checked. The plugin doesn't seem to be on brandon
 * 		page anymore (http://brandonaaron.net/code) but the dimension pluin
 * 		page still sends you there for docs. That would support the possibility
 * 		of its integration into core. I'll keep it here for a while but that
 * 		needs to be investigated. No sense loading an unneeded script!
 */
function EventsCalendarManagementINIT() {
	$options = get_option('optionsEventsCalendar');
	$EC_userLevel = isset($options['accessLevel']) && !empty($options['accessLevel']) ? $options['accessLevel'] : 'level_10';
	$management = new EC_Management();
	add_menu_page(__('Events Calendar','events-calendar'), __('Events Calendar','events-calendar'), $EC_userLevel, 'events-calendar', array(&$management, 'display'));
	if(isset($_GET['page']) && strstr($_GET['page'], 'events-calendar')) {
		global $loc_lang;
    	wp_enqueue_script('jquerybgiframe', '/wp-content/plugins/events-calendar/js/jquery.bgiframe.js', array('jquery'), '2.1');
    	wp_enqueue_script('jquerydimensions', '/wp-content/plugins/events-calendar/js/jquery.dimensions.js', array('jquery'), '1.0b2');
    	wp_enqueue_script('jquerytooltip', '/wp-content/plugins/events-calendar/js/jquery.tooltip.min.js', array('jquery'), '1.3');
    	wp_enqueue_script('jqueryuicore', '/wp-content/plugins/events-calendar/js/ui.core.min.js', array('jquery'), '1.5.2');
    	wp_enqueue_script('jqueryuidatepicker', '/wp-content/plugins/events-calendar/js/ui.datepicker.js', array('jquery'), '1.5.2');
		
		if ($loc_lang != 'en')
			wp_enqueue_script('jqueryuidatepickerlang', '/wp-content/plugins/events-calendar/js/i18n/ui.datepicker-'.$loc_lang.'.js', array('jquery'), '1.5.2');
		
		wp_enqueue_script('jqueryclockpicker', '/wp-content/plugins/events-calendar/js/jquery.clockpick.1.2.6.js', array('jquery'), '1.2.6');
//		add_submenu_page('events-calendar', __('Events Calendar','events-calendar'), __('Calendar','events-calendar'), $EC_userLevel, 'events-calendar', array(&$management, 'calendarOptions'));

		add_submenu_page('events-calendar', __('Events Calendar','events-calendar'), __('Calendar','events-calendar'), $EC_userLevel, 'events-calendar', '');

		add_submenu_page('events-calendar', __('Events Calendar','events-calendar'), __('Add Event','events-calendar'), $EC_userLevel, '#addEventform', '');
		add_submenu_page('events-calendar', __('Events Calendar Options','events-calendar'), __('Options','events-calendar'), $EC_userLevel, 'events-calendar-options', array(&$management, 'calendarOptions'));
	}
}

/**
 * Loads the stylesheets and the jQuery library.
 * The function generates a call to jQuery,noConflict() and passes it the jQuery
 * Extreme Flag that can be set/unset in the admin panel.
 * The jQuery object is stored in ecd.jq which will then be used by the plugin.
 */
function EventsCalendarHeaderScript() {
?>
<!-- Start Of Script Generated By Events-Calendar [Luke Howell | www.lukehowell.com] and [R. MALKA | www.heirem.fr] -->
<!-- More information at http://wp-eventscalendar.com. -->
<link type="text/css" rel="stylesheet" href="<?php bloginfo('wpurl');?>/wp-includes/js/thickbox/thickbox.css" />
<link type="text/css" rel="stylesheet" href="<?php echo EVENTSCALENDARCSSURL;?>/events-calendar.css" />
<?php
	require_once(ABSPATH . 'wp-admin/includes/admin.php');
   // jQuery DOM extreme protection management
	$options = get_option('optionsEventsCalendar');
   echo ' <script type="text/javascript">',"\n\t";
   echo '// <![CDATA[',"\n\t";
   echo 'var ecd = {};',"\n\t";
   echo 'ecd.jq = jQuery.noConflict('.$options['jqueryextremstatus'].');',"\n\t";
   echo '//]]>',"\n";
   echo ' </script>',"\n";
	echo "<!-- End Of Script Generated By Events-Calendar - ".EVENTSCALENDARVERS." -->\n";

}

/**
 * Loads the needed stylesheets for the admin panel.
 */
function EventsCalendarAdminHeaderScript() {
	if(isset($_GET['page']) && $_GET['page'] == 'events-calendar') {
?>
<link type="text/css" rel="stylesheet" href="<?php echo EVENTSCALENDARCSSURL;?>/events-calendar-management.css" />
<link type="text/css" rel="stylesheet" href="<?php echo EVENTSCALENDARCSSURL;?>/ui.datepicker.css" />
<link type="text/css" rel="stylesheet" href="<?php echo EVENTSCALENDARCSSURL;?>/clockpick.css" />
<?php
	}
}

/**
 * Installs or upgrade the plugin on activation.
 * This is why it is important to de-activate the plugin before
 * upgrading it.
 * @uses EC_DB
 */
function EventsCalendarActivated() {
  $db = new EC_DB();
  $db->createTable();
  $db->initOptions();
}

/**
 * Either returns needle or the data before needle.
 *
 * This is used by the filterEventsCalendarLarge() function to get
 * the content of a page before and after the short tag [[EventsCalendarLarge]]
 *
 * @param string $haystack      page or post where the shrt tag lives
 * @param string $needle        the short tag
 * @param bool   $before_needle do we want the data before the short tag?
 * @return string
 */
function ec_strstr($haystack, $needle, $before_needle=FALSE) {
	if (FALSE === ($pos = strpos($haystack, $needle)))
		return FALSE;

	if ($before_needle)
		return substr($haystack, 0, $pos);
	else
		return substr($haystack, $pos + strlen($needle));
}

/**
 * Displays the large calendar in place of the [[EventsCalendarLarge]] short tag.
 *
 * @param string $content 		the content of the page
 * @return string             the content after the tag
 * @uses ec_strstr()
 * @uses EC_Calendar
 */
function filterEventsCalendarLarge($content) {
	if(preg_match("[EventsCalendarLarge]",$content)) {
		$calendar = new EC_Calendar();
		$ec_match_filter = '[[EventsCalendarLarge]]';
		$before_large_calendar = ec_strstr($content, $ec_match_filter, TRUE);
		$content = ec_strstr($content, $ec_match_filter, FALSE);
		$calendar->displayLarge(date('Y'), date('m'), $before_large_calendar);
	}
	return $content;
}

/**
 * Will display the small calendar in sidebar.
 *
 * This can be used by themes that are not widget ready.
 */
function SidebarEventsCalendar() {
	$calendar = new EC_Calendar();
	$calendar->displayWidget(date('Y'), date('m'));
}

/**
 * Will display an events list in sidebar.
 *
 * This can be used by themes that are not widget ready.
 *
 * @param int $num 		number of events to display. defaults to 5.
 */
function SidebarEventsList($num = 5) {
	$calendar= new EC_Calendar();
	$calendar->displayEventList($num);
}

add_action('activate_events-calendar/events-calendar.php', 'EventsCalendarActivated');
add_action('plugins_loaded', 'EventsCalendarINIT');
add_action('admin_menu', 'EventsCalendarManagementINIT');
add_action('wp_head', 'EventsCalendarHeaderScript');
add_action('admin_head', 'EventsCalendarAdminHeaderScript');
add_filter('the_content', 'filterEventsCalendarLarge');
?>
