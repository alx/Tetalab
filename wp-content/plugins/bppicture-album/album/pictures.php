<?php get_header() ?>

<div class="content-header">
	
</div>

<div id="content">
	<h2><?php bp_word_or_name( __( "My Pictures", 'bp-album' ), __( "%s's Pictures", 'bp-album' ) ) ?></h2>

    <?php do_action( 'template_notices' ) // (error/success feedback) ?>
    <?php if ( bp_has_pictures() ) : ?>
		<div class="pagination-links" id="pag">
			<?php bp_pictures_pagination_links() ?>
		</div>

        <?php while ( bp_pictures() ) : bp_the_picture(); ?>
			<div class="picture-thumb">

                <a href='<?php bp_picture_view_link() ?>'><img src='<?php bp_picture_small_link() ?>' /></a><br />
                <span class="title"><?php bp_picture_title() ?></span><div class="clear"></div>

			</div>
		<?php endwhile; ?>
		
        
	<?php else: ?>

		<div id="message" class="info">
			<p><?php bp_word_or_name( __( "Why don't you show community, what an amazing collection you have got!", 'bp-album' ), __( "%s hasn't uploaded any public picture yet.", 'bp-album' ) ) ?></p>
		</div>

	<?php endif;?>
</div>

<?php get_footer() ?>