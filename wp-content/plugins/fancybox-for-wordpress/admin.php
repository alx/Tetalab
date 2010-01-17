<?php

function mfbfw_options_page() {

	require_once( FBFW_PATH . '/lib/admin-head.php');

	?>

	<div class="wrap">

	<div id="icon-plugins" class="icon32"></div><h2><?php printf(__('Fancybox for WordPress (version %s)', 'mfbfw'), $settings['version']); ?></h2>

	<br />

	<form method="post" action="options.php" id="options">

		<?php
		
		wp_nonce_field('update-options');
		settings_fields('mfbfw-options');

		?>

		<div id="fbfwTabs">

			<ul>
				<li><a href="#fbfw-info"><?php _e('Info', 'mfbfw'); ?></a></li>
				<li><a href="#fbfw-appearance"><?php _e('Appearance', 'mfbfw'); ?></a></li>
				<li><a href="#fbfw-animations"><?php _e('Animations', 'mfbfw'); ?></a></li>
				<li><a href="#fbfw-behaviour"><?php _e('Behaviour', 'mfbfw'); ?></a></li>
				<li><a href="#fbfw-galleries"><?php _e('Galleries', 'mfbfw'); ?></a></li>
				<li><a href="#fbfw-other"><?php _e('Other', 'mfbfw'); ?></a></li>
				<li><a href="#fbfw-troubleshooting"><?php _e('Troubleshooting', 'mfbfw'); ?></a></li>
				<li><a href="#fbfw-uninstall"><?php _e('Uninstall', 'mfbfw'); ?></a></li>
			</ul>

			<div id="fbfw-info">
			
			<?php require_once ( FBFW_PATH . '/lib/admin-tab-info.php'); ?>
			
			</div>

			<div id="fbfw-appearance">
			
			<?php require_once ( FBFW_PATH . '/lib/admin-tab-appearance.php'); ?>
			
			</div>

			<div id="fbfw-animations">
			
			<?php require_once ( FBFW_PATH . '/lib/admin-tab-animations.php'); ?>
			
			</div>

			<div id="fbfw-behaviour">
			
			<?php require_once ( FBFW_PATH . '/lib/admin-tab-behaviour.php'); ?>
			
			</div>

			<div id="fbfw-galleries">
			
			<?php require_once ( FBFW_PATH . '/lib/admin-tab-galleries.php'); ?>
			
			</div>

			<div id="fbfw-other">
			
			<?php require_once ( FBFW_PATH . '/lib/admin-tab-other.php'); ?>
			
			</div>

			<div id="fbfw-troubleshooting">
			
			<?php require_once ( FBFW_PATH . '/lib/admin-tab-troubleshooting.php'); ?>
			
			</div>

			<div id="fbfw-uninstall">
			
			<?php require_once ( FBFW_PATH . '/lib/admin-tab-uninstall.php'); ?>
			
			</div>

		</div>

		<input type="hidden" name="action" value="update" />

		<p class="submit" style="text-align:center;">
			<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes','mfbfw'); ?>" />
		</p>

	</form>

</div>

<?php } ?>
