<?php /* Template Name: THSF Workshop
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
			<h5 class="widgettitle"><?php _e('Programme', 'arras') ?></h5>
			<div class="widgetcontent">
		        <center><h2 style="padding:5px;margin:0px"><a href="http://www.k-danse.net/corpusmedia" style="text-decoration:none;">Corpus Media</a></h2></center><br>

                <hr/>
                <a href="http://thsf.tetalab.org"><img src="http://tetalab.org/files/2010/05/Screen-shot-2010-05-17-at-14.19.23.png" width="270px" height="194px" alt="THSF"/></a><br>

                <center><h2 style="padding:5px;margin:0px"><a href="http://tetalab.org/thsf/planning" style="text-decoration:none">Programme</a></h2></center><br>
                			    <center><h3 style="padding:5px;margin:0px"><a href="http://tetalab.org/thsf/thsf-planning-samedi-29-mai" style="text-decoration:none">Samedi 29 Mai</a></h3></center><br>
                			    <center><h3 style="padding:5px;margin:0px"><a href="http://tetalab.org/thsf/thsf-planning-dimanche-30-mai" style="text-decoration:none">Dimanche 30 Mai</a></h3></center><br>
                			    <center><h3 style="padding:5px;margin:0px"><a href="http://tetalab.org/thsf/thsf-planning-ateliers" style="text-decoration:none">Ateliers</a></h3></center><br>
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