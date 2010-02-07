<?php 
	global $user_ID, $current_user;
	$authors = get_editable_user_ids( $current_user->id );
?>
<div class="wrap">
	<h2><?php _e('Manage PhotoQ - Upload', 'PhotoQ') ?></h2>	
	
	<div id="commonInfo<?php if(isset($_POST['ftp_upload'])) echo 'Ftp'; ?>">		
		<form id="batchedit" method="post" enctype="multipart/form-data" action="edit.php?page=whoismanu-photoq.php">
		<div id="poststuff">
		<h4><?php _e('Enter common info:', 'PhotoQ') ?></h4>
		<div class="photo_info">
		
		<div class="main info_group">
			<div class="info_unit"><label><?php _e('Description', 'PhotoQ') ?>:</label><br /><textarea style="font-size:small;" name="img_descr" id="img_descr" cols="30" rows="3"></textarea></div>
			
			<?php //this makes it retro-compatible
				if(function_exists('get_tags_to_edit')): ?>
			<div class="info_unit"><label><?php _e('Tags (separate multiple tags with commas: cats, pet food, dogs)', 'PhotoQ'); ?>:</label><br /><input type="text" name="tags_input" class="tags-input" id="tags-input" size="50"/></div>
			<?php endif; ?>
			<div class="info_unit"><label><?php _e('Post Author','PhotoQ') ?>:</label><?php wp_dropdown_users( array('include' => $authors, 'name' => 'img_author', 'selected' => $user_ID) ); ?></div>
			
		</div>
		<?php PhotoQHelper::showMetaFieldList(); ?>
		<div class="wimpq_cats info_group">
		<?php PhotoQHelper::showCategoryCheckboxList(); ?>
		</div>
		<br class="clr" />
		
		<?php
			$submitLabel = isset($_POST['ftp_upload']) ? __('Import/Enter Info &raquo;', 'PhotoQ') : __('Enter Info &raquo;', 'PhotoQ');
		?>
		<p style="float: right" class="infobutton">
			<input type="submit" class="button-primary action" name="edit_batch" value="<?php echo $submitLabel; ?>" />
		</p>
		</div>
		<?php if($this->_isFtpUpload()) $this->showFtpFileList(); ?>
		</div>
		</form>
		<div class="clr">&nbsp;</div>
	</div>
	
<?php if(!isset($_POST['ftp_upload'])): ?>
	<form action="edit.php?page=whoismanu-photoq.php" method="post" enctype="multipart/form-data">
		
		<div class="tablenav">
		
			<div class="alignleft actions">
			<?php if($this->_oc->getValue('enableBatchUploads')): ?>
				<div id="flash-browse-button"></div>
			<?php else: ?>
				<input type="file" class="button-secondary" name="Filedata" id="Filedata" />
				<input type="submit" class="button-secondary action" value="Upload"/>
				<input type="hidden" name="batch_upload" value="0">
			<?php endif; ?>
			</div>
			<div class="alignleft actions">
				<input type="button" id="cancelbtn" class="button-secondary action" onclick="cancelUpload()" value="<?php _e('Cancel', 'PhotoQ') ?>" />		
			</div>
			<div class="alignright actions">
			<?php if(!PhotoQHelper::isWPMU() && $this->_oc->getValue('enableFtpUploads')): ?>
				<input type="submit" id="ftpUploadBtn" name="ftp_upload" class="button-secondary action" value="<?php _e('Import from FTP directory...', 'PhotoQ') ?>" />
			<?php endif; ?>
			</div>
		</div>
	</form>
	
	<div id="SWFUploadFileListingFiles"></div>
	
	<br class="clr" />
	
	
	<br /><br />

<?php endif;?>	
		
</div>

