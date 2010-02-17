<?php get_header(); ?>

<?php
if ( function_exists('dsq_comment_count') ) {
	remove_action('loop_end', 'dsq_comment_count');
	add_action('arras_above_index_news_post', 'dsq_comment_count');
}

$stickies = get_option('sticky_posts');
?>

<div id="content" class="section">
<?php arras_above_content() ?>

<?php if ( ( $featured1_cat = arras_get_option('slideshow_cat') ) !== '' && $featured1_cat != '-1' ) : ?>
    <!-- Featured Slideshow -->
    <div class="featured clearfix">
    <?php
	if ($featured1_cat == '-5') {
		if (count($stickies) > 0) 
			$query = array('post__in' => $stickies, 'showposts' => arras_get_option('slideshow_count') );
	} elseif ($featured1_cat == '0') {
		$query = 'showposts=' . arras_get_option('slideshow_count');
	} else {
		$query = 'showposts=' . arras_get_option('slideshow_count') . '&cat=' . $featured1_cat;
	}
	
	$q = new WP_Query( apply_filters('arras_slideshow_query', $query) );
	?> 
    	<div id="controls" style="display: none;">
			<a href="" class="prev"><?php _e('Prev', 'arras') ?></a>
			<a href="" class="next"><?php _e('Next', 'arras') ?></a>
        </div>
    	<div id="featured-slideshow">
        	<?php $count = 0; ?>
    		<?php if ($q->have_posts()) : while ($q->have_posts()) : $q->the_post(); ?>
    		<div <?php if ($count != 0) echo 'style="display: none"'; ?>>

            	<a class="featured-article" href="<?php echo wpmu_link(); ?>" rel="bookmark" style="background: url(<?php echo arras_get_thumbnail('featured-slideshow-thumb'); ?>) no-repeat #1E1B1A;">
                <span class="featured-entry">
                    <span class="entry-title"><?php the_title(); ?></span>
                    <span class="entry-summary"><?php echo arras_strip_content(get_the_excerpt(), 20); ?></span>
					<span class="progress"></span>
                </span>
            	</a>
        	</div>
    		<?php $count++; endwhile; endif; ?>
    	</div>
    </div>
<?php endif; ?>

<!-- Featured Articles -->
<?php if (!$paged) : if ( ($featured2_cat = arras_get_option('featured_cat') ) !== '' && $featured2_cat != '-1' ) : ?>
<div id="index-featured">
	<div class="video-link"><a href="http://vimeo.com/groups/tetalab/videos">Plus de Videos &#x2192;</a></div>
	<div class="home-title">Videos</div>
	<?php get_video_posts(); ?>
</div><!-- #index-featured -->

<div id="index-tetaglobule">
	<div class="video-link"><a href="http://vimeo.com/groups/tetalab/videos">Plus de Videos &#x2192;</a></div>
	<div class="home-title">Videos</div>
	<?php get_video_posts('tetaglobule'); ?>
</div><!-- #index-tetaglobule -->
<?php endif; endif; ?>


<?php arras_above_index_news_post() ?>

<!-- News Articles -->
<div id="index-news">
<div class="home-title"><?php _e('Latest Headlines', 'arras') ?></div>
<?php
$news_query = array(
	'cat' => arras_get_option('news_cat'),
	'paged' => $paged,
	'showposts' => ( (arras_get_option('index_count') == 0 ? get_option('posts_per_page') : arras_get_option('index_count')) )
);

// if you are a WP plugin freak you can use 'arras_news_query' filter to override the query
wp_reset_query(); query_posts(apply_filters('arras_news_query', $news_query));

arras_get_posts('news') ?>

<?php if(function_exists('wp_pagenavi')) wp_pagenavi(); else { ?>
	<div class="navigation clearfix">
		<div class="floatleft"><?php next_posts_link( __('Older Entries', 'arras') ) ?></div>
		<div class="floatright"><?php previous_posts_link( __('Newer Entries', 'arras') ) ?></div>
	</div>
<?php } ?>

</div><!-- #index-news -->

<?php arras_below_index_news_post() ?>

<?php $sidebars = wp_get_sidebars_widgets(); ?>

<div id="bottom-content-1">
	<?php if ( $sidebars['sidebar-4'] ) : ?>
	<ul class="clearfix xoxo">
    	<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Bottom Content #1') ) : ?>
        <?php endif; ?>
	</ul>
	<?php endif; ?>
</div>

<div id="bottom-content-2">
	<?php if ( $sidebars['sidebar-5'] ) : ?>
	<ul class="clearfix xoxo">
    	<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Bottom Content #2') ) : ?>
        <?php endif; ?>
	</ul>
	<?php endif; ?>
</div>

<?php arras_below_content() ?>
</div><!-- #content -->
    
<?php get_sidebar(); ?>
<?php get_footer(); ?>