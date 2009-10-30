<?php get_header() ?>

<div class="content-header">

</div>

<div id="content">
    <?php if ( bp_single_pic_exist() && bp_single_pic_check_owner()):?>
            <h2><?php bp_single_picture_title() ?></h2>
            <?php do_action( 'template_notices' ) // (error/success feedback) ?>
            <div class="picture-single">
                <img src='<?php bp_picture_view()?>' />
            
                <div class="picture-nav">
                    <div class="prev"><?php previous_picture_link('%link', '&lsaquo;') ?></div>
                    <h3><?php bp_single_picture_description() ?></h3>
                    <?php bp_single_pic_delete_link()?>
                    <div class="next"><?php next_picture_link('%link','&rsaquo;') ?></div>
                </div>
                
            </div>
            <div class="clear"></div>
            <p>&nbsp;</p>
				<div class="picture-wire">
                <?php if ( function_exists('bp_pic_wire_get_post_list') ) : ?>
					<?php bp_pic_wire_get_post_list( bp_single_pic_id(), __( "Picture Wire", 'bp-album'), __( "There are no wire posts",'bp-album'), true, false ) ?>
                <?php endif; ?>
                </div>
    <?php else: ?>

        <?php do_action( 'template_notices' ) // (error/success feedback) ?>
		<div id="message" class="info">
			<p><?php bp_word_or_name( __( "Eigther picture doesn't exist for this user or not for public viewing!", 'bp-album' ), __( "Eigther picture doesn't exist for %s or not for public viewing!", 'bp-album' ) ) ?></p>
		</div>

	<?php endif;?>
</div>

<?php get_footer() ?>