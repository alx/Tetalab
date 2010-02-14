<?php /* Template Name: THSF
*/ ?>
<?php get_header(); ?>

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
        
        <div class="entry-content">
		<?php the_content( __('<p>Read the rest of this entry &raquo;</p>', 'arras') ); ?>  
        <?php wp_link_pages(array('before' => __('<p><strong>Pages:</strong> ', 'arras'), 
			'after' => '</p>', 'next_or_number' => 'number')); ?>
		</div>
        
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
			<h5 class="widgettitle"><?php _e('Details', 'arras') ?></h5>
			<div class="widgetcontent">
				<ul>
					<li>Date: 28..30 Mai 2010</li>
					<li>Lieux: Toulouse, France - <a href="http://www.mixart-myrys.org/">Mixart Myrys</a></li>
					<li>Contact: <a href="mailto:thsf@lists.tetalab.org">thsf@lists.tetalab.org</a></li>
				</ul>
				
				<iframe width="270" height="190" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://www.openstreetmap.org/export/embed.html?bbox=1.4025,43.5893,1.4656,43.6289&layer=mapnik&marker=43.61868,1.42045" style="border: 1px solid black"></iframe><br /><small><a href="http://www.openstreetmap.org/?lat=43.6091&lon=1.43405&zoom=13&layers=B000FTFTT&mlat=43.61868&mlon=1.42045">View Larger Map</a></small>
				
			</div>
		</li>
		
		<li class="widgetcontainer clearfix">
			<h5 class="widgettitle"><?php _e("Plus d'informations", 'arras') ?></h5>
			<div class="widgetcontent">
				<ul>
					<li><a href="http://hackerspace.net">Hacker Space Festival</a></li>
					<li><a href="http://tetalab.org">Tetalab</a></li>
					<li><a href="http://tmplab.org">/tmp/lab</a></li>
					<li><a href="http://wiki.hacktivistas.net/">hacktivistas.net</a></li>
					<li><a href="http://cadenalibre.net/">CadenaLibre</a></li>
					<li><a href="http://red-sostenible.net/index.php/From_now_on,_Net_and_Freedom">Red Sostenible</a></li>
					<li><a href="http://www.hackerspace.net/appel-a-projets">Hacker Space Festival: Appel à Projets</a></li>
				</ul>
			</div>
		</li>
	</ul>		
</div><!-- #primary -->

<?php get_footer(); ?>