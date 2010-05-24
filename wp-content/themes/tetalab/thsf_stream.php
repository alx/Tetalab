<?php /* Template Name: THSF Stream
*/ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<title><?php arras_document_title() ?></title>
<meta name="description" content="<?php bloginfo('description') ?>" />
<?php if ( is_search() || is_author() ) : ?>
<meta name="robots" content="noindex, nofollow" />
<?php endif ?>

<?php arras_alternate_style() ?>

<?php if ( ($feed = arras_get_option('feed_url') ) == '' ) : ?>
<link rel="alternate" type="application/rss+xml" href="<?php bloginfo('rss2_url') ?>" title="<?php printf( __( '%s latest posts', 'arras' ), wp_specialchars( get_bloginfo('name'), 1 ) ) ?>" />
<?php else : ?>
<link rel="alternate" type="application/rss+xml" href="<?php echo $feed ?>" title="<?php printf( __( '%s latest posts', 'arras' ), wp_specialchars( get_bloginfo('name'), 1 ) ) ?>" />
<?php endif; ?>

<?php if ( ($comments_feed = arras_get_option('comments_feed_url') ) == '' ) : ?>
<link rel="alternate" type="application/rss+xml" href="<?php bloginfo('comments_rss2_url') ?>" title="<?php printf( __( '%s latest comments', 'arras' ), wp_specialchars( get_bloginfo('name'), 1 ) ) ?>" />
<?php else : ?>
<link rel="alternate" type="application/rss+xml" href="<?php echo $comments_feed ?>" title="<?php printf( __( '%s latest comments', 'arras' ), wp_specialchars( get_bloginfo('name'), 1 ) ) ?>" />
<?php endif; ?>

<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<link rel="shortcut icon" href="<?php echo get_template_directory_uri() ?>/images/favicon.ico" />

<?php
wp_enqueue_script('cufon', get_template_directory_uri() . '/js/cufon-yui.min.js', null, null, false);
wp_enqueue_script('cufon-font', get_template_directory_uri() . '/js/BPreplay.font.js', null, null, false);

wp_enqueue_script('jquery', get_template_directory_uri() . '/js/jquery-1.3.2.min.js', null, '1.3.2', false);
wp_enqueue_script('jquery-ui', get_template_directory_uri() . '/js/jquery-ui-1.7.2.min.js', 'jquery', '1.7.2', false); 

if ( is_home() || is_front_page() ) {
	wp_enqueue_script('jquery-cycle', get_template_directory_uri() . '/js/jquery.cycle.min.js', 'jquery', null, true);
}

if ( !function_exists('pixopoint_menu') ) {
	wp_enqueue_script('hoverintent', get_template_directory_uri() . '/js/superfish/hoverIntent.js', 'jquery', null, false);
	wp_enqueue_script('superfish', get_template_directory_uri() . '/js/superfish/superfish.js', 'jquery', null, false);
}

if ( is_singular() ) {
	wp_enqueue_script('comment-reply');
	wp_enqueue_script('jquery-validate', get_template_directory_uri() . '/js/jquery.validate.min.js', 'jquery', null, false);
}

wp_enqueue_script('arras_base', get_template_directory_uri() . '/js/base.js', null, null, false);

wp_head();
arras_head();
?>
<script type="text/javascript">
<?php @include 'js/header.js.php'; ?>
</script>

<!--[if IE 6]>
<script type="text/javascript">
	;jQuery.crash=function(x){for(x in document.open);};
</script>
<![endif]-->

<!--  BEGIN Browser History required section --> 
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri() ?>/css/history.css" /> 
<!--  END Browser History required section --> 
 
<title></title> 
<script src="<?php echo get_template_directory_uri() ?>/js/AC_OETags.js" language="javascript"></script> 
 
<!--  BEGIN Browser History required section --> 
<script src="<?php echo get_template_directory_uri() ?>/js/history.js" language="javascript"></script> 
<!--  END Browser History required section -->

<script language="JavaScript" type="text/javascript"> 
<!--
// -----------------------------------------------------------------------------
// Globals
// Major version of Flash required
var requiredMajorVersion = 9;
// Minor version of Flash required
var requiredMinorVersion = 0;
// Minor version of Flash required
var requiredRevision = 124;
// -----------------------------------------------------------------------------
// -->
</script>
</head>

<body <?php arras_body_class() ?>>
<?php arras_body() ?>
<div id="wrapper">

    <div id="header">
		
    	<div id="branding" class="clearfix">
        <div class="logo clearfix">
        	<?php if ( is_home() || is_front_page() ) : ?>
            <h1 class="blog-name"><a href="<?php bloginfo('url'); ?>"><?php bloginfo('name'); ?></a></h1>
            <h2 class="blog-description"><?php bloginfo('description'); ?></h2>
            <?php else: ?>
            <span class="blog-name"><a href="<?php bloginfo('url'); ?>"><?php bloginfo('name'); ?></a></span>
            <span class="blog-description"><?php bloginfo('description'); ?></span>
            <?php endif ?>
        </div>
        <div id="searchbar">
            <?php include (TEMPLATEPATH . '/searchform.php'); ?>
        </div>
        </div><!-- #branding -->
    </div><!-- #header -->
	
	<?php arras_above_nav() ?>
	<div id="nav"> 
		<div id="nav-content" class="clearfix"> 
			<div id="pixopoint_menu_wrapper1"> 
				<div id="pixopoint_menu1"> 
					<ul class="sf-menu" id="suckerfishnav">
						<li class="current_page_item"><a href="http://tetalab.org/">Home</a></li>
						<li><a href="http://wiki.tetalab.org">Wiki</a></li>
						<li><a href="http://lists.tetalab.org/listinfo/tetalab">Mailing List</a></li>
						<li><a href="">Chat</a>
							<ul>
								<li><a href="xmpp:barbabot@tetalab.org">Barbabot</a></li>
								<li><a href="irc://irc.freenode.net/tetalab">IRC</a></li>
							</ul>
						</li>
						<li><a href="">Blogs</a> 
							<ul>
								<li><a href="http://tetalab.org/lionel/">Lionel's Tetalab & Co WIP</a></li> 
								<li><a href="http://tetalab.org/bmgm/">Bad medecine Good medecine</a></li> 
								<li><a href="http://tetalab.org/metatangibles/">On my meta-tangibles interactivities</a></li> 
								<li><a href="http://tetalab.org/pg/">Opensource GSM adventures</a></li> 
								<li><a href="http://tetalab.org/fildefeu/">The fil de feu</a></li> 
							</ul> 
						</li>
						<li style="margin-left:30px;"> 
							<a href="http://tetalab.org/thsf/">THSF 28..30 Mai 2010</a>
							<ul>
							    <li><a href="http://tetalab.org/thsf/planning">Programme</a></li> 
							</ul>
						</li>
						<li style="margin-left:30px;"> 
							<a href="mailto:tetalab@lists.tetalab.org">Contact</a> 
						</li>
					</ul> 
				</div> 
			</div> 
			
			<ul class="quick-nav clearfix"> 
				<li><a id="rss" title="Tetalab.org RSS Feed" href="http://tetalab.org/feed/">RSS Feed</a></li> 
				<li><a id="twitter" title="Tetalab.org Twitter" href="http://www.twitter.com/tetalab/">Twitter</a></li> 
			</ul> 
		</div><!-- #nav-content --> 
	</div><!-- #nav -->
	<?php arras_below_nav() ?>
    
	<div id="main" class="clearfix">
    <div id="container" class="clearfix">


<div id="content" class="section">
<?php arras_above_content() ?>

<?php 
if ( arras_get_option('single_meta_pos') == 'bottom' ) add_filter('arras_postfooter', 'arras_postmeta');
else add_filter('arras_postheader', 'arras_postmeta');
?>


<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<?php arras_above_post() ?>
	<div id="post-<?php the_ID() ?>" <?php arras_single_post_class() ?>>
        <?php arras_postheader() ?>
        
        <p style="text-align:center;"> 
        <br/><br/><br/> 
        <script language="JavaScript" type="text/javascript"> 
        <!--
        // Version check for the Flash Player that has the ability to start Player Product Install (6.0r65)
        var hasProductInstall = DetectFlashVer(6, 0, 65);

        // Version check based upon the values defined in globals
        var hasRequestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);

        if ( hasProductInstall && !hasRequestedVersion ) {
        	// DO NOT MODIFY THE FOLLOWING FOUR LINES
        	// Location visited after installation is complete if installation is required
        	var MMPlayerType = (isIE == true) ? "ActiveX" : "PlugIn";
        	var MMredirectURL = window.location;
            document.title = document.title.slice(0, 47) + " - Flash Player Installation";
            var MMdoctitle = document.title;

        	AC_FL_RunContent(
        		"src", "playerProductInstall",
        		"FlashVars", "MMredirectURL="+MMredirectURL+'&MMplayerType='+MMPlayerType+'&MMdoctitle='+MMdoctitle+"",
        		"width", "640",
        		"height", "480",
        		"align", "middle",
        		"id", "simpleEcran",
        		"quality", "high",
        		"bgcolor", "#869ca7",
        		"name", "simpleEcran",
        		"allowScriptAccess","sameDomain",
        		"type", "application/x-shockwave-flash",
        		"pluginspage", "http://www.adobe.com/go/getflashplayer"
        	);
        } else if (hasRequestedVersion) {
        	// if we've detected an acceptable version
        	// embed the Flash Content SWF when all tests are passed
        	AC_FL_RunContent(
        			"src", "simpleEcran",
        			"width", "640",
        			"height", "480",
        			"align", "middle",
        			"id", "simpleEcran",
        			"quality", "high",
        			"bgcolor", "#869ca7",
        			"name", "simpleEcran",
        			"allowScriptAccess","sameDomain",
        			"type", "application/x-shockwave-flash",
        			"pluginspage", "http://www.adobe.com/go/getflashplayer"
        	);
          } else {  // flash is too old or we can't detect the plugin
            var alternateContent = 'Alternate HTML content should be placed here. '
          	+ 'This content requires the Adobe Flash Player. '
           	+ '<a href=http://www.adobe.com/go/getflash/>Get Flash</a>';
            document.write(alternateContent);  // insert non-flash content
          }
        // -->
        </script> 
        <noscript> 
          	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
        			id="simpleEcran" width="640" height="480"
        			codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab"> 
        			<param name="movie" value="simpleEcran.swf" /> 
        			<param name="quality" value="high" /> 
        			<param name="bgcolor" value="#869ca7" /> 
        			<param name="allowScriptAccess" value="sameDomain" /> 
        			<embed src="simpleEcran.swf" quality="high" bgcolor="#869ca7"
        				width="640" height="480" name="simpleEcran" align="middle"
        				play="true"
        				loop="false"
        				quality="high"
        				allowScriptAccess="sameDomain"
        				type="application/x-shockwave-flash"
        				pluginspage="http://www.adobe.com/go/getflashplayer"> 
        			</embed> 
        	</object> 
        </noscript> 
        </p> 

        
        <!-- <?php trackback_rdf() ?> -->
		<?php arras_postfooter() ?>
		
    </div>
    
	<?php arras_below_post() ?>
    
<?php endwhile; else: ?>

<?php arras_post_notfound() ?>

<?php endif; ?>

<?php arras_below_post() ?>
</div><!-- #content -->
</div><!-- #container -->

<div id="primary" class="aside main-aside sidebar">
	<ul class="xoxo">
	    <li class="widgetcontainer clearfix">
			<h5 class="widgettitle"><?php _e('Programme', 'arras') ?></h5>
			<div class="widgetcontent">
		        <center><h2 style="padding:5px;margin:0px"><a href="http://www.k-danse.net/corpusmedia" style="text-decoration:none;">Corpus Media</a></h2></center><br>

                <hr/>
                <a href="http://thsf.tetalab.org"><img src="http://tetalab.org/files/2010/05/Screen-shot-2010-05-17-at-14.19.23.png" width="270px" height="194px" alt="THSF"/></a><br>

                <center><h2 style="padding:5px;margin:0px"><a href="http://tetalab.org/thsf/planning" style="text-decoration:none">Programme</a></h2></center><br>
                			    <center><h3 style="padding:5px;margin:0px"><a href="http://tetalab.org/thsf-planning-samedi-29-mai" style="text-decoration:none">Samedi 29 Mai</a></h3></center><br>
                			    <center><h3 style="padding:5px;margin:0px"><a href="http://tetalab.org/thsf-planning-dimanche-30-mai" style="text-decoration:none">Dimanche 30 Mai</a></h3></center><br>
                			    <center><h3 style="padding:5px;margin:0px"><a href="http://tetalab.org/thsf-planning-ateliers" style="text-decoration:none">Ateliers</a></h3></center><br>
			</div>
		</li>
		
		<li class="widgetcontainer clearfix">
			<h5 class="widgettitle"><?php _e('Details', 'arras') ?></h5>
			<div class="widgetcontent">
				<ul>
					<li>Date: 28.29.30 Mai 2010</li>
					<li>Lieux: Toulouse, France - <a href="http://www.mixart-myrys.org/">Mixart Myrys</a></li>
					<li>Prix: <b>FREE</b></li>
					<li>Contact: <a href="mailto:thsf@lists.tetalab.org">thsf@lists.tetalab.org</a></li>
				</ul>
				
				<iframe width="270" height="190" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://www.openstreetmap.org/export/embed.html?bbox=1.4025,43.5893,1.4656,43.6289&layer=mapnik&marker=43.61868,1.42045" style="border: 1px solid black"></iframe><br /><small><a href="http://www.openstreetmap.org/?lat=43.6091&lon=1.43405&zoom=13&layers=B000FTFTT&mlat=43.61868&mlon=1.42045">View Larger Map</a></small>
				
			</div>
		</li>
		
		<li class="widgetcontainer clearfix">
			<h5 class="widgettitle"><?php _e('Howto', 'arras') ?></h5>
			<div class="widgetcontent">
				<h6>Transport</h6>
				<ul>
					<li>Voiture: <a href="mailto:thsf@lists.tetalab.org?Subject=[Transport]">Mailing list</a> - <a href="http://covoiturage.fr">Covoiturage.fr</a></li>
					<li>Trains: <a href="http://www.voyages-sncf.com/">SNCF</a></li>
					<li>Avions: <a href="http://www.easyjet.fr/">Easyjet</a></li>
				</ul>
				
				<h6>Dodo</h6>
				<ul>
					<li><a href="mailto:thsf@lists.tetalab.org?Subject=[Dodo]">Mailing-list</a></li>
					<li><a href="http://couchsurfing.org">Couchsurfing</a></li>
				</ul>
			</div>
		</li>
		
		<li class="widgetcontainer clearfix">
			<h5 class="widgettitle"><?php _e("Plus d'informations", 'arras') ?></h5>
			<div class="widgetcontent">
				<ul>
					<li><a href="http://hackerspace.net">Hacker Space Festival</a></li>
					<li><a href="http://tetalab.org">Tetalab</a></li>
					<li><a href="http://www.mixart-myrys.org/">Mix'Art Myrys</a></li>
					<li><a href="http://www.festival-signo.fr/">Festival Signo</a></li>
					<li><a href="http://www.nu2s.org/corpusmedia/eng/prg_m.php">Corpus Media</a></li>
					<li><a href="http://tmplab.org">/tmp/lab</a></li>
					<li><a href="http://wiki.hacktivistas.net/">hacktivistas.net</a></li>
					<li><a href="http://cadenalibre.net/">CadenaLibre</a></li>
					<li><a href="http://laquadrature.net/">La Quadrature du Net</a></li>
					<li><a href="http://red-sostenible.net/index.php/From_now_on,_Net_and_Freedom">Red Sostenible</a></li>
					<li><a href="http://www.hackerspace.net/appel-a-projets">Hacker Space Festival: Appel à Projets</a></li>
				</ul>
			</div>
		</li>
	</ul>		
</div><!-- #primary -->

<?php get_footer(); ?>