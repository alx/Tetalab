<?php
/**
 * WordPress Administration Template Footer
 *
 * @package WordPress
 * @subpackage Administration
 */

// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>

<div class="clear"></div></div><!-- wpbody-content -->
<div class="clear"></div></div><!-- wpbody -->
<div class="clear"></div></div><!-- wpcontent -->
</div><!-- wpwrap -->

<div id="footer">
<p id="footer-left" class="alignleft"><?php
do_action( 'in_admin_footer' );
$upgrade = '';
$footer_text = __('Thank you for creating with <a href="http://mu.wordpress.org/">WordPress MU</a>');
if( is_site_admin() ) {
	$upgrade = apply_filters( 'update_footer', '' );
	$footer_text .= ' ' . $wpmu_version;
}
$footer_text .= ' | ' . __('<a href="http://mu.wordpress.org/docs/">Documentation</a>');
echo apply_filters( 'admin_footer_text', '<span id="footer-thankyou">' . $footer_text . '</span>' );
?></p>
<p id="footer-upgrade" class="alignright"><?php echo $upgrade; ?></p>
<div class="clear"></div>
</div>
<?php
do_action('admin_footer', '');
do_action('admin_print_footer_scripts');
do_action("admin_footer-$hook_suffix");

// get_site_option() won't exist when auto upgrading from <= 2.7
if ( function_exists('get_site_option') ) {
	if ( false === get_site_option('can_compress_scripts') )
		compression_test();
}

?>

<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
</body>
</html>
