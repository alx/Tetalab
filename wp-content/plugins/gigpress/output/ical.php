<?php

function gigpress_ical() {

	global $wpdb, $gpo;
	$further_where = '';
	if(isset($_GET['show_id'])) {
		$further_where .= $wpdb->prepare(' AND s.show_id = %d', $_GET['show_id']);
	}
	if(isset($_GET['artist'])) {
		$further_where .= $wpdb->prepare(' AND s.show_artist_id = %d', $_GET['artist']);
	}
	if(isset($_GET['tour'])) {
		$further_where .= $wpdb->prepare(' AND s.show_tour_id = %d', $_GET['tour']);
	}
	if(isset($_GET['venue'])) {
		$further_where .= $wpdb->prepare(' AND s.show_venue_id = %d', $_GET['venue']);
	}

	$shows = $wpdb->get_results(
		$wpdb->prepare("SELECT * FROM " . GIGPRESS_ARTISTS . " AS a, " . GIGPRESS_VENUES . " as v, " . GIGPRESS_SHOWS ." AS s LEFT JOIN  " . GIGPRESS_TOURS . " AS t ON s.show_tour_id = t.tour_id WHERE show_status != 'deleted' AND s.show_artist_id = a.artist_id AND s.show_venue_id = v.venue_id" . $further_where, $_GET['show_id'])
		);
	if($shows) {
		$count = 1;
		$total = count($shows);
		foreach($shows as $show) {
			$showdata = gigpress_prepare($show, 'ical');
			if(isset($_GET['artist'])) {
				$filename = sanitize_title($showdata['artist']) . '-icalendar';
				$title = $show->artist_name;
			} elseif(isset($_GET['tour'])) {
				$filename = sanitize_title($showdata['tour']) . '-icalendar';
				$title = $show->tour_name;
			} elseif(isset($_GET['venue'])) {
				$filename = sanitize_title($showdata['venue_plain']) . '-icalendar';
				$title = $show->venue_name;
			} elseif(isset($_GET['show_id'])) {
				$filename = sanitize_title($showdata['artist']) . '-' . $show->show_date;
				$title = $show->artist_name . ' - ' . $showdata['date'];
			} else {
				$filename = sanitize_title(get_bloginfo('name')) . '-icalendar';
				$title = $gpo['rss_title'];
			}

			if($count == 1) {		
header('Content-type: text/calendar');
header('Content-Disposition: attachment; filename="' . $filename . '.ics"');
echo("BEGIN:VCALENDAR
X-WR-CALNAME:" . $title . "
PRODID:GIGPRESS 2.0 WORDPRESS PLUGIN
VERSION:2.0
CALSCALE:GREGORIAN
X-WR-TIMEZONE:Etc/GMT
METHOD:PUBLISH
");
			}
echo("BEGIN:VEVENT
SUMMARY;CHARSET=" . get_bloginfo('charset') . ':' . $showdata['calendar_summary'] . "
DESCRIPTION;CHARSET=" . get_bloginfo('charset') . ':' . $showdata['calendar_details'] . "
LOCATION;CHARSET=" . get_bloginfo('charset') . ':' . $showdata['calendar_location'] . "
UID:" . $showdata['calendar_start'] . '-' . $showdata['id'] . '-' . get_bloginfo('admin_email') . "
URL:" . $showdata['permalink'] . "
DTSTART;VALUE=DATE;TZID=Etc/GMT:" . $showdata['calendar_start'] . "
DTEND;VALUE=DATE;TZID=Etc/GMT:" . $showdata['calendar_end'] . "
DTSTAMP:" . date('Ymd') . 'T' . date('his') . 'Z' . "
END:VEVENT
");
		if($count == $total) {
echo("END:VCALENDAR");		
		}
		$count++;
		}
	} 
}