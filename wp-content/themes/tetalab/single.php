<?php get_header(); ?>

<div id="content" class="section">
<?php arras_above_content() ?>

<?php 
if ( arras_get_option('single_meta_pos') == 'bottom' ) add_filter('arras_postfooter', 'arras_postmeta');
else add_filter('arras_postheader', 'arras_postmeta');
?>


<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<?php arras_above_post() ?>
	
	<div class="featured clearfix">
		<div id="featured-slideshow">
			<div>
				<a class="featured-article" href="<?php echo wpmu_link(); ?>" rel="bookmark" style="background: url(<?php echo arras_get_thumbnail('featured-slideshow-thumb'); ?>) no-repeat #1E1B1A;">
					<span class="featured-entry">
						<span class="entry-title"><?php the_title(); ?></span>
						<span class="progress"></span>
					</span>
				</a>
			</div>
		</div>
	</div>
	
	<div id="post-<?php the_ID() ?>" <?php arras_single_post_class() ?>>
        
        <div class="entry-content">
		<?php the_content( __('<p>Read the rest of this entry &raquo;</p>', 'arras') ); ?>  
        <?php wp_link_pages(array('before' => __('<p><strong>Pages:</strong> ', 'arras'), 
			'after' => '</p>', 'next_or_number' => 'number')); ?>
		</div>
        
        <!-- <?php trackback_rdf() ?> -->
		<?php arras_postfooter() ?>
    </div>
    
	<?php arras_below_post() ?>
	<a name="comments"></a>
    <?php comments_template('', true); ?>
	<?php arras_below_comments() ?>
    
<?php endwhile; else: ?>

<?php arras_post_notfound() ?>

<?php endif; ?>

<?php arras_below_content() ?>
</div><!-- #content -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>