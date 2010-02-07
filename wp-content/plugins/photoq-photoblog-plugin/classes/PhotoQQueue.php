<?php
class PhotoQQueue extends PhotoQObject
{
	
	/**
	 * The list of queued photos
	 *
	 * @var array
	 * @access private
	 */
	var $_queuedPhotos;
	
	/**
	 * Reference to PhotoQDB singleton
	 * @var object PhotoQDB
	 */
	var $_db;

	/**
	 * Reference to PhotoQOptionController singleton
	 * @var object PhotoQOptionController
	 */
	var $_oc;
	/**
	 * PHP5 type constructor
	 */
	function __construct()
	{
		
		$this->_db =& PhotoQSingleton::getInstance('PhotoQDB');
		$this->_oc =& PhotoQSingleton::getInstance('PhotoQOptionController');
		
		//get Queue from DB
		$this->load();
	}
	
	function load()
	{
		//$timer =& PhotoQSingleton::getInstance('PhotoQTimers');
		//$timer->start('photoQFullExec');
		
		$this->_queuedPhotos = array();
	
		if($results = $this->_db->getQueueByPosition()){
			foreach ($results as $position => $qEntry) {
				//tags are split by commas surrounded by any kind of space character
				$this->addPhoto(new PhotoQQueuedPhoto($qEntry->q_img_id,
						$qEntry->q_title, $qEntry->q_descr, $qEntry->q_exif, '', 
						$qEntry->q_imgname, preg_split("/[\s]*,[\s]*/", $qEntry->q_tags),
						$qEntry->q_slug, $qEntry->q_edited, $qEntry->q_fk_author_id, $position, $qEntry->q_date
					));
			}
		}
		
		//$timer->stop('photoQFullExec');
	}
	
	function addPhoto(&$photo)
	{
		array_push($this->_queuedPhotos, $photo);
	}
	
	/**
	 * Delete a photo from the queue.
	 *
	 * @param int $id the id of the photo to delete
	 * @return object PhotoQStatusMessage
	 */
	function deletePhotoById($id)
	{
		global $current_user;
		foreach($this->_queuedPhotos as $position => $photo) {
    		if($photo->id == $id){
    			//check that user is allowed to delete this one
    			if ( $current_user->id == $photo->getAuthor() ||  current_user_can('delete_others_posts') ){
    			
    				//remove from database
					$this->_db->deleteQueueEntry($id, $position);
        			//remove from queue
    				unset($this->_queuedPhotos[$position]);
        			//remove from server
    				return $photo->delete();
    			}else
    				return new PhotoQErrorMessage(sprintf(__('You do not have privileges to delete: %s', 'PhotoQ'),$id));	
    		}
    	}
    	return new PhotoQErrorMessage(sprintf(__('Could not find photo to delete: %s', 'PhotoQ'),$id));
	}
	
	function deleteAll()
	{
		foreach($this->_queuedPhotos as $position => $photo)
    		$this->deletePhotoById($photo->id);
	}
	
	
	/**
	 * Returns the length of the queue.
	 *
	 * @return integer	The length of the queue.
	 * @access public
	 */
	function getLength()
	{
		return count($this->_queuedPhotos);
	}
	
	/**
	 * Returns the photo at position $pos in the queue.
	 * @param $pos int	the position to retrieve
	 * @return object PhotoQQueuedPhoto
	 */
	function &getQueuedPhoto($pos)
	{
		return $this->_queuedPhotos[$pos];
	}
	
	function &getQueuedPhotoById($id){
		foreach ( array_keys($this->_queuedPhotos) as $position ) {
			$photo =& $this->_queuedPhotos[$position];
    		if($photo->getId() == $id){
    			return $photo;
    		}
    	}
    	return new PhotoQErrorMessage(sprintf(__('Could not find photo with ID: %s', 'PhotoQ'),$id));
	}
	
	function getQueuedUneditedPhotos(){
		$unedited = array();
		foreach ( array_keys($this->_queuedPhotos) as $position ) {
			$photo =& $this->_queuedPhotos[$position];
    		if(!$photo->wasEdited()){
    			array_push($unedited, $photo);
    		}
    	}
    	return $unedited;
	}
	
	
	/**
	 * Publish the top of the queue.
	 *
	 * @return object PhotoQStatusMessage
	 */
	function publishTop()
	{
		PhotoQHelper::debug('enterPublishTop()');
		if($this->getLength() == 0){
			return new PhotoQErrorMessage(__('Queue is empty, nothing to post.', 'PhotoQ'));
		}
		$topPhoto = $this->_queuedPhotos[0];
		if($postID = $topPhoto->publish()){
			PhotoQHelper::debug('publishing ok');
			$this->_postPublishingActions($topPhoto->id,$postID);
			$statusMsg = '<strong>'.__('Your post has been saved.', 'PhotoQ').'</strong> <a href="'. get_permalink( $postID ).'">'.__('View post', 'PhotoQ').'</a> | <a href="post.php?action=edit&amp;post='.$postID.'">'.__('Edit post', 'PhotoQ').'</a>';
			PhotoQHelper::debug('leave PublishTop() returning ok message');
			return new PhotoQStatusMessage($statusMsg);
		}else
			return new PhotoQErrorMessage(__('Publishing Photo did not succeed.', 'PhotoQ'));
	}
	
	/**
	 * Actions that need to be performed after photo is published.
	 * @param $topID
	 * @param $postID
	 * @return unknown_type
	 */
	function _postPublishingActions($topID, $postID){
		$this->_db->deleteQueueEntry($topID, 0);
		//if exif is inlined we already need a rebuild to get the post_tag
		//links needed for the tagsFromExif stuff. These are not available
		//before the post has been posted (and thus the tags registered).
		if($this->_oc->getValue('inlineExif')){
			$photo = &$this->_db->getPublishedPhoto($postID);
			if($photo)
				$photo->rebuild(array(),false,true);
		}
	}
	
	/**
	 * Publish several photos from queue at once.
	 *
	 * @param $num2Post the number of photos to post.
	 * @return object PhotoQStatusMessage
	 */
	function publishMulti($num2Post)
	{
		if($this->getLength() == 0){
			return new PhotoQErrorMessage(__('Queue is empty, nothing to post.', 'PhotoQ'));
		}
		$num2Post = min($this->getLength(), $num2Post);
		
		//we'll increase this timestamp from one post to the next to make sure 
		//that posts are at least spaced by one second otherwise wordpress doesn't 
		//know how to deal with it.
		$postDateFirst = current_time('timestamp');
		
		for ($i = 0; $i<$num2Post; $i++){
			$topPhoto = $this->_queuedPhotos[$i];
			if($postID = $topPhoto->publish($postDateFirst + $i))
				$this->_postPublishingActions($topPhoto->id,$postID);
			else
				return new PhotoQErrorMessage(__('Publishing Photo did not succeed.', 'PhotoQ'));
			
		}
		$statusMsg = '<strong>'.__('Your posts have been saved.', 'PhotoQ').'</strong>';
		return new PhotoQStatusMessage($statusMsg);
	}
	
	/**
	 * Sorts the queue according to the specified criterion
	 * @param $criterion
	 * @return unknown_type
	 */
	function sort($criterion){
		if($criterion === '-1')
			return;
		$this->_db->sortQueue($criterion);
	}
	

	
}
?>