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

<?php get_footer(); ?>