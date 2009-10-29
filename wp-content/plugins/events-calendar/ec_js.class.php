<?php
/**
 * This file contains the EC_JS class.
 *
 * @package 			WP-Events-Calendar
 * 
 * @autbor 			Luke Howell <luke@wp-eventscalendar.com>
 * @author 			Brad Bodine <brad@wp-eventscalendar.com>
 * @author 			René MALKA <heirem@wp-eventscalendar.com>
 *
 * @copyright 			Copyright (c) 2007-2009 Luke Howell
 * @copyright 			Copyright (c) 2007-2009 Brad Bodine
 * @copyright 			Copyright (c) 2008-2009 René Malka
 *
 * @license 			GPLv3 {@link http://www.gnu.org/licenses/gpl}
 * @filesource
 */
/*
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
if(!class_exists('EC_JS')) :

require_once(ABSWPINCLUDE.'/capabilities.php');
// require_once(ABSPATH . 'wp-includes/pluggable.php'); Moved in the class for Role Scoper compatibility at least (Thanx Maida ;) )
require_once(EVENTSCALENDARCLASSPATH.'/ec_db.class.php');

/**
 * Displays the calendar content.
 *
 * This class is responsible for outputing the calendar data for both
 * the widget and large calendars and for the event list.
 *
 * Also, the class generates the necessary javascript to show the tooltips
 * and toolbox.
 *
 * @package WP-Events-Calendar
 * @since   6.0  
 */
class EC_JS {

	/**
	 * the $wpdb global
	 * @var object
	 */
	var $db;

	/**
	 * the WP_Locale object
	 * @since 6.5.2.2
	 * @var object
	 * @accesd private
	 */
	var $locale;

	/**
	 * constructor.
	 */
	function EC_JS() {
		// 6.5.2.2 commenting this. i don't understand why it's here.
		// it loads functions unless they have been overriden by plugins.
		// requiring it stops other plugins to overrride these functions.
		// --laplix
		//require_once(ABSWPINCLUDE.'/pluggable.php');
		$this->db = new EC_DB();
		$this->locale = new WP_Locale;
	}

	/**
	 * Manages the year-end transitions in the calendars.
	 *
	 * The method takes care of returning a localized month
	 * name if available.
	 *
	 * @param int $m		month number
	 * @return string		translated month name
	 * @access private
	 */
	function get_incrMonth($m) {
		//$wp_locale = new WP_Locale();
		if ($m > 12)
			$m=1;
		if ($m < 1)
			$m=12;
		return $this->locale->get_month($m);
	}

	/**
	 * Outputs the calendar data for the widget.
	 *
	 * The method also generates the necessary javascript for tooltips
	 * and toolbox.
	 *
	 * If the user checks "I have adapted the CSS stylesheet", in the admin
	 * option panel, 
	 * @param int $month		month number
	 * @param int $year 		year
	 */
	function calendarData($month, $year) {
		global $current_user;

		// Localisation
		// 6.5.2.2 moved to constructor
		//load_default_textdomain();
		//require_once(ABSWPINCLUDE.'/locale.php');
		//$wp_locale = new WP_Locale();
		
		$options = get_option('optionsEventsCalendar');
		$adaptedCSS = $options['adaptedCSS'];

		// Option : Is the CSS adapted for your site ?
		// todo 	instead of hardcoding color:red here, we need a class.
		//       ok this goes deeper. instead of using style="$dayHasEvdent" we should
		//       use class="$dayHasEvent". this is true all over the place...
		$dayHasEventCSS = '';
		if (!$adaptedCSS)
			$dayHasEventCSS = (isset($options['dayHasEventCSS']) && !empty($options['dayHasEventCSS']))
				? $options['dayHasEventCSS']
				: 'color:red;';

		$lastDay = date('t', mktime(0, 0, 0, $month, 1, $year));

		for ($day = 1; $day <= $lastDay; $day++) {
      	$sqldate = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
      	$output = '<ul class="EC-tt-widget-day-event">';
			
			// each day can have multiple events. So loop through them.
			foreach ($this->db->getDaysEvents($sqldate) as $e) {

				if (($e->accessLevel == 'public') || (current_user_can($e->accessLevel))) {
					$title = $e->eventTitle;
					// $description = $e->eventDescription;
					$location = isset($e->eventLocation) ? $e->eventLocation : '';
					$startDate = $e->eventStartDate;
					$endDate = $e->eventEndDate;
					$startTime = isset($e->eventStartTime) ? $e->eventStartTime : '';
					$endTime = isset($e->eventEndTime) ? $e->eventEndTime : '';

					$output .= '<li class="EC-tt-widget-day-event-title">'.$title.'</li>';
					$output .= '<dd class="EC-tt-widget-day-event-detail">'.$location.'</dd>';

					list($ec_startyear, $ec_startmonth, $ec_startday) = explode("-", $startDate);
					list($ec_endyear, $ec_endmonth, $ec_endday) = explode("-", $endDate);
					
					if (($startDate != $endDate) && ($endDate > $sqldate)) {
						$output .= '<dd class="EC-tt-widget-day-event-detail">'
						  		  . __('Until','events-calendar') . __(': ', 'events=calendar')
							     . date($options['dateFormatWidget'], mktime(0, 0, 0, $ec_endmonth, $ec_endday, $ec_endyear))
								  . '</dd>';
					}
					
					if (!is_null($startTime) && ($startTime != '')) {
						list($ec_starthour, $ec_startminute, $ec_startsecond) = explode(":", $startTime);
						$startTime = date($options['timeFormatWidget'], mktime($ec_starthour, $ec_startminute, $ec_startsecond, $ec_startmonth, $ec_startday, $ec_startyear));
						
						if (!is_null($endTime) && ($endTime != '')) {
							list($ec_endhour, $ec_endminute, $ec_endsecond) = explode(":", $endTime);
							$output .= '<dd class="EC-tt-widget-day-event-detail">' . $startTime . ' ' . __('to','events-calendar') . ' '
								. date($options['timeFormatWidget'], mktime($ec_endhour, $ec_endminute, $ec_endsecond, $ec_endmonth, $ec_endday, $ec_endyear))
								. '</dd>';
						
						} else {
							$output .= '<dd class="EC-tt-widget-day-event-detail>'.__('at','events-calendar').' '.$startTime.'</dd>';
						}
					}
				}
			}	// foreach
			
			$output .= '</ul>';
			$clickdate = __('Click date for more details','events-calendar');
			if ($output != '<ul class="EC-tt-widget-day-event"></ul>') {
				$output .= '<span class="EC-tt-widget-clickdate">'.$clickdate.'</span>';

				$format = $options['dateFormatLarge'];
				$elemnts_date = explode(' ', $format);

				if ($format == $elemnts_date[0])
					$elemnts_date = explode('-', $format);
				
				if ($format == $elemnts_date[0])
					$elemnts_date = explode('/', $format);
        
				if ($format == $elemnts_date[0])
					$elemnts_date = explode("\\", $format);

				if ($format == $elemnts_date[0])
					$elemnts_date = explode(',', $format);

				if ($format == $elemnts_date[0]) //added by pepawo
					$elemnts_date = explode('.', $format); //added by pepawo
        
				if (($format == $elemnts_date[0]) || ($elemnts_date[2] == Null )) {

					echo '<script type="text/javascript">alert("' 
						. __('Review your Large Calendar Date Format in the Events-Calendars options ;-)','events-calendar')
					  	. '");</script>';
					exit;
				}
				
				$date_show = '';
				
				foreach ( $elemnts_date as $elem_dt ) {
					// Find the DAY in the format string
					if (substr_count('dDjlNSwz', $elem_dt))
						$date_show .= ucfirst($this->locale->get_weekday(gmdate('w', mktime(0,0,0,$month,$day,$year)))) . ' ' . $day . ' ';
					
					// Find the MONTH in the format string
					if (substr_count('FmMnt', $elem_dt))
						$date_show .= ucfirst($this->locale->get_month($month)) . ' ';
					
					// Attrib the YEAR
					if (substr_count('0Yy', $elem_dt))
						$date_show .= $year;
				}
 
				// making the textbox as large as necessary
				$len_desc = strlen($e->eventDescription);
				if ($len_desc < 100) {
					$tbw = 220;
					$tbh = 250;
				}
				elseif ($len_desc < 250) {
					$tbw = 320;
					$tbh = 350;
				}
				else {
					$tbw = 420;
					$tbh = 450;
				}

				// make sure we don't double escape
				if (preg_match("/\'/", $output))
					$output = stripslashes($output);
?>
		ecd.jq('#events-calendar-<?php echo $day;?>')
			.attr('title', '<?php echo addslashes($output);?>')
			.attr('style', '<?php echo $dayHasEventCSS;?>')
			.mouseover(function() {
				ecd.jq(this).css('cursor', 'pointer');
      	})
	      .click(function() {
         	tb_show(	"<?php echo $date_show; ?>", "<?php bloginfo('siteurl');?>?EC_view=day&EC_month=<?php echo $month;?>&EC_day=<?php echo $day;?>&EC_year=<?php echo trim($year);?>&TB_iframe=true&width=<?php echo $tbw;?>&height=<?php echo $tbh;?>", false);
      	})
      	.tooltip({
        		track: true,
        		delay: 0,
        		showURL: false,
        		opacity: 1,
        		fixPNG: true,
        		showBody: " - ",
        		// extraClass: "pretty fancy",
        		top: -15,
        		left: 10
			});

<?php
			}
		}
?>
		ecd.jq('#EC_previousMonth')
			.append('&#171;<?php echo ucfirst($this->locale->get_month_abbrev($this->get_incrMonth($month-1)));?>')
			.mouseover(function() {
				ecd.jq(this).css('cursor', 'pointer');
      		})
			.click(function() {
				ecd.jq('#EC_loadingPane').append('<img src="<?php echo EVENTSCALENDARIMAGESURL . '/loading.gif';?>" style="width:50px;" />');
				ecd.jq.get("<?php bloginfo('siteurl');?>/index.php",
					{EC_action: "switchMonth", EC_month: <?php echo $month-1;?>, EC_year: <?php echo $year;?>},
					function(ecdata) {
						ecd.jq('#calendar_wrap').empty().append(ecd.jq(ecdata).html());
					});
				});

		ecd.jq('#EC_nextMonth')
			.prepend('<?php echo ucfirst($this->locale->get_month_abbrev($this->get_incrMonth($month+1)));?>&#187;')
			.mouseover(function() {
				ecd.jq(this).css('cursor', 'pointer');
      		})
			.click(function() {
				ecd.jq('#EC_loadingPane').append('<img src="<?php echo EVENTSCALENDARIMAGESURL . '/loading.gif';?>" style="width:50px;" />');
				ecd.jq.get("<?php bloginfo('siteurl');?>/index.php",
					{EC_action: "switchMonth", EC_month: <?php echo ($month+1);?>, EC_year: <?php echo $year;?>},
					function(ecdata) {
						ecd.jq('#calendar_wrap').empty().append(ecd.jq(ecdata).html());
					});
				});

		ecd.jq.preloadImages = function() {
			for (var i = 0; i < arguments.length; i++) {
				jQuery("img").attr("src", arguments[i]);
			}
		}
		ecd.jq.preloadImages("<?php echo EVENTSCALENDARIMAGESURL . '/loading.gif';?>");
<?php
	}

	/**
	 * Outputs the large calendar.
	 *
	 * @param int $m 		the month
	 * @param int $y 		the year
	 */
	function calendarDataLarge($m, $y) {
		global $current_user;

		// Localisation
		// 6.5.2.2 moved to constructor
		//load_default_textdomain();
		//require_once(ABSWPINCLUDE.'/locale.php');
		//$wp_locale = new WP_Locale();

		$options = get_option('optionsEventsCalendar');
		$lastDay = date('t', mktime(0, 0, 0, $m, 1, $y));

		for($d = 1; $d <= $lastDay; $d++) {
			$sqldate = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));

			foreach($this->db->getDaysEvents($sqldate) as $e) {
				// Change: Output has to be after foreach and before the if statement.
				$output = '';
				if (($e->accessLevel == 'public') || (current_user_can($e->accessLevel))) {
					// $output = '';
					$id = "$d-$e->id";
					$title = $e->eventTitle;
					$description = preg_replace('#\r?\n#', '<br />', $e->eventDescription);
					if (strlen($description) > 750)
						$description = substr($description, 0, 750). ' (...)';
					$location = isset($e->eventLocation) && !empty($e->eventLocation) ? $e->eventLocation : '';
					$linkout = isset($e->eventLinkout) && !empty($e->eventLinkout) ? $e->eventLinkout : '';
					$startDate = $e->eventStartDate;
					$startTime = $e->eventStartTime;
					$endDate = $e->eventEndDate;
					$endTime = $e->eventEndTime;
					$PostID = isset($e->postID) ? $e->postID : '';

					// if ((!is_null($startDate) && !empty($startDate))) {
					
					list($ec_startyear, $ec_startmonth, $ec_startday) = explode("-", $startDate);
					$startDate = date($options['dateFormatLarge'], mktime($ec_starthour, $ec_startminute, $ec_startsecond, $ec_startmonth, $ec_startday, $ec_startyear));
					
					// }
					// if (($endDate != null) && (!empty($endDate))) {
					
					list($ec_endyear, $ec_endmonth, $ec_endday) = explode("-", $endDate);
					$endDate = date($options['dateFormatLarge'], mktime($ec_endhour, $ec_endminute, $ec_endsecond, $ec_endmonth, $ec_endday, $ec_endyear));
					
					// }
					
					if ((!is_null($startTime)) && (!empty($startTime))) {
						list($ec_starthour, $ec_startminute, $ec_startsecond) = explode(":", $startTime);
						$startTime = date($options['timeFormatLarge'], mktime($ec_starthour, $ec_startminute, $ec_startsecond, $ec_startmonth, $ec_startday, $ec_startyear));
					}
					if ((!is_null($endTime)) && (!empty($endTime))) {
						list($ec_endhour, $ec_endminute, $ec_endsecond) = explode(":", $endTime);
						$endTime = date($options['timeFormatLarge'], mktime($ec_endhour, $ec_endminute, $ec_endsecond, $ec_endmonth, $ec_endday, $ec_endyear));
					}

					if (!empty($title) && !is_null($title))
						$output .= '<div class="EC-tt-title"><span class="EC-tt-data EC-tt-title-data">'.$title.'</span></div>';
					if (!empty($location) && !is_null($location))
						$output .= '<div class="EC-tt-location"><span class="EC-tt-label EC-tt-location-label">'._c('Location','events-calendar').': </span><span class="EC-tt-data EC-tt-location-data">' . $location.'</span></div>';
					if (!empty($description) && !is_null($description))
						$output .= '<div class="EC-tt-description"><span class="EC-tt-label EC-tt-description-label">'._c('Description','events-calendar').': </span><span class="EC-tt-data EC-tt-description-data">'.$description.'</span></div>';
					if ($startDate != $endDate) // && (!is_null($startDate) || !empty($startDate)) && (!is_null($endDate) || !empty($endDate)))
						$output .= '<div class="EC-tt-startdate"><span class="EC-tt-label EC-tt-startdate-label">'._c('Start Date','events-calendar').': </span><span class="EC-tt-data EC-tt-startdate-data">'.$startDate.'</span></div>';
					if (!empty($startTime) || !is_null($startTime))
						$output .= '<div class="EC-tt-starttime"><span class="EC-tt-label EC-tt-starttime-label">'._c('Start Time','events-calendar').': </span><span class="EC-tt-data EC-tt-starttime-data">'.$startTime.'</span></div>';
					if ($startDate != $endDate) // && (!is_null($endDate) || !empty($endDate)))
						$output .= '<div class="EC-tt-enddate"><span class="EC-tt-label EC-tt-enddate-label">'._c('End Date','events-calendar').': </span><span class="EC-tt-data EC-tt-enddate-data">'.$endDate.'</span></div>';
					if (!empty($endTime) && !empty($startTime) || !is_null($endTime) && !is_null($startTime))
						$output .= '<div class="EC-tt-endtime"><span class="EC-tt-label EC-tt-endtime-label">'._c('End Time','events-calendar').': </span><span class="EC-tt-data EC-tt-endtime-data">'.$endTime.'</span></div>';
					//
					// Link outside the site if the link exist : priority on the PostID link
					if ($linkout != '') {
						$output .= '<div class="EC-tt-linkout"><span class="EC-tt-label EC-tt-linkout-label">'._c('Link out','events-calendar').': </span><span class="EC-tt-data EC-tt-linkout-data">'.substr($linkout,0,19).'</span></div>';
						$titlinked = '<a class="EC-tt-title-link EC-tt-user-link" href="' . $linkout. '" target="_blank">'.$title.'</a>';
					} elseif ($PostID != '') { // Link to a post when exist
						$titlinked = '<a class="EC-tt-title-link EC-tt-post-link" href="' . get_permalink($PostID) . '">'.$title.'</a>';
					} else {
						$titlinked = '<span class="EC-tt-title-no-link">'.$title.'</span>';
					}
					$cursor = (($PostID != '') OR ($linkout != '')) ? 'pointer' : 'default';
				}

				if($output != '') {

					// this corrects a problem where we saw escaped string in text.
					// we will addslashes just before echoing the strings.
					// this is not optimal. we should use WP functions to do this
					// but this is going to do for now, until 7.0 is up and running.
					// and if i am to start testing 7.0, i need to stop working on
					// this one.
					$pattern = "/\'/";
					if (preg_match($pattern, $titlinked))
						$titlinked = stripslashes($titlinked);

					if (preg_match($pattern, $output))
						$output = stripslashes($output);

					// need to decide the width of the tooltip. With large descriptions, the tooltip
					// looses its head...
					$len_desc = strlen($description);
					if ($len_desc > 250)
						$EC_tt_special = 'EC-tt-100';
					elseif ($len_desc > 150)
						$EC_tt_special = 'EC-tt-75';
					if ($len_desc > 50)
						$EC_tt_special = 'EC-tt-50';
					else
						$EC_tt_special = 'EC-tt-25';
					
?>
ecd.jq('#events-calendar-<?php echo $d;?>Large')
	.append('<span class="event-block" id="events-calendar-<?php echo $id;?>Large"><?php echo addslashes($titlinked);?></span>');
ecd.jq('#events-calendar-<?php echo $id;?>Large')
	.attr('title', '<?php echo addslashes($output);?>')
	.mouseover(function() {
		ecd.jq(this).css('cursor', '<?php echo $cursor; ?>');
		})
	.tooltip({
		track: true,
		delay: 0,
		showURL: false,
		opacity: 1,
		fixPNG: true,
		showBody: " - ",
		extraClass: "<?php echo $EC_tt_special;?>",
		top: -15,
		left: 15
	});
<?php
				} // if
			} //endforeach
		}
?>
ecd.jq('#EC_previousMonthLarge')
	.append("&#171;&nbsp;<?php echo ucfirst($this->get_incrMonth($m-1));?>")
	.mouseover(function() {
		ecd.jq(this).css('cursor', 'pointer')
		})
	.click(function() {
		ecd.jq('#EC_ajaxLoader').show('slow');
		ecd.jq.get("<?php bloginfo('siteurl');?>/index.php",
        {EC_action: "switchMonthLarge", EC_month: "<?php echo $m-1;?>", EC_year: "<?php echo $y;?>"},
        function(ecdata) {
			 ecd.jq('#EC_ajaxLoader').hide('slow');
          ecd.jq('#calendar_wrapLarge').empty().append(ecd.jq(ecdata).html());
        });
      });

ecd.jq('#EC_nextMonthLarge')
	.prepend("<?php echo ucfirst($this->get_incrMonth($m+1));?>&nbsp;&#187;")
	.mouseover(function() {
		ecd.jq(this).css('cursor', 'pointer')
		})
	.click(function() {
		ecd.jq('#EC_ajaxLoader').show('slow');
		ecd.jq.get("<?php bloginfo('siteurl');?>/index.php",
			{EC_action: "switchMonthLarge", EC_month: "<?php echo $m+1;?>", EC_year: "<?php echo $y;?>"},
			function(ecdata) {
				ecd.jq('#EC_ajaxLoader').hide('slow');
				ecd.jq('#calendar_wrapLarge').empty().append(ecd.jq(ecdata).html());
			});
		});
<?php
	} // end of calendarDataLarge

	/**
	 * provides an unordered list of events and the necessary javascript to make it work.
	 *
	 * @param array $events 		array of event objects
	 */
	function listData($events) {
    /* Localisation ------------------------------------------------***/
	 // 6.5.2.2 moved to constructor
    //load_default_textdomain();
    //require_once(ABSWPINCLUDE.'/locale.php');
    //$wp_locale = new WP_Locale();
    /* -------------------------------------------------------------***/
    global $current_user;
    $options = get_option('optionsEventsCalendar');
    $format = $options['dateFormatLarge'];
    foreach($events as $e):
    $output = '';
    if($e->accessLevel == 'public' || $current_user->has_cap($e->accessLevel)) {
      $id = "$e->id";
      $title = $e->eventTitle;
      $description = preg_replace('#\r?\n#', '<br />', $e->eventDescription);
      $location = isset($e->eventLocation) && !empty($e->eventLocation) ? $e->eventLocation : '';
      list($ec_startyear, $ec_startmonth, $ec_startday) = explode("-", $e->eventStartDate);
        if(!is_null($e->eventStartTime) && !empty($e->eventStartTime)) {
          list($ec_starthour, $ec_startminute, $ec_startsecond) = explode(":", $e->eventStartTime);
          $startTime = date($options['timeFormatLarge'], mktime($ec_starthour, $ec_startminute, $ec_startsecond, $ec_startmonth, $ec_startday, $ec_startyear));
		  }
		  else {
			  $startTime = null;
			  $ec_starthour = $ec_startminute = $ec_startsecond = 0;
		  }
        $startDate = date($options['dateFormatLarge'], mktime($ec_starthour, $ec_startminute, $ec_startsecond, $ec_startmonth, $ec_startday, $ec_startyear));
        list($ec_endyear, $ec_endmonth, $ec_endday) = split("-", $e->eventEndDate);
        if($e->eventEndTime != null && !empty($e->eventEndTime)) {
          list($ec_endhour, $ec_endminute, $ec_endsecond) = split(":", $e->eventEndTime);
          $endTime = date($options['timeFormatLarge'], mktime($ec_endhour, $ec_endminute, $ec_endsecond, $ec_endmonth, $ec_endday, $ec_endyear));
		  }
		  else {
			  $endTime = null;
			  $ec_endhour = $ec_endminute = $ec_endsecond = 0;
		  }

        $endDate = date($options['dateFormatLarge'], mktime($ec_endhour, $ec_endminute, $ec_endsecond, $ec_endmonth, $ec_endday, $ec_endyear));
      $accessLevel = $e->accessLevel;
      $output .= "<strong>"._c('Title','events-calendar').": </strong>$title<br />";
      if(!empty($location) && !is_null($location))
        $output .= "<strong>"._c('Location','events-calendar').": </strong>$location<br />";
      if(!empty($description) && !is_null($description))
        $output .= "<strong>"._c('Description','events-calendar').": </strong>$description<br />";
      if($startDate != $endDate )
        $output .= "<strong>"._c('Start Date','events-calendar').": </strong>$startDate<br />";
      if(!empty($startTime) || !is_null($startTime))
        $output .= "<strong>"._c('Start Time','events-calendar').": </strong>$startTime<br />";
      if($startDate != $endDate)
        $output .= "<strong>"._c('End Date','events-calendar').": </strong>$endDate<br />";
      if(!empty($endTime) && !empty($startTime) || !is_null($endTime) && !is_null($startTime))
        $output .= "<strong>"._c('End Time','events-calendar').": </strong>$endTime<br />";
    }
    if($output != ''):
		 if (preg_match("/\'/", $output))
			 $output = stripslashes($output);

?>
<script type="text/javascript">
// <![CDATA[
//jQuery.noConflict();
//(function($) {
	ecd.jq(document).ready(function() {
		ecd.jq('#events-calendar-list-<?php echo $id;?>')
			.attr('title', '<?php echo addslashes($output);?>')
			.mouseover(function() {
				ecd.jq(this).css('cursor', 'pointer');
				});
		ecd.jq('#events-calendar-list-<?php echo $e->id;?>').tooltip({
        delay:0,
        track:true
      });
	});
//})(jQuery);
//]]>
</script>
<?php
    endif;
    endforeach;
  }
}
endif;
?>
