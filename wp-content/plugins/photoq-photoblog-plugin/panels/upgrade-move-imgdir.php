<div class="wrap">
		
		<h2><?php _e('PhotoQ Options', 'PhotoQ'); ?></h2>
			<form method="post" action="options-general.php?page=whoismanu-photoq.php"  enctype="multipart/form-data">		
			
			<h3><?php _e('Move Image Directory to wp-content', 'PhotoQ') ?></h3>
			
			<p>As of PhotoQ 1.5.2, it is assumed that the image directory equals "wp-content". If you used a different
			image directory until now, you can move your image directory to "wp-content" here.</p>
			<p>Please read the instructions before doing the upgrade</p>
			<p>And remember: Not saving your wordpress database before might turn out to be one of the most stupid things you did lately.</p>
			
			<p>The following will be moved from "<?php echo $_POST['oldImgDir'] ?>" to "wp-content":</p><ul>
			
		<?php 
			
			$imgDirContent = $this->_getOldImgDirContent( $this->_getOldImgDir($_POST['oldImgDir']) );
			
					foreach( $imgDirContent as $folderName)
						echo "<li>$folderName</li>";
			
		?>
			</ul>
			
		<?php if ( function_exists('wp_nonce_field') )
					wp_nonce_field('photoq-updateOptions');
					
		 ?>
		 
		 <input type="hidden" name="oldImgDir" id="oldImgDir" value="<?php echo $_POST['oldImgDir'] ?>" />
		<p class="submit">
			<input type="submit" name="moveOldImgDir" 
				value="<?php _e('Move', 'PhotoQ') ?> &raquo;" />
		</p>
	</form>
	
	
	
</div> 