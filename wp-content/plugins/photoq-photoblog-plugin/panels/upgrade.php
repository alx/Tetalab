<div class="wrap">
		
		<h2><?php _e('PhotoQ Options', 'PhotoQ'); ?></h2>
			<form method="post" action="options-general.php?page=whoismanu-photoq.php"  enctype="multipart/form-data">		
			
			<h3><?php _e('Upgrade from PhotoQ < 1.5', 'PhotoQ') ?></h3>
			
			<p><?php _e('Please read the instructions before doing the upgrade', 'PhotoQ') ?></p>
			<p><?php _e('And remember: Not saving your wordpress database before might turn out to be one of the most stupid things you did lately.', 'PhotoQ') ?></p>
			<p><?php _e('The following photos will be imported:', 'PhotoQ') ?>
			
		<?php 
			if ( function_exists('wp_nonce_field') )
					wp_nonce_field('wimpq_options-nonce','wimpq_options-nonce');
					
			//get all photo posts, foreach size, rebuild the photo
			$photos = $this->_db->getAllPhotos2Import();
			echo '<br/><br/>'. __('Number of photos to import:', 'PhotoQ') . ' ' . count($photos) . '<br/><ol>';
			foreach ( $photos as $photo ){
				echo '<li>
					title: ' . $photo->title. '<br/> 
					descr: ' . htmlentities($photo->descr) . '<br/> 
					path: ' . htmlentities($photo->_path) . '</li>';			}
		?>
			</ol></p>
		<p class="submit">
			<input type="submit" name="upgradePhotoQ" 
				value="<?php _e('Upgrade', 'PhotoQ') ?> &raquo;" />
		</p>
	</form>
	
	<?php 
	
	$folders = $this->getOldYMFolders();
	
	if(count($photos)==0 && count($folders)): ?>
	
	<form method="post" action="options-general.php?page=whoismanu-photoq.php"  enctype="multipart/form-data">		
			
			<h3><?php _e('Remove old folder structure', 'PhotoQ') ?></h3>
			
			<p><?php _e('Please only do this once you are sure your update was successful.', 'PhotoQ') ?></p>
			<p><?php _e('The following folders will be deleted:', 'PhotoQ') ?><ul>
			
		<?php 
			if ( function_exists('wp_nonce_field') )
					wp_nonce_field('wimpq_options-nonce','wimpq_options-nonce');
			
					foreach( $folders as $folderName)
						echo "<li>$folderName</li>";
			
		?>
			</ul></p>
		<p class="submit">
			<input type="submit" name="removeOldYMFolders" 
				value="<?php _e('Delete Folders', 'PhotoQ') ?> &raquo;" />
		</p>
	</form>
	<?php endif; ?>
	
</div> 