<?php
/**
 * This file contains the EC_Calendar class.
 *
 * @package 			WP-Events-Calendar
 * 
 * @autbor			Luke Howell <luke@wp-eventscalendar.com>
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
if(!class_exists("EC_Calendar")) :
require_once(EVENTSCALENDARCLASSPATH . '/ec_db.class.php');

/**
 * Displays the events list and the calendars
 *
 * @package WP-Events-Calendar
 * @since   6.0
 */
class EC_Calendar {

	/**
	 * Holds the WP_Locale object.
	 * @var object
	 * @access private
	 */
	var $locale;

	/**
	 * Constructor.
	 */
	function __construct() {
		$this->locale = new WP_Locale;
	}

	/**
	 * Prehistoric constructor.
	 * This will live until we decide we don't support PHP4 anymore.
	 * That should be soon!!
	 *
	 * @deprecated 	Upgrade to PHP5
	 */
	function EC_Calendar() {
		$this->__construct();
	}

	/**
	 * Returns a DB_CHARSET aware substring.
	 *
	 * In some languages, day of the week displays poorly on two or three characters.
	 *
	 * This function is there to correct the problem. If the DB_CHARSET is set
	 * to utf8, it will return $length part of $str, starting at $start. IF the
	 * DB_CHARSET is not uft8, it just return a normal substring of $str.
	 *
	 * @author pepawo
	 * @author heirem
	 *
	 * @param string $str 			string to work on
	 * @param int $start 			offset from where to start
	 * @param int $length 			length to extract
	 * @return string 			substring to return
	 * @access private
    */
	function utf8_substr($str, $start, $length)	{
		if (DB_CHARSET == 'utf8') {
			preg_match_all('/./u', $str, $ar);
			return join("", array_slice($ar[0], $start, $length));
		} else {
			return substr($str, $start, $length);
		}
	}
	
	/**
	 * Displays the Event List Widget.
	 *
	 * @param int $num 			number of events to list
    */
	function displayEventList($num) {
		global $current_user;

		// Localisation
		// not needed here anymore. moved to constructor.
		//load_default_textdomain();
		//require_once(ABSWPINCLUDE.'/locale.php');
		//$wp_locale = new WP_Locale();

		$db = new EC_DB();
		$js = new EC_JS();
		
		$options = get_option('optionsEventsCalendar');
		$format = $options['dateFormatLarge'];
		$day_name_length = $options['daynamelength'];
		$events = $db->getUpcomingEvents($num);
		
		$output = '<ul id="events-calendar-list">';

		foreach($events as $event) {
			if ($event->accessLevel == 'public' || $current_user->has_cap($event->accessLevel)) {
				$splitDate = explode("-", $event->eventStartDate);
				$month = $splitDate[1];
				$day = $splitDate[2];
				$year = $splitDate[0];
				$timeStp = mktime(0, 0, 0, $month, $day, $year);
				$startDate = date("$format", $timeStp );
				$day_names = ucfirst($this->locale->get_weekday(date('w', $timeStp )));
				
				if( $day_name_length)
					$day_names = $day_name_length < 4 ? $this->utf8_substr($day_names,0,$day_name_length) : $day_names;
				
				$PostID = isset($event->postID) ? $event->postID : '';
				
				if ($PostID == '')
					$titlinked = '<strong>'.$day_names.' '.$startDate.'</strong>: ' . $event->eventTitle;
				else
					$titlinked = '<a href="' . get_permalink($PostID) . '">'
								  . '<strong>' . $day_names . ' ' . $startDate . '</strong>' . __(': ', 'events-calendar')
								  . $event->eventTitle . '</a>';

				// don't send T\'itles 
				if (false !== strpos($titlinked, "\'"))
					$titlinked = stripslashes($titlinked);

				//$startDate = $startDate < date("$format") ? date("$format") : $startDate;
				$output .= '<li id="events-calendar-list-' . $event->id . '">' . $titlinked .'</li>' . "\n";
			}
		}

		$output .= "</ul>";
		
		// $output='<ul id="events-calendar-list"></ul>'; // for tests
		if ($output == '<ul id="events-calendar-list"></ul>') {
			echo '<ul><li id="no-events-in-list"><strong>', __('Events are coming soon, stay tuned!','events-calendar'), '</strong></li></ul>' ."\n";
		} else {

			if (false !== strpos($output, "\'"))
				$output = stripslashes($output);

			echo $output . "\n";
			$js->listData($events);
		}
	}
	
	/**
	 * Displays the Widget Calendar.
	 *
	 * The method displays the calendar structure then calls EC_JS::calendarData()
	 * to output the data itself.
	 *
	 * @param int $year		year to display
	 * @param int $month		month to display
	 * @param array $days		unknown. not used
	 * @param int $day_name_length	day name length to display. If equal to zero,
	 *                            	day names won't be shown. If greater than 3,
	 *                            	full name will be shown. Defaults to 2.
	 */
	function displayWidget($year, $month, $days = array(), $day_name_length = 2) {
		// Localisation
		// not needed here anymore. moved to constructor.
		//load_default_textdomain();
		//require_once(ABSWPINCLUDE.'/locale.php');
		//$wp_locale = new WP_Locale();

		// The following two lines are to get the length of day names - Ron
		$options = get_option('optionsEventsCalendar');
		$day_name_length = $options['daynamelength'];

		// Option : Is the CSS adapted for your site ?
		$adaptedCSS = $options['adaptedCSS'];

		// If not we prepare the CSS style for today
		$todayCSS = '';
		if(!$adaptedCSS) {
			$todayCSS = isset($options['todayCSS']) && !empty($options['todayCSS'])
				? $options['todayCSS']
				: 'border:thin solid blue;font-weight: bold;background-color: #a8c3d6;';
		}
		
		$js = new EC_JS();
		$first_day = get_option('start_of_week');
		$first_of_month = gmmktime(0,0,0,$month,1,$year);
		$day_names = array();

		//January 4, 1970 was a Sunday
		for ($n=0, $t = (3 + $first_day) * 86400; $n < 7; $n++, $t += 86400)
			$day_names[$n] = ucfirst($this->locale->get_weekday(gmdate('w', $t)));

		list($month, $year, $month_name, $weekday) = explode(',', gmstrftime('%m, %Y, %B, %w', $first_of_month));

		//adjust for $first_day
		$weekday = ($weekday + 7 - $first_day) % 7;

		$title = ucfirst($this->locale->get_month($month)).'&nbsp;'.$year;

		$calendar = "\n" . '<div id="calendar_wrap">' . "\n" 
				. '<table summary="Event Calendar" id="wp-calendar">' . "\n"
				. '<caption id="calendar-month" class="calendar-month">' . $title . '</caption>' . "\n";

		// if the day names should be shown ($day_name_length > 0)
		if ($day_name_length) {
			//if day_name_length is >3, the full name of the day will be printed
			$calendar .= '<thead><tr>' . "\n";
			
			foreach($day_names as $d)
				$calendar .= '<th abbr="' . $d . '" scope="col" title="' . $d .'">' 
								 . ($day_name_length < 4 ? $this->utf8_substr($d,0,$day_name_length) : $d)
								 . '</th>' . "\n";
			
			$calendar .= '</tr></thead>' . "\n";
		}

		// build the month navigation. the links will be provided by the EC_JS class
		$calendar .= '<tfoot><tr>' . "\n"
				.' <td class="pad" style="text-align:left" colspan="2">&nbsp;'
				. '<span id="EC_previousMonth"></span>'
				. '</td>' . "\n"
				. '<td class="pad" colspan="3" id="EC_loadingPane" style="text-align:center;"></td>' . "\n"
				. '<td class="pad" style="text-align:right;" colspan="2">'
				. '<span id="EC_nextMonth"></span>&nbsp;</td>' . "\n"
				. '</tr></tfoot>' . "\n";
			 
		// time to build the calendar itself
		$calendar .= '<tbody><tr>' ."\n";

		// initial empty days
		// todo this does a colpan. But me think we could just loop through
		// the days and output their cells. would look nicer... just my 2¢.
		if ($weekday > 0)
			$calendar .= '<td colspan="'.$weekday.'" class="padday">&nbsp;</td>' . "\n";
		
		// the calendar
		$today = mktime(0,0,0,date('m'),date('j'),date('Y'));
		for ($day=1, $days_in_month = gmdate('t', $first_of_month); $day <= $days_in_month; $day++, $weekday++) {
			// start a new week
			if($weekday == 7) {
				$weekday = 0;
				$calendar .= "</tr><tr>\n";
			}

			$dayID = '';

			// today
			$theday = mktime(0,0,0,$month,$day,$year);
			if ($theday == $today)
				$dayID = $adaptedCSS ? ' id="today"' : ' id="todayWidget" style="' . $todayCSS . '"';

			// todo not sure we really need the span tag here... byte pollution?
			$calendar .= '<td'.$dayID.'><span id="events-calendar-'.$day.'">'.$day.'</span></td>'."\n";
		}
		
		// remaining empty days
		if ($weekday != 7)
			$calendar .= '<td colspan="' .  (7-$weekday) . '" class="padday">&nbsp;</td>' . "\n";
		
		$calendar .= '</tr></tbody></table>' . "\n";

		// load the Thickbox script
		$start_script  = '<script type="text/javascript">' . "\n";
		$start_script .= '// <![CDATA[' . "\n";
		$start_script .= 'tb_pathToImage ="'.get_option('siteurl').'/wp-includes/js/thickbox/loadingAnimation.gif";'."\n";
		$start_script .= 'tb_closeImage = "'.get_option('siteurl').'/wp-includes/js/thickbox/tb-close.png";'."\n";

		// FIXME
		// This needs to be called immediately after jQuery has been loaded
		// and before it is used anywhere, meaning not just for our plugin.
		// Also, if extremme protection has been selected by the user,
		// jQuery won't even exists!!
		// there are a lot of noConflict calls. 
		//$start_script .= 'jQuery.noConflict();'."\n";

		// and prepare for the javascript onslaught!
		// todo 	I don't think we need ecd here. we're passing jQuery as a parameter
		// 		which means it won't be available outside. 
		// 		So we should use the $ var.
		// todo 	We should get rid of the inline JS. Using a class on the days would
		// 		enable us to use something like
		// 		$(".some_class").each(function(el) { ... });
		// 		and use a external JS file loaded in the head.
		//
		$start_script .= '(function($) {' . "\n";
		$start_script .= "\t" . 'ecd.jq(document).ready(function() {' . "\n";

		// closing the JS.
		$end_script  = "\t" . '});' . "\n";
		$end_script .= '})(jQuery);' . "\n";
		$end_script .= '//]]>' . "\n";
		$end_script .= '</script>' . "\n";

		// output the calendar
		echo $calendar;
		echo '<!-- WPEC script starts here -->'. "\n";
		echo $start_script;
		$js->calendarData($month, $year);
		echo $end_script . "\n";
		echo '<!-- WPEC script ends here. -->'."\n";
		echo '</div>' . "\n";
	}

	/**
	 * Displays the Large Calendar.
	 *
	 * Displays the caledar then calls EC_JS::calendarDataLarge() to output the
	 * data itself.
	 *
	 * @param int $year				year to display
	 * @param int $month				month to display
	 * @param string $before_large_calendar		this is going to be displayed before
	 *						the calendar
	 * @param array $days				unknown. not used
	 * @param int $day_name_length			day name length to display. if equal to zero,
	 *						day names won't be shown. default to 7.
    */
	function displayLarge($year, $month, $before_large_calendar = "", $days = array(), $day_name_length = 7 ) {
		// Localisation 
		// not needed here anymore. moved to constructor.
		//load_default_textdomain();
		//require_once(ABSWPINCLUDE.'/locale.php');
		//$wp_locale = new WP_Locale();

		// Option : Is the CSS adapted for your site ?
    	$options = get_option('optionsEventsCalendar');
		$adaptedCSS = $options['adaptedCSS'];

		// If not we prepare the CSS style for ToDay
		$todayCSS = '';
		if (!$adaptedCSS)
			$todayCSS = isset($options['todayCSS']) && !empty($options['todayCSS']) ? $options['todayCSS'] : 'background-color:#9BA9CF; color:#FFF;';

		$js = new EC_JS();
		$first_day = get_option('start_of_week');
		$first_of_month = gmmktime(0,0,0,$month,1,$year);
		$day_names = array();

		// January 4, 1970 was a Sunday
		for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400)
			$day_names[$n] = ucfirst($this->locale->get_weekday(gmdate('w',$t)));
		
		list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
		$weekday = ($weekday + 7 - $first_day) % 7; //adjust for $first_day
		//$title = ucfirst($this->locale->get_month($month)).'&nbsp;<span id="EC_ajaxLoader"></span>&nbsp;&nbsp;'.$year;
		$titMonth = ucfirst($this->locale->get_month($month));

		// trying the heredoc constuct... it works
		// todo 	Thinking about this, we should extract all the html from code.
		// 		Using a ec_html class could do the job and possibly be of use
		// 		to other plugin authors...
		// 		
		// 		$html = new EC_Html();
		// 		or
		// 		$html = EC_Html::getInstance();
		//
		// 		$html->div(array('id' => 'calendar_wrapLarge'));
		// 		$html->h2('style' => 'text-align: center;');
		// 		$html->table(array('id'=>'CalendarLarge-Header', 'class' => 'calendar-large'));
		// 		etc.
		//
		// todo 	We really need to get rid of all the inline CSS. I won't do it in the 6.5.2.x
		// 		series but after 7.0 is out, guest what? I'm gonna bug the hell out of the team
		// 		to make sure each and every CSS is out to stylesheet. NO MORE ALIGN="LEFT"
		// 		For crying out loud,I was using that in 1996, along with font-face and the
		// 		infamous blink thingy... 
		// 		-- Louis
		//
		$ajax_loader = EVENTSCALENDARIMAGESURL . '/ajax-loader.gif';
		$calendar  = <<<EOHTML
<div id="calendar_wrapLarge">
	<h2 style="text-align:center;">
	<table id="CalendarLarge-Header" cellspacing="0" cellpadding="0" width="100%" border="0">
	<tr>
		<td width="25%"><div align="left"><span id="EC_previousMonthLarge"></span></div></td>
		<td width="50%"><div id="EC_current-month" align="center"><div id="EC_ajaxLoader"><img src="$ajax_loader" /></div>$titMonth $year</div></td>
		<td width="25%" align="right"><span id="EC_nextMonthLarge"></span></td>
	</tr>
	</table>
	</h2>
	<table summary="Large Event Calendar" id="wp-calendarLarge">
	<thead><tr>
EOHTML;
		
		// Following two lines will get the length of day names - Ron
		$options = get_option('optionsEventsCalendar');
		$day_name_length = $options['daynamelengthLarge'];

		//if the day names should be shown ($day_name_length > 0)
		if ($day_name_length){
			//if day_name_length is >3, the full name of the day will be printed
			foreach($day_names as $d)
				$calendar .= '<th abbr="' . $d . '" scope="col" title="'.$d.'">'
							. ($day_name_length < 4 ? $this->utf8_substr($d,0,$day_name_length) : $d)
							. '</th>' . "\n";
		}
		$calendar .= '</tr></thead>'."\n";

		if ($weekday > 0)
			$calendar .= '<tbody><tr>'."\n".'<td colspan="'.$weekday.'" class="pad">&nbsp;</td>'."\n";
		
		for ($day=1, $days_in_month = gmdate('t',$first_of_month); $day <= $days_in_month; $day++, $weekday++) {
			if($weekday == 7){
				$weekday = 0; //start a new week
				$calendar .= "</tr><tr>\n";
			}
		
			$dayID = '';
			
			if ("$month/$day/$year" == date('m/j/Y'))
				$dayID = (!$adaptedCSS) ? ' id="todayLarge" style="'.$todayCSS.'" ' : ' id="todayLarge" ';
			
			$calendar .= '<td' . $dayID . '>';
			$calendar .= '<div class="dayHead">' . $day . '</div>';
			$calendar .= '<div id="events-calendar-' . $day . 'Large"></div>';
			$calendar .= '</td>'."\n";
		}
		
		if($weekday != 7)
			$calendar .= '<td colspan="'.(7-$weekday).'" class="pad">&nbsp;</td>'."\n"; //remaining "empty" days
		
		$calendar .= "</tr></tbody></table>\n".'<script type="text/javascript">'."\n".'// <![CDATA['."\n";
		$calendar .= ' jQuery.noConflict();'."\n".' (function($) {'."\n".' ecd.jq(document).ready(function() {'."\n";
		
		echo $before_large_calendar;
		echo $calendar;
		
		$js->calendarDataLarge($month, $year);

		echo ' });'."\n".' })(jQuery);'."\n".'//]]>'."\n".'</script>'."\n".'</div>';
	}

	/**
	 * Displays the Admin Calendar.
	 *
	 * @param int $year			year to display
	 * @param int $month			month to display
	 * @param array $days			unknown. not used
	 * @param int $day_name_length		day name length to display. if equal to zero,
	 *					day names won't be shown. default to 7.
	 */
	function displayAdmin($year, $month, $days = array(), $day_name_length = 7) {

		// Localisation
		// not needed here anymore. moved to constructor.
		//load_default_textdomain();
		//require_once(ABSWPINCLUDE.'/locale.php');
		//$wp_locale = new WP_Locale();

		// Option : Is the CSS adapted for your site ?
		$options = get_option('optionsEventsCalendar');
		$adaptedCSS = $options['adaptedCSS'];

		// If not we prepare the CSS style for today
		$todayCSS = '';
		if (!$adaptedCSS)
			$todayCSS = isset($options['todayCSS']) && !empty($options['todayCSS'])
				? $options['todayCSS']
				: 'background-color:#9BA9CF; color:#FFF;';

		$first_day = get_option('start_of_week');
		$first_of_month = gmmktime(0,0,0,$month,1,$year);
		$day_names = array();

		//January 4, 1970 was a Sunday
		for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400)
			$day_names[$n] = ucfirst($this->locale->get_weekday(gmdate('w',$t)));

		list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
		$weekday = ($weekday + 7 - $first_day) % 7; //adjust for $first_day
		$title = ucfirst($this->locale->get_month($month)).'&nbsp;'.$year;
		$previousMonth = $this->locale->get_month(date('n', mktime(0, 0, 0, $month-1, 1, $year)));
		$nextMonth = $this->locale->get_month(date('n', mktime(0, 0, 0, $month+1, 1, $year)));
		
		$calendar = '<div class="ec-wrap">'."\n";

		$calendar .= '<h2 style="padding-right:0;text-align:center;"><a href="?page=events-calendar&amp;EC_action=switchMonthAdmin&amp;EC_month='.($month-1).'&amp;EC_year='.($year).'">&#171; '.$previousMonth.'</a> &mdash; '. __('Events','events-calendar') .' ('.$title.') &mdash; <a href="?page=events-calendar&amp;EC_action=switchMonthAdmin&amp;EC_month='.($month+1).'&amp;EC_year='.($year).'">'.$nextMonth.' &#187;</a></h2><hr />';
		
		$calendar .= '<table width="98%" summary="Admin Event Calendar" id="wp-calendar"><thead><tr>';

		// if the day names should be shown ($day_name_length > 0)
		if ($day_name_length) {
			//if day_name_length is >3, the full name of the day will be printed
			foreach($day_names as $d)
				$calendar .= '<th width="14%" abbr="'.$d.'" scope="col" title="'.$d.'">'.($day_name_length < 4 ? $this->utf8_substr($d,0,$day_name_length) : $d).'</th>';

			$calendar .= '</tr></thead>';
		}

		// initial "empty" days
		if ($weekday > 0)
			$calendar .= '<td colspan="'.$weekday.'" class="pad">&nbsp;</td>';

		for ($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++) {
			if ($weekday == 7) {
				$weekday = 0; //start a new week
				$calendar .= '</tr><tr>';
			}
			$dayID = '';

			if ("$month/$day/$year" == date('m/j/Y'))
				$dayID = (!$adaptedCSS) ? ' id="todayAdmin" style="'.$todayCSS.'" ' : ' id="todayAdmin" ';

			$calendar .= '<td'.$dayID.'><div class="dayHead">'.$day.'</div><div id="events-calendar-'.$day.'"></div></td>';
		}

		// remaining "empty" days
		if ($weekday != 7)
			$calendar .= '<td colspan="'.(7-$weekday).'" class="pad">&nbsp;</td>';

		echo $calendar.'</tr></tbody></table>'."\n";
	}
}
endif;
?>
