<?php
/**
 * This file contains the EC_Day class.
 *
 * @package 			WP-Events-Calendar
 * 
 * @autbor 			Luke Howell <luke@wp-eventscalendar.com>
 * @author 			Brad Bodine <brad@wp-eventscalendar.com>
 * @author 			René MALKA <heirem@wp-eventscalendar.com>
 * @author 			Louis Lapointe <laplix@wp-eventscalendar.com>
 *
 * @copyright 			Copyright (c) 2007-2009 Luke Howell
 * @copyright 			Copyright (c) 2007-2009 Brad Bodine
 * @copyright 			Copyright (c) 2008-2009 René Malka
 * @copyright 			Copyright (c) 2009      Louis Lapointe
 *
 * @license 			GPLv3 {@link http://www.gnu.org/licenses/gpl}
 * @filesource
 */
/*
---------------------------------------------------------------------
This file is part of the WordPress Events Calendar plugin project.

For questions, help, comments, discussion, etc., please join our
forum at {@link http://www.wp-eventscalendar.com/forum}. You can
also go to Luke's ({@link http://www.lukehowelll.com}) and
Heirem's ({@link http://heirem.fr}) blogs.

You can also submit bugs or feature requests at this address:
http://tracker.wp-eventscalendar.com/my_view_page.php.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
---------------------------------------------------------------------
*/
if(!class_exists('EC_Day')):

/** Load the WPEC database class. */
require_once('ec_db.class.php');

/**
 * Display the events of the day in a thickbox.
 *
 * @package WP-Events-Calendar
 * @since   6.0
 */
class EC_Day {

	/**
	 * Holds the $wpdb global object
	 * @var object
	 * @access private
	 */
	var $db;

	/**
	 * Constructor.
	 */
	function EC_Day() {
		// 6.5.2.2 commenting this. i don't understand why it's here.
		// it loads functions unless they have been overriden by plugins.
		// requiring it stops other plugins to overrride these functions.
		// --laplix
		//require_once(ABSWPINCLUDE.'/pluggable.php');
		$this->db = new EC_DB();
	}

	/**
	 * Display the day events for the date $d.
	 * @param string $d 			date for which to shw events
	 */
	function display($d) {
?>
    <link type="text/css" rel="stylesheet" href="<?php bloginfo('siteurl');?>/wp-includes/js/thickbox/thickbox.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo EVENTSCALENDARCSSURL;?>/events-calendar.css" />
<?php
    $options = get_option('optionsEventsCalendar');
    $events = $this->db->getDaysEvents($d);
    list($ec_year, $ec_month, $ec_day) = explode("-", $d);
?>
      <div id="EC_daysEvents">
<?php
    foreach($events as $event) {
      if(($event->accessLevel == 'public') || (current_user_can($event->accessLevel))) {
        $title = stripslashes($event->eventTitle);
        $description = preg_replace('#\r?\n#', '<br />', $event->eventDescription);
        $description = stripslashes($description);
        $location = stripslashes($event->eventLocation);
        $PostID = isset($event->postID) ? $event->postID : '';
        $linkout = isset($event->eventLinkout) ? $event->eventLinkout : '';
        list($ec_startyear, $ec_startmonth, $ec_startday) = explode("-", $event->eventStartDate);
        if(!is_null($event->eventStartTime) && !empty($event->eventStartTime)) {
          list($ec_starthour, $ec_startminute, $ec_startsecond) = explode(":", $event->eventStartTime);
          $startTime = date($options['timeFormatWidget'], mktime($ec_starthour, $ec_startminute, $ec_startsecond, $ec_startmonth, $ec_startday, $ec_startyear));
        } else $startTime = null;
        $startDate = date($options['dateFormatWidget'], mktime($ec_starthour, $ec_startminute, $ec_startsecond, $ec_startmonth, $ec_startday, $ec_startyear));
        list($ec_endyear, $ec_endmonth, $ec_endday) = explode("-", $event->eventEndDate);
        if($event->eventEndTime != null && !empty($event->eventEndTime)) {
          list($ec_endhour, $ec_endminute, $ec_endsecond) = explode(":", $event->eventEndTime);
          $endTime = date($options['timeFormatWidget'], mktime($ec_endhour, $ec_endminute, $ec_endsecond, $ec_endmonth, $ec_endday, $ec_endyear));
        } else $endTime = null;
        $endDate = date($options['dateFormatWidget'], mktime($ec_endhour, $ec_endminute, $ec_endsecond, $ec_endmonth, $ec_endday, $ec_endyear));
        // Title
        $output = '<p>'."\n".'<div for="EC_title" class="EC_title"><strong>&nbsp;'.$title.'</strong></div>'."\n";
        // If Location
        if(!empty($location) && !is_null($location)) $output .= '<div for="EC_location" class="EC_location"><strong>&nbsp;'._c('Location','events-calendar').':</strong> '.$location.'</div>'."\n";
        // start time
        if(!empty($startTime) && !is_null($startTime))
          $output .='<div for="EC_time" class="EC_time"><strong>&nbsp;'.$startTime.'</strong>&nbsp;';
        // end Time
        if((!empty($endTime) && !empty($startTime)) || (!is_null($endTime) && !is_null($startTime)))
          $output .= _c('to','events-calendar').'<strong>&nbsp;'.$endTime.'</strong>';
        // Description
        $output .= '</div><div for="EC_description" class="EC_description">&nbsp;'.$description.'</div>'."\n";
        // If Star Date <> End Date
        if($event->eventStartDate != $event->eventEndDate )
          $output .= '<div for="EC_date" class="EC_date"><strong>'._c('Date range','events-calendar').'</strong></div>'._c('Since','events-calendar').'&nbsp;'.$startDate.'&nbsp;'._c('until','events-calendar').'&nbsp;'.$endDate."\n";
        // Link outside the site if the link exist
        if ($linkout != '')
          $output .= '<div for="EC_linkout" class="EC_linkout_and_postid"><strong>'._c('Link out','events-calendar').'</strong></div><a href="'.$linkout.'" target="_parent">'.substr($linkout,0,37).'</a>';
        // Link to a post when exist
        if ($PostID != '') {
          $IDtmp = get_post($PostID);
          $ptitle = $IDtmp->post_title;
          $output .= '<div for="EC_postid" class="EC_linkout_and_postid"><strong>'._c('Post','events-calendar').' ('.$PostID.')</strong></div><a href="'.get_permalink($PostID).'" target="_parent"/>'.stripslashes($ptitle).'</a><br />';
        }
        echo $output;
      } // if
    } // for each
  } // function display
}
endif;
?>
