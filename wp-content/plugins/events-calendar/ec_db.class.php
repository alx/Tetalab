<?php
/**
 * This file contains the EC_DB class.
 *
 * @package 			WP-Events-Calendar
 * 
 * @autbor			Luke Howell <luke@wp-eventscalendar.com>
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
if(!class_exists('EC_DB')):

/**
 * This class is used by WPEC to access and modify the database.
 *
 * All the DB work needed by WPEC is done through this class. 
 * Internally, it uses the $wpdb global object provided by WordPress.
 *
 * If installing the plugin, the class will be called upon to create
 * or update the WPEC database table.
 *
 * tHIS IS WHY IT IS IMPORTANT TO DEACTIVATE AND THEN REACTIVATE 
 * THE PLUGIN WHEN UPGRADING TO A NEW VERSION.
 * 
 * Later on, EC_DB is used to read, create, modify and delete events.
 *
 * @package WP-Events-Calendar
 * @since   6.0  
 */
class EC_DB {

	/**
	 * Holds an instance of the $wpdb object.
	 * @var object
	 * @access private
	 */
	var $db;

	/**
	 * Name of the WPEC table where events are stored.
	 * @var string
	 * @access private
	 */
	var $mainTable;

	/**
	 * Name of the posts table with its prefix.
	 * @var string
	 * @access private
	 */
	var $postsTable;

	/**
	 * Holds the main WPEC table version.
	 * @var int
	 * @access private
	 */
	var $dbVersion;

	/**
	 * Constructor. 
	 * Loads the $wpdb global object and makes sure we have the good table name
	 */
	function EC_DB() {
		global $wpdb;
		$this->dbVersion = "108";
		$this->db = $wpdb;
		$this->mainTable = $this->db->prefix . 'eventscalendar_main';
		
		// FIXME why is this needed? Is it for backward compatibility?
		$this->mainTableCaps = $this->db->prefix . 'EventsCalendar_main';
		if ($this->db->get_var("show tables like '$this->mainTableCaps'") == $this->mainTableCaps)
			$this->mainTable = $this->mainTableCaps;

		$this->postsTable = $this->db->prefix . 'posts';
	}

	/**
	 * Called on plugin activation to create or upgrade the WPEC table.
	 * FIXME I don't think we need that much code here. get_option will
	 *       return false if an option does not exist. This means that
	 *       if the eventscalendar_db_version is false or different
	 *       from the new version, we just execute the SQL.
	 */
	function createTable() {
		if ($this->db->get_var("show tables like '$this->mainTable'") != $this->mainTable ) {
			$sql = "CREATE TABLE " . $this->mainTable . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				eventTitle varchar(255) CHARACTER SET utf8 NOT NULL,
				eventDescription text CHARACTER SET utf8 NOT NULL,
				eventLocation varchar(255) CHARACTER SET utf8 default NULL,
				eventLinkout varchar(255) CHARACTER SET utf8 default NULL,
				eventStartDate date NOT NULL,
				eventStartTime time default NULL,
				eventEndDate date NOT NULL,
				eventEndTime time default NULL,
				accessLevel varchar(255) CHARACTER SET utf8 NOT NULL default 'public',
				postID mediumint(9) NULL DEFAULT NULL,
				PRIMARY KEY  id (id)
				);";

			require_once(ABSPATH . "wp-admin/upgrade-functions.php");
			dbDelta($sql);

			// Request whithout CHARACTER SET utf8 if the CREATE TABLE failed
			if ($this->db->get_var("show tables like '$this->mainTable'") != $this->mainTable ) {
				$sql = str_replace("CHARACTER SET utf8 ","",$sql);
				dbDelta($sql);
			}
			add_option("events_calendar_db_version", $this->dbVersion);
		}

		$installed_ver = get_option( "eventscalendar_db_version" );

		if ($installed_ver != $this->dbVersion) {
			$sql = "CREATE TABLE " . $this->mainTable . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				eventTitle varchar(255) CHARACTER SET utf8 NOT NULL,
				eventDescription text CHARACTER SET utf8 NOT NULL,
				eventLocation varchar(255) CHARACTER SET utf8 default NULL,
				eventLinkout varchar(255) CHARACTER SET utf8 default NULL,
				eventStartDate date NOT NULL,
				eventStartTime time default NULL,
				eventEndDate date NOT NULL,
				eventEndTime time default NULL,
				accessLevel varchar(255) CHARACTER SET utf8 NOT NULL default 'public',
				postID mediumint(9) NULL DEFAULT NULL,
				PRIMARY KEY  id (id)
				);";

			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			dbDelta($sql);

			$this->db->query("UPDATE " . $this->mainTable . " SET `eventLocation` = REPLACE(`eventLocation`,' ','');");
			$this->db->query("UPDATE " . $this->mainTable . " SET `eventLocation` = REPLACE(`eventLocation`,'',NULL);");
			$this->db->query("UPDATE " . $this->mainTable . " SET `eventStartTime` = REPLACE(`eventStartTime`,'00:00:00',NULL);");
			$this->db->query("UPDATE " . $this->mainTable . " SET `eventEndTime` = REPLACE(`eventEndTime`,'00:00:00',NULL);");

			update_option( "events_calendar_db_version", $this->dbVersion);
		}
	}

	/**
	 * Initializes the WPEC options.
	 *
	 * This makes sure our options are in database with sensible values.
	 *
	 * There are two sets of options, the Events Calendar general options
	 * and the widget options.
	 */
	function initOptions() {

		$options = get_option('optionsEventsCalendar');
		if(!is_array($options)) $options = array();
		if (!isset($options['dateFormatWidget'])) $options['dateFormatWidget'] = 'm-d';
		if (!isset($options['timeFormatWidget'])) $options['timeFormatWidget'] = 'g:i a';
		if (!isset($options['dateFormatLarge'])) $options['dateFormatLarge'] = 'n/j/Y';
		if (!isset($options['timeFormatLarge'])) $options['timeFormatLarge'] = 'g:i a';
		if (!isset($options['timeStep'])) $options['timeStep'] = '30';
		if (!isset($options['adaptedCSS'])) $options['adaptedCSS'] = '';
		if (!isset($options['jqueryextremstatus'])) $options['jqueryextremstatus'] = 'false';
		if (!isset($options['todayCSS'])) $options['todayCSS'] = 'border:thin solid blue;font-weight: bold;';
		if (!isset($options['dayHasEventCSS'])) $options['dayHasEventCSS'] = 'color:red;';
		if (!isset($options['daynamelength'])) $options['daynamelength'] = '3';
		if (!isset($options['daynamelengthLarge'])) $options['daynamelengthLarge'] = '3';
		if (!isset($options['accessLevel'])) $options['accessLevel'] = 'level_10';
		update_option('optionsEventsCalendar', $options);

		$widget_options = get_option('widgetEventsCalendar');
		if (!is_array($widget_options) || empty($widget_options))
			$widget_options = array();
		if (!isset($widget_options['title']))
			$widget_options['title'] = __('Events Calendar', 'events-calendar');
		if (!isset($widget_options['type']))
			$widget_options['type'] = 'calendar';
		if (!isset($widget_options['listCount']))
			$widget_options['listCount'] = 5;
		update_option('widgetEventsCalendar');
	}

	/**
	 * Adds a new event into database.
	 *
	 * @param int 		$id 			the event id
	 * @param string 	$title 			the event title
	 * @param string 	$location 		the event location
	 * @param string 	$linkout 		URL to an external web site
	 * @param string 	$description 		description of the event
	 * @param date		$startDate 		date of the event. If empty, will be today.
	 * @param time 		$startTime 		start time of the event.
	 * @param date 		$endDate 		end date. if empty, will be same as start date.
	 * @param time 		$endTime		end time
	 * @param int 		$accessLevel 		who has access to this event
	 * @param int 		$postId 		post id if use activated it
	 */
	function addEvent($title, $location, $linkout, $description, $startDate, $startTime, $endDate, $endTime, $accessLevel, $postID) {
		$postID = is_null($postID) ? "NULL" : "'$postID'";
		$location = is_null($location) ? "NULL" : "'$location'";
		$description = is_null($description) ? "NULL" : "'$description'";
		$startDate = is_null($startDate) ? "NULL" : "'$startDate'";
		$endDate = is_null($endDate) ? "NULL" : "'$endDate'";
		$linkout = is_null($linkout) ? "NULL" : "'$linkout'";
		$startTime = is_null($startTime) ? "NULL" : "'$startTime'";
		$accessLevel = is_null($accessLevel) ? "NULL" : "'$accessLevel'";
		$endTime = is_null($endTime) ? "NULL" : "'$endTime'";

		$sql = "INSERT INTO `$this->mainTable` ("
			 ."`id`, `eventTitle`, `eventDescription`, `eventLocation`, `eventLinkout`,`eventStartDate`, `eventStartTime`, `eventEndDate`, `eventEndTime`, `accessLevel`, `postID`) "
			 ."VALUES ("
			 ."NULL , '$title', $description, $location, $linkout, $startDate, $startTime, $endDate, $endTime , $accessLevel, $postID);";

		$this->db->query($sql);
	}

	/**
	 * Updates an already existing event.
	 *
	 * @param int 		$id 			the event id
	 * @param string 	$title 			the event title
	 * @param string 	$location 		the event location
	 * @param string 	$linkout 		URL to an external web site
	 * @param string 	$description 		description of the event
	 * @param date 		$startDate 		date of the event. If empty, will be today.
	 * @param time 		$startTime 		start time of the event.
	 * @param date 		$endDate 		end date. if empty, will be same as start date.
	 * @param time 		$endTime 		end time
	 * @param int 		$accessLevel 		who can access this event
	 * @param int 		$postId 		post id if use activated it
	 */
	function editEvent($id, $title, $location, $linkout, $description, $startDate, $startTime, $endDate, $endTime, $accessLevel, $postID) {

		// just to make sure
		if (empty($id))
			return;

		// todo get rid of the quotes here. don't need them anymore
		// since we are using wpdb->prepare()
		$postID = is_null($postID) ? "NULL" : "'$postID'";
		$location = is_null($location) ? "NULL" : "'$location'";
		$description = is_null($description) ? "NULL" : "'$description'";
		$startDate = is_null($startDate) ? "NULL" : "'$startDate'";
		$endDate = is_null($endDate) ? "NULL" : "'$endDate'";
		$linkout = is_null($linkout) ? "NULL" : "'$linkout'";
		$startTime = is_null($startTime) ? "NULL" : "'$startTime'";
		$accessLevel = is_null($accessLevel) ? "NULL" : "'$accessLevel'";
		$endTime = is_null($endTime) ? "NULL" : "'$endTime'";

		$sql = "UPDATE `$this->mainTable` SET "
			."`eventTitle` = '$title', "
			."`eventDescription` = $description, "
			."`eventLocation` = $location, "
			."`eventLinkout` = $linkout, "
			."`eventStartDate` = $startDate, "
			."`eventStartTime` = $startTime, "
			."`eventEndDate` = $endDate, "
			."`eventEndTime` = $endTime, "
			."`postID` = $postID, "
			."`accessLevel` = $accessLevel"
			." WHERE `id` = $id LIMIT 1;";

		$this->db->query($sql);
	}

	/**
	 * Deletes an event.
	 * @param int $id 		ID of the event to delete.
	 */
	function deleteEvent($id) {
		if (empty($id))
			return;

		$sql = "DELETE FROM `$this->mainTable` WHERE `id` = %d";
		$this->db->query($this->db->prepare($sql,(int)$id));
	}

	/**
	 * Returns the events for a specified date.
	 *
	 * @param date $d
	 * @return array 
	 */
	function getDaysEvents($d) {
		$sql = "SELECT *"
		 	. "  FROM `$this->mainTable`"
		  	. " WHERE `eventStartDate` <= '$d'"
			. "   AND `eventEndDate` >= '$d'"
			. " ORDER BY `eventStartTime`, `eventEndTime`;";
		return $this->db->get_results($sql);
	}

	/**
	 * Returns a specific event.
	 *
	 * @param int $id
	 * @return array
	 */
	function getEvent($id) {
		$sql = "SELECT * FROM `$this->mainTable` WHERE `id` = $id LIMIT 1;";
		return $this->db->get_results($sql);
	}

	/**
	 * Returns upcoming events.
	 * @param int $num 		number of events to retrieve
	 * @return array
	 */
	function getUpcomingEvents($num = 5) {
		$dt = date('Y-m-d');
		$sql = "SELECT *"
			. "  FROM `$this->mainTable`"
			. " WHERE `eventStartDate` >= '$dt'"
			. "    OR `eventEndDate` >= '$dt'"
			. " ORDER BY eventStartDate, eventStartTime LIMIT $num";
		return $this->db->get_results($sql);
	}

	/**
	 * Returns the latest post id.
	 *
	 * @todo Why should we call this. Latest post could be anything!!!
	 *
	 * @return array
	 */
	function getLatestPost() {
		$sql = "SELECT `id` FROM `$this->postsTable` ORDER BY `id` DESC LIMIT 1;";
		return $this->db->get_results($sql);
	}
}
endif;
?>
