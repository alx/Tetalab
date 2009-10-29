<?php
/**
 * This file contains the EC_Widget class.
 *
 * @package			WP-Events-Calendar
 * @since			6.0
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

if(!class_exists('EC_Widget')) :
require_once(EVENTSCALENDARCLASSPATH . '/ec_calendar.class.php');
require_once(EVENTSCALENDARCLASSPATH . '/ec_js.class.php');

/**
 * Displays the sidebar widget.
 *
 * This can either be the small calendar or the event list, 
 * depending on the widget control option.
 *
 * @package WP-Events-Calendar
 * @since 6.0
 */
class EC_Widget {

	/**
	 * Month to display.
	 * 
	 * @since 6.0
	 * @access private
	 * @var int
	 */
	var $month;

	/**
	 * Year to display.
	 *
	 * @since 6.0
	 * @access private
	 * @var int
	 */
	var $year;

	/**
	 * Holds the EC_Calendar object.
	 *
	 * @since 6.0
	 * @access private
	 * @var object
	 */
	var $calendar;

	/**
	 * Constructor.
	 *
	 * Instantiates the EC_Calendar and setups the year-month to display.
	 * This is either going to be the current month or one asked by the user
	 * when clicking on the navigation links in the calendar.
	 *
	 * @since 6.5.2.2
	 */
	function __construct() {
		$this->calendar = new EC_Calendar();
		$this->month = date('m');
		$this->year = date('Y');
	  	if (isset($_GET['EC_action'])) {
			$this->month = $_GET['EC_action'] == 'switchMonth' ? (int)$_GET['EC_month'] : date('m');
    		$this->year = $_GET['EC_action'] == 'switchMonth' ? (int)$_GET['EC_year'] : date('Y');
		}
	}
	/**
	 * Prehistoric constructor. Calls the PHP5 constructor.
	 *
	 * @since 6.0
	 */
	function EC_Widget() {
		$this->__construct();
	}

	/**
	 * Displays the widget.
	 *
	 * This is called from event-calendar.php and eventscalendar.class.php
	 * Depending on the wiget option "type", it will either display the
	 * small calendar or the event list.
	 *
	 *
	 * @since 6.0
	 *
	 * @param array  $args            an array containing the following parameters
	 * @param string $name            the sidebar name
	 * @param int    $id              the sidebar id
	 * @param string $before_widget   
	 * @param string $after_widget    
	 * @param string $before_title    
	 * @param string $after_title     
	 * @param int    $widget_id       the widget ID
	 * @param string $widget_name     the wwidget name
	 */
	function display($args) {
		$js = new EC_JS();
		extract($args);
		echo $before_widget;
		$options = get_option('widgetEventsCalendar');

		if(isset($options['title']) && !empty($options['title']))
			echo $before_title . $options['title'] . $after_title;

		if($options['type'] == 'calendar') {
			$this->calendar->displayWidget($this->year, $this->month);
		}
		else {
			if (!isset($options['listCount'])) 
				$this->calendar->displayEventList(5);
			else
				$this->calendar->displayEventList($options['listCount']);
		}
		echo $after_widget;
	}
}
endif;
?>
