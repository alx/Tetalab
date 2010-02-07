<?php 
	PhotoQHelper::debug('manage_page: reached edit-batch panel');
?>

<div class="wrap">
	<h2><?php _e('Manage PhotoQ - Enter Info', 'PhotoQ'); ?></h2>	
<form method="post" enctype="multipart/form-data" action="edit.php?page=whoismanu-photoq.php">	
	
<div id="poststuff">
<?php 
	
	if ( function_exists('wp_nonce_field') )
		wp_nonce_field('photoq-saveBatch','saveBatchNonce');

	PhotoQHelper::debug('manage_page: passed nonce in edit-batch panel');
		
	$photosToEdit = $this->_queue->getQueuedUneditedPhotos();	
	foreach ($photosToEdit as $currentToEdit){
		echo '<div class="photo_info">';
			$currentToEdit->showPhotoEditForm();
		echo '</div>';
	}
	
?>
	</div>
		<div>
			<input type="submit" class="button-primary action" name="save_batch" 
			value="<?php _e('Save Batch Info', 'PhotoQ') ?> &raquo;" />
		</div>
	</form>		
</div> 