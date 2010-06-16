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
								<li><a href="http://tetalab.org/lionel/">Lionel's Tetalab &amp; Co WIP</a></li> 
								<li><a href="http://tetalab.org/bmgm/">Bad medecine Good medecine</a></li> 
								<li><a href="http://tetalab.org/metatangibles/">On my meta-tangibles interactivities</a></li> 
								<li><a href="http://tetalab.org/pg/">Opensource GSM adventures</a></li> 
								<li><a href="http://tetalab.org/fildefeu/">The fil de feu</a></li>  
								<li><a href="http://tetalab.org/sack/">Sack's lab: Computing for artistic needs
                                </a></li>
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
