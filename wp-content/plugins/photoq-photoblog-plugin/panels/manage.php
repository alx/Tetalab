<div class="wrap">

<form method="post" action="edit.php?page=whoismanu-photoq.php">
<h2><?php _e('Manage PhotoQ', 'PhotoQ') ?></h2>
<div id="poststuff">

<?php
	global $current_user;

	if ( function_exists('wp_nonce_field')){
		wp_nonce_field('photoq-manageQueue', 'manageQueueNonce');
		wp_nonce_field( 'queueReorder', 'queueReorderNonce', false );
	}
	
	//get the length of the queue
	$qLength = $this->_queue->getLength();
	
?> 

<div class="qlen">		
<?php 
	if($qLength)
		printf(__('Number of photos in the queue: %s', 'PhotoQ'), "<b>$qLength</b>");
	else
		_e('Queue empty, why not upload some photos?', 'PhotoQ');
?>
</div>

<div class="tablenav">	
	<div class="alignleft actions">
		<input type="submit" class="button-primary action" name="add_entry"
			value="<?php _e('Add Photos to Queue', 'PhotoQ') ?>" />
	
		<input type="submit" class="button-secondary action" name="clear_queue"
			value="<?php _e('Clear Queue...', 'PhotoQ') ?>"
			onclick="return confirm(
		'<?php _e('Are you sure you want to clear the entire queue?', 'PhotoQ') ?>');" />
	</div>
	
	<div class="alignright actions">
		<?php if(current_user_can( 'reorder_photoq' )): ?>
			<span id="sortOptions"><select name="sort_criterion">
				<option selected="selected" value="-1"><?php _e('Sort Criterion:', 'PhotoQ') ?></option>
				<option value="date_asc"><?php _e('Capture Date (oldest first)', 'PhotoQ') ?></option>
				<option value="date_desc"><?php _e('Capture Date (newest first)', 'PhotoQ') ?></option>
				<option value="title_asc"><?php _e('Title (ascending)', 'PhotoQ') ?></option>
				<option value="title_desc"><?php _e('Title (descending)', 'PhotoQ') ?></option>
				<option value="filename_asc"><?php _e('Filename (ascending)', 'PhotoQ') ?></option>
				<option value="filename_desc"><?php _e('Filename (descending)', 'PhotoQ') ?></option>
				<option value="random"><?php _e('Random', 'PhotoQ') ?></option>
			</select>
			<input id="sort_queue" class="button-secondary action" type="submit" name="sort_queue" value="Sort"/>
			</span>	
		<?php endif; ?>
		
		<?php 
		if ( current_user_can('use_secondary_photoq_post_button') ):
		
			$num2Post = $this->_oc->getValue('postMulti');
			if(is_numeric($num2Post) && $num2Post > 1):
				$btnString = sprintf(__('Post Next %d Photos...','PhotoQ'), $num2Post);
				if($num2Post >= $qLength)
					$btnString = __('Post Whole Queue...', 'PhotoQ');
		?>
		
			<input type="submit" class="button-secondary action" name="post_multi"
			value="<?php echo $btnString; ?>"
			onclick="return confirm(
			'<?php _e('Are you really sure you want to publish the next entries in the queue?', 'PhotoQ') ?>');" />
		<?php 
			endif;
		endif;
		?>
		<?php if ( current_user_can('use_primary_photoq_post_button') ): ?>
		<input type="submit" class="button-secondary action" name="post_first"
			value="<?php _e('Post Top of Queue...', 'PhotoQ') ?>"
			onclick="return confirm('<?php _e('Are you sure you want to publish the first entry of the queue?', 'PhotoQ') ?>');" />
		<?php endif; ?>
	</div>
</div>
<div class="clr"></div>

<?php if($qLength): ?>

	<div id="qHeader" class="thead">
		<div class="qHCol qHPosition"><?php _e('Position', 'PhotoQ') ?></div>
		<div class="qHCol qThumb"><?php _e('Thumbnail', 'PhotoQ') ?></div>
		<div class="qHCol qTitle"><?php _e('Title', 'PhotoQ') ?></div>
		<div class="qHCol qAuthor"><?php _e('Author', 'PhotoQ') ?></div>
		<div class="qHCol qDescr"><?php _e('Description', 'PhotoQ') ?></div>
		<div class="qHCol qDate"><?php _e('Captured', 'PhotoQ') ?></div>
		<div class="qHCol qEdit"></div>
		<div class="qHCol qDelete"></div>
		<div class="clr">&nbsp;</div>
	</div>
	
	
	<ul id="photoq">
	
		<?php
		for ($i = 0; $i < $qLength; $i++){
			//get the i-th photo from the queue
			$currentPhoto =& $this->_queue->getQueuedPhoto($i);
			
			//construct the url to the fullsize image
			$imgUrl = PhotoQHelper::getRelUrlFromPath($currentPhoto->getPath());
	
			$path = $currentPhoto->getAdminThumbURL();		
			
			$deleteLink = 'edit.php?page=whoismanu-photoq.php&action=delete&entry='.$currentPhoto->getId();
			$deleteLink = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($deleteLink, 'photoq-deleteQueueEntry' . $currentPhoto->getId()) : $deleteLink;
				
		?>
	
			<li id="photoq-<?php echo $currentPhoto->getId(); ?>" class='photoqEntry'>
				<div class="qCol qPosition"><?php echo ($i + 1); ?></div>
				<div class="qCol qThumb">
					<a class="img_link" href="<?php echo $imgUrl; ?>" title="<?php _e('Click to see full-size photo', 'PhotoQ') ?>" target="_blank">
						<img src='<?php echo $path; ?>' alt='<?php echo $queue[$i]->q_title; ?>' />
					</a>
				</div>
				<div class="qCol qTitle"><?php echo $currentPhoto->getTitle(); ?></div>
				<div class="qCol qAuthor"><?php $userData = get_userdata($currentPhoto->getAuthor()); echo $userData->display_name; ?></div>
				<div class="qCol qDescr"><?php if($currentPhoto->getDescription()) echo $currentPhoto->getDescription(); else echo "&nbsp;"; ?></div>
				<div class="qCol qDate"><?php echo $currentPhoto->getCaptureDate(); ?></div>
				
				<?php if ( $current_user->id == $currentPhoto->getAuthor() ||  current_user_can('edit_others_posts') ): ?>
				<div class="qCol qEdit">
					<a href="#" onclick="return editQEntry('<?php echo $currentPhoto->getId(); ?>');"><?php _e('Edit', 'PhotoQ') ?></a>
				</div>
				<?php endif; ?>
				<?php if ( $current_user->id == $currentPhoto->getAuthor() ||  current_user_can('delete_others_posts') ): ?>
				<div class="qCol qDelete">
					<a href="<?php echo $deleteLink; ?>" onclick="return confirm('<?php _e('Delete entry? Corresponding image will also be deleted from server?', 'PhotoQ') ?>');"><?php _e('Delete', 'PhotoQ') ?></a>
				</div>
				<?php endif; ?>
				<div class="clr">&nbsp;</div>
			</li>
		<?php 	} //for loop over queue entries ?>
	
	</ul>

<?php endif; //if(qLength) ?>



</div>

</form>

</div>
