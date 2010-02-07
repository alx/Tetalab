<?php

// first lines to include all the stuff from admin-ajax.php 
define('DOING_AJAX', true);

// let's load WordPress
require_once( 'whoismanu-photoq-wploader.php' );


PhotoQHelper::debug('got ajax call');

if ( !is_user_logged_in() )
	die('-1');

// end stuff from admin-ajax.php

//initialize the queue
$photoq->initQueue(); //lazy initialization of queue

PhotoQHelper::debug('queue initialized');

foreach( $_POST as $key => $value)
	PhotoQHelper::debug("POST $key: $value");


switch ( $_POST['action'] ) :
case 'reorder' :
	check_ajax_referer( 'queueReorder', 'queueReorderNonce' );
	
	if(!current_user_can( 'reorder_photoq' ))
		die(__('You do not have sufficient privileges to perform this task', 'PhotoQ'));
		
	PhotoQHelper::debug('reordering queue');
	PhotoQHelper::debug(sizeof($_POST['photoq']));
	//get length of queue and check that both arrays have same size
	$qLength = $photoq->_queue->getLength();
	sizeof($_POST['photoq']) == $qLength or die('1');
		
	PhotoQHelper::debug('sanity check passed');
	
	global $wpdb;
	for($i=0; $i<$qLength; $i++){
		$currentPhoto =& $photoq->_queue->getQueuedPhoto($i);
		if( $_POST['photoq'][$i] != $currentPhoto->getId() ){			
			$wpdb->query("UPDATE $photoq->QUEUE_TABLE SET q_position = '".($i)."' WHERE q_img_id = '" . attribute_escape($_POST['photoq'][$i]) . "'");
		}
	}
		
	break;


	
case 'edit' :
	global $current_user;
	
	$photoToEdit = $photoq->_queue->getQueuedPhotoById(attribute_escape($_POST['id']));	
	
	//the user tries do something he is not allowed to do
	if ( $current_user->id != $photoToEdit->getAuthor() &&  !current_user_can('edit_others_posts') ){
		die(__('You do not have sufficient privileges to perform this task', 'PhotoQ'));	
	}
	
	
	PhotoQHelper::debug('starting ajax editing');
		
	?>
		<form method="post" enctype="multipart/form-data" action="edit.php?page=whoismanu-photoq.php">	
			<div class="photo_info">
		
	<?php 
	PhotoQHelper::debug('started form');
		if ( function_exists('wp_nonce_field') )
			wp_nonce_field('photoq-saveBatch','saveBatchNonce');
	PhotoQHelper::debug('passed nonce');				
		$photoToEdit->showPhotoEditForm();		
	PhotoQHelper::debug('showed photo');	
	?>
		
				<div class="submit">
					<input type="submit" class="button-primary submit-btn" name="save_batch" value="<?php _e('Save Changes', 'PhotoQ') ?>" />
					<input type="submit" class="button-secondary submit-btn" onclick="window.location = window.location.href;" 
					value="<?php _e('Cancel', 'PhotoQ') ?>" />
				</div>
			</div>
		</form>
	
	<?php
	PhotoQHelper::debug('form over');
	
	break;
	
case 'batchProcessing' :
	check_ajax_referer( "photoq-batchProcess" );
	PhotoQHelper::debug('starting batch with id: '. $_POST['id']);
	$photoqBatchResult = $photoq->executeBatch($_POST['id']);
	
	PhotoQHelper::debug('executed');
	
	$photoqErrMsg = PhotoQErrorHandler::showAllErrorsExcept($photoqErrStack, array(PHOTOQ_QUEUED_PHOTO_NOT_FOUND), false);
	
	echo '{
     "percentage": "'.$photoqBatchResult->getPercentage() * 100 .'",
     "message": "'.$photoqBatchResult->getMessage().'",
     "errorMessage": "'.addslashes($photoqErrMsg).'"
 	}';
	
	break;
	
endswitch;

?>