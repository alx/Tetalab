<?php
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

if(!class_exists('EventsCalendar')) :
require_once(EVENTSCALENDARCLASSPATH . '/ec_widget.class.php');
require_once(EVENTSCALENDARCLASSPATH . '/ec_management.class.php');

class EventsCalendar {
  var $widget;
  var $management;

  function EventsCalendar() {
    $this->widget = new EC_Widget();
	  $this->management = new EC_Management();
  }

  function displayWidget($args) {
	  $this->widget->display($args);
  }

  function displayManagementPage() {
    $this->management->display();
  }

  function displayOptionsPage() {
    $this->management->calendarOptions();
  }
}
endif;
?>
