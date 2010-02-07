<?php

//photoq only loads in admin section
define('WP_ADMIN', true);

require_once('wp-load.php');

//call cronjob function of photoq plugin
$photoq->cronjob();

?>