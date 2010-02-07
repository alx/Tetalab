<?php

class PhotoQPhoto extends PhotoQObject
{
	
	/**
	 * The name of the custom/meta field used for the photo description
	 *
	 * @var string
	 * @access private
	 * 
	 */
	var $_descrFieldName = 'photoQDescr';
	var $_pathFieldName = 'photoQPath';
	var $_exifFullFieldName = 'photoQExifFull';
	var $_exifFieldName = 'photoQExif';
	var $_sizesFieldName = 'photoQImageSizes';
	var $_DEFAULT_VIEWS = array('content', 'excerpt');
	
	/**
	 * Reference to OptionControllor singleton
	 * @var object PhotoQOptionController
	 */
	var $_oc;
	
	/**
	 * Reference to PhotoQDB singleton
	 * @var object PhotoQDB
	 */
	var $_db;
	
	/**
	 * Reference to ErrorStack singleton
	 * @var object PEAR_ErrorStack
	 */
	var $_errStack;
	
	var $_sizes = array();
	var $_path;
	var $_width;
	var $_height;
	var $_yearMonthDir;
	
	/**
	 * The tag names of this photos. Now an array instead of comma separated list
	 * as this is often easier to handle
	 * @var array
	 */
	var $_tags;
	
	var $id;
	var $title;
	var $descr;
	var $imgname;
	var $exif;
		

	/**
	 * PHP5 type constructor
	 */
	function __construct($id, $title, $descr, $exif, $path, $imgname, 
		$tags = '', $slug = '', $edited = false)
	{
		
		//get the PhotoQ error stack for easy access
		$this->_errStack = &PEAR_ErrorStack::singleton('PhotoQ');
		
		//get the other singletons
		$this->_oc =& PhotoQSingleton::getInstance('PhotoQOptionController');
		$this->_db =& PhotoQSingleton::getInstance('PhotoQDB');
		
		
		$this->id = $id;
		$this->imgname = $imgname;
		$this->_tags = $tags;
		
		$this->title = $title;
		$this->descr = $descr;
		$this->exif = maybe_unserialize($exif);
		
		if(empty($path))
			$this->_path = $this->_oc->getQDir() . $this->imgname;
		else
			$this->_path = $path;

		//mute this one because it can issue warnings if the original does not exist.
		@$this->initImageSizes();
		
	}
	
	/**
	 * Use this one (factory pattern) to create instances of this class. 
	 * Returns NULL if there was an error creating the object
	 * @param unknown_type $name
	 */
	/*function &createInstance($class, $id, $title, $descr = '', $exif = '', 
		$path = '', $imgname = '', $slug = '', $tags = '', $edited = false)
	{
		
		// If the class exists, return a new instance of it.
        if (class_exists($class)) {
            $obj = &new $class($id, $title, $descr, $exif, $path, $imgname, $slug, $tags, $edited );
            //here come the init functions that can go wrong and produce errors
            if(@$obj->initImageSizes())
            	return $obj;
        }
		
        return NULL;
    }*/
	
	/**
	 * we move this function out of the constructor because it can fail. The clean way would
	 * be to throw an exception which is not possible in PHP4. We therefore might have to resort
	 * to a factory method and do error checking there.
	 */
	function initImageSizes(){
		
		if(file_exists($this->_path)){
			//set original width and height
			$imageAttr = getimagesize($this->_path);
			$this->_width = $imageAttr[0];
			$this->_height = $imageAttr[1];
		}else{//we have a problem the photo does not exist at the specified location
			$this->_raisePhotoNotFoundError();
			$this->_width = 0;
			$this->_height = 0;
		}
		//add all the image sizes
		foreach ($this->_oc->getImageSizeNames() as $sizeName){
			$this->_sizes[$sizeName] =& PhotoQImageSize::createInstance($sizeName, $this->imgname, $this->_yearMonthDir, $this->_width, $this->_height);
		}
		//add the original
		$this->_sizes[$this->_oc->ORIGINAL_IDENTIFIER] =& PhotoQImageSize::createInstance($this->_oc->ORIGINAL_IDENTIFIER, $this->imgname, $this->_yearMonthDir, $this->_width, $this->_height);
		
	}
	
	function _raisePhotoNotFoundError(){
		$this->_errStack->push(PHOTOQ_PHOTO_NOT_FOUND,'error', array('title' => $this->title, 'imgname' => $this->imgname, 'path' => $this->_path));
	}
	
	function _raiseSizeNotDefinedError($sizeName){
		$this->_errStack->push(PHOTOQ_SIZE_NOT_DEFINED,'error', array('sizename' => $sizeName));
	}
	
	/**
	 * Deletes image files associated with this photo from the server.
	 *
	 * @return object PhotoQStatusMessage
	 */
	function delete()
	{
		//remove from server
		$deleted = true;
		if(file_exists($this->_path))
			$deleted = unlink($this->_path);
		if(!$deleted)
			$status = new PhotoQErrorMessage(sprintf(__("Could not delete photo %s from server. Please delete manually.", 'PhotoQ'), $this->imgname));
		else
			$status = new PhotoQStatusMessage(__('Entry successfully removed from queue. Corresponding files deleted from server.', 'PhotoQ'));
		return $status;
	}
	
	
	function generateImgTag($sizeName, $class)
	{				
		return '<img width="'.$this->_sizes[$sizeName]->getScaledWidth().'" height="'.$this->_sizes[$sizeName]->getScaledHeight().'" alt="'.$this->title.'" src="'.$this->_sizes[$sizeName]->getUrl().'" class="'.$class.'" />';
	}
	
	function generateImgLink($sourceSizeName, $targetSizeName, $attributes, $class)
	{
		return '<a '. $attributes . ' href="'.$this->_sizes[$targetSizeName]->getUrl().'" title="'.$this->title.'"><img width="'.$this->_sizes[$sourceSizeName]->getScaledWidth().'" height="'.$this->_sizes[$sourceSizeName]->getScaledHeight().'" alt="'.$this->title.'" src="'.$this->_sizes[$sourceSizeName]->getUrl().'" class="'.$class.'" /></a>';
	}
	
	function generateFreeformView($template){
		$result = $template;
		$simpleReplacements = array(
			'title' => $this->title,
			'descr' => $this->descr,
			'exif' => $this->getNiceExif(),
			//'tags' => $this->getTagString()
		);
		
		//handle the meta fields
		$fields = $this->_db->getAllFields();
		foreach ($fields as $field) {
			$simpleReplacements[$field->q_field_name] = $this->getField($field->q_field_name, $field->q_field_id);
		}
		
		$result = PhotoQHelper::formatShorttags($result, $simpleReplacements);
		
		$sizeReplacements = array('Url', 'Path', 'Width', 'Height');
		//foreach($simpleReplacements as $replKey => $replVal)
		//	$result = preg_replace('/\['.preg_quote($replKey).'\]/', $replVal, $result);
		
		foreach($sizeReplacements as $repl)
			$result = preg_replace_callback('/\[img'.preg_quote($repl).'\|(.+?)\]/', array(&$this, 'get'.$repl.'FromMatchedSize'), $result);
			
		
		return $result;
	}
	
	function getUrlFromMatchedSize($matches){
		if(isset($this->_sizes[$matches[1]]))
			return $this->_sizes[$matches[1]]->getUrl();
		else{
			$this->_raiseSizeNotDefinedError($matches[1]);
			return '';
		}
	}
	
	function getPathFromMatchedSize($matches){
		if(isset($this->_sizes[$matches[1]]))
			return $this->_sizes[$matches[1]]->getPath();
		else{
			$this->_raiseSizeNotDefinedError($matches[1]);
			return '';
		}
	}
	
	function getWidthFromMatchedSize($matches){
		if(isset($this->_sizes[$matches[1]]))
			return $this->_sizes[$matches[1]]->getScaledWidth();
		else{
			$this->_raiseSizeNotDefinedError($matches[1]);
			return '';
		}
	}
	
	function getHeightFromMatchedSize($matches){
		if(isset($this->_sizes[$matches[1]]))
			return $this->_sizes[$matches[1]]->getScaledHeight();
		else{
			$this->_raiseSizeNotDefinedError($matches[1]);
			return '';
		}
	}
	
	
	
	

	
	/**
	 * Generates the data stored in the_content or the_excerpt.
	 *
	 * @param string $viewName the name of the view to generate (content or excerpt).
	 * @return string	the data to be stored.
	 */
	function generateContent($viewName = 'content')
	{
		PhotoQHelper::debug('enter generateContent()');
		$viewType = $this->_oc->getValue($viewName . 'View-type');
		PhotoQHelper::debug('viewName: ' . $viewName. ', viewType: ' . $viewType);
		switch($viewType){

			case 'single':
				$singleSize = $this->_oc->getValue($viewName . 'View-singleSize');
				PhotoQHelper::debug('generateContent('.$viewName.') size: '. $singleSize);
				//if($singleSize != 'main')
				$data = $this->generateImgTag($singleSize, "photoQ$viewName photoQImg");
				break;

			case 'imgLink':
				$sourceSize = $this->_oc->getValue($viewName . 'View-imgLinkSize');
				$targetSize = $this->_oc->getValue($viewName . 'View-imgLinkTargetSize');
				$data = $this->generateImgLink($sourceSize, $targetSize,
					stripslashes(html_entity_decode($this->_oc->getValue($viewName . 'View-imgLinkAttributes'))),
					"photoQ$viewName photoQLinkImg"
				);
				break;
			case 'freeform':
				$data = $this->generateFreeformView(stripslashes(html_entity_decode($this->_oc->getValue($viewName . 'View-freeform'))));
				break;
		}
		
		if($viewName == 'content' && $viewType != 'freeform'){
			if($this->_oc->getValue('inlineDescr'))
				//leave this on separate line or wpautop() will mess up, strange but true...
				$data .= '
				<div class="'.$this->_descrFieldName.'">' . $this->descr . '</div>';
			if($this->_oc->getValue('inlineExif'))
				$data .= $this->getNiceExif();
		}
		
		return $data;
			
	}
	
	function generateSizesField()
	{
		$sizeFieldData = array();
		foreach($this->_sizes as $size){
			$imgTag = $this->generateImgTag($size->getName(), "PhotoQImg");
			$imgUrl = $size->getUrl();
			$imgPath = $size->getPath();
			$imgWidth = $size->getScaledWidth();
			$imgHeight = $size->getScaledHeight();
			$sizeFieldData[$size->getName()] = compact('imgTag', 'imgUrl', 'imgPath', 'imgWidth', 'imgHeight');
		}	
		return $sizeFieldData;
	}
	
	function hasOriginal(){
		return file_exists($this->_path);
	}
	
	
	
	/**
	 * Rebuild the downsized version for a given image size.
	 *
	 * @param object PhotoQImageSize $size
	 * @return boolean
	 */
	function rebuildSize($size, $moveOriginal = true){
		PhotoQHelper::debug('enter rebuildSize()');
		$status = $size->createPhoto($this->_path, $moveOriginal);
		if($status->isError()){//an error occurred
			$status->show();
			$this->cleanUpAfterError();
			PhotoQHelper::debug('leave rebuildSize() with Error');
			return false;
		}
		return true;
	}
	
	function cleanUpAfterError(){
		//move back original if it has been moved already
		$oldPath = $this->_oc->getQDir() . $this->imgname;
		if (!file_exists($oldPath) && file_exists($this->_sizes[$this->_oc->ORIGINAL_IDENTIFIER]->getPath()))
			PhotoQHelper::moveFile($this->_sizes[$this->_oc->ORIGINAL_IDENTIFIER]->getPath(), $oldPath);
		
		//remove any resized images that have been created unless a corresponding original image exists
		
		if(!file_exists($this->_sizes[$this->_oc->ORIGINAL_IDENTIFIER]->getPath())){
			foreach($this->_sizes as $size){
				$size->deleteResizedPhoto();
			}
		}
	}
	
	/**
	 * Rebuild downsized version of an image given the name of the downsized version.
	 *
	 * @param string $sizeName
	 * @return boolean
	 */
	function rebuildByName($sizeName){
		$size = $this->_sizes[$sizeName];
		return $this->rebuildSize($this->_sizes[$sizeName]);
	}
	
	/**
	 * Getter for the image name field
	 * @return string
	 */
	function getName(){
		return $this->imgname;
	}
	
	/**
	 * Getter for the path field
	 * @return string
	 */
	function getPath(){
		return $this->_path;
	}
	
	/**
	 * Getter for the id field
	 * @return int
	 */
	function getId(){
		return $this->id;
	}
	
	/**
	 * Getter for the title field
	 * @return string
	 */
	function getTitle(){
		return $this->title;
	}
	
	/**
	 * Get the customfield with specified name.
	 * @param $name
	 * @param $id
	 * @return unknown_type
	 */
	function getField($name, $id = 0){
		return get_post_meta($this->id, $name, true);
	}
	
	/**
	 * Getter for the descr field
	 * @return string
	 */
	function getDescription(){
		return $this->descr;
	}
	
	function getTagString(){
		return implode(', ', $this->_tags);
	}
	
	/**
	 * Returns the formatted list of Exif data, only containing Exif tags that
	 * were selected in the PhotoQ settings.
	 * @return unknown_type
	 */
	function getNiceExif(){
		$displayOptions = array(
			'before' => stripslashes(html_entity_decode($this->_oc->getValue('exifBefore'))),
			'after' => stripslashes(html_entity_decode($this->_oc->getValue('exifAfter'))),
			'elementBetween' => stripslashes(html_entity_decode($this->_oc->getValue('exifElementBetween'))),
			'elementFormatting' => stripslashes(html_entity_decode($this->_oc->getValue('exifElementFormatting')))
		);
		return PhotoQExif::getFormattedExif(
			$this->exif,
			$this->_oc->getValue('exifTags'),
			array_keys($this->getTagsFromExifKeyValArray()),
			$this->getExifTagsDisplayNameArray(),
			$displayOptions	
		);
	}
	
	/**
	 * Create array of tagsFromExif key value pairs for this photo
	 * @return array
	 */
	function getTagsFromExifKeyValArray(){
		$result = array();
		if(count($this->exif)){
			foreach($this->exif as $key => $value){
				if($this->_oc->getValue($key.'-tag'))
					$result[$key] = $value;
			}
		}
		return $result;
	}
	
	function getExifTagsDisplayNameArray(){
		$result = array();
		if(count($this->exif)){
			foreach($this->exif as $key => $value){
				$result[$key] = $this->_oc->getValue($key.'-displayName');
			}
		}
		return $result;
	}
	
	
	function getTagsFromExifString(){
		return implode(',', array_values($this->getTagsFromExifKeyValArray()));
	}
	
	
	function getAdminThumbURL($width = 200, $height = 90)
	{
		$phpThumbLocation = PhotoQHelper::getRelUrlFromPath(PHOTOQ_PATH.'lib/phpThumb_1.7.9/phpThumb.php?');
		$phpThumbParameters = 'src='.$this->getPath().'&amp;h='.$height.'&amp;w='.$width;
		
		$imagemagickPath = 
			( $this->_oc->getValue('imagemagickPath') ? $this->_oc->getValue('imagemagickPath') : null );
		if($imagemagickPath)
			$phpThumbParameters .= '&amp;impath='.$imagemagickPath;
		//for WPMU we also have to set the cache path via get
		//$phpThumbParameters .= '&amp;cpath='.$this->_oc->getCacheDir();
		return $phpThumbLocation.$phpThumbParameters;
	}
	
	
	
}

class PhotoQQueuedPhoto extends PhotoQPhoto
{
	 
	var $edited; 
	var $_authorID;
	var $_position;
	var $_slug;
	var $_captureDate;
	
	
	/**
	 * PHP5 type constructor
	 */
	function __construct($id, $title, $descr, $exif, $path, $imgname, $tags, 
					$slug, $edited, $authorID, $position, $date)
	{
		
		$this->edited = $edited;
		$this->_position = $position;
		$this->_captureDate = $date;
		$this->_slug = $slug;
		$this->_authorID = $authorID;
		
		$this->_yearMonthDir = mysql2date('Y_m', current_time('mysql')) . "/";
		
		
		parent::__construct($id, $title, $descr, $exif, $path, $imgname, $tags);
		
	}
	
	/**
	 * Getter for the position field
	 * @return int
	 */
	function getPosition(){
		return $this->_position;
	}
	
	/**
	 * Getter for the captureDate field
	 * @return int
	 */
	function getCaptureDate(){
		return $this->_captureDate;
	}
	
	/**
	 * Getter for the edited field
	 * @return boolean
	 */
	function wasEdited(){
		return $this->edited;
	}
	
	function getSlug(){
		return $this->_slug;
	}
	
	function getAuthor(){
		global $user_ID;
		
		$postAuthor = $this->_authorID;
		
		if ( empty($postAuthor) )
			$postAuthor = $user_ID;
			
		//we still didn't get an author -> set it to default
		if ( empty($postAuthor) )
			$postAuthor = $this->_oc->getValue('qPostAuthor');
		return $postAuthor;
	}
	
	
	/**
	 * Return list of ids of selected categories
	 * @return array
	 */
	function getSelectedCats(){
		$selectedCats = array();
		//first check for common info
		if ( isset($_POST['post_category']) )
			$selectedCats = $_POST['post_category'][0];
		else
			$selectedCats = $this->_db->getCategoriesByImgId($this->id);
		 
		return $selectedCats;
	}
	
	/**
	 * Get the customfield with specified id. Overrides the parent 
	 * function because here custom fields are still in the photoq DB.
	 * @param $name the name of the field to fetch
	 * @param $id the id of the field to fetch
	 * @return unknown_type
	 */
	function getField($name, $id = 0){
		return $this->_db->getFieldValue($this->id, $id);
	}

	/**
	 * Shows the edit/enter info form for one photo.
	 *
	 * @param mixed $this	The photo to be edited.
	 */
	function showPhotoEditForm()
	{
		global $current_user;
		//if we have post values (common info) we take those instead of db value.
		$descr = attribute_escape($_POST['img_descr']) ? attribute_escape(stripslashes($_POST['img_descr'])) : $this->getDescription();
		$tags = attribute_escape($_POST['tags_input']) ? attribute_escape(stripslashes($_POST['tags_input'])) : $this->getTagString();
		$selectedAuthor = attribute_escape($_POST['img_author']) ? attribute_escape(stripslashes($_POST['img_author'])) : $this->getAuthor();
		$fullSizeUrl = PhotoQHelper::getRelUrlFromPath($this->getPath());
		
		// output photo information form
		$path = $this->getAdminThumbURL($this->_oc->getValue('photoQAdminThumbs-Width'), 
						$this->_oc->getValue('photoQAdminThumbs-Height'));
						
		
		$authors = get_editable_user_ids( $current_user->id ); 				
		
	?>
		
		<div class="main info_group">
			<div class="info_unit"><a class="img_link" href="<?php echo $fullSizeUrl; ?>" title="Click to see full-size photo" target="_blank"><img src='<?php echo $path; ?>' alt='<?php echo $this->getName(); ?>' /></a></div>
			<div class="info_unit"><label><?php _e('Title','PhotoQ') ?>:</label><br /><input type="text" name="img_title[]" size="30" value="<?php echo $this->getTitle(); ?>" /></div>
			<div class="info_unit"><label><?php _e('Description','PhotoQ') ?>:</label><br /><textarea style="font-size:small;" name="img_descr[]" cols="30" rows="3"><?php echo $descr; ?></textarea></div>
			
			<?php //this makes it retro-compatible
				if(function_exists('get_tags_to_edit')): ?>
			<div class="info_unit"><label><?php _e('Tags (separate multiple tags with commas: cats, pet food, dogs)', 'PhotoQ'); ?>:</label><br /><input type="text" name="tags_input[]" class="tags-input" size="50" value="<?php echo $tags; ?>" /></div>
			<?php endif; ?>
			
			<div class="info_unit"><label><?php _e('Slug','PhotoQ') ?>:</label><br /><input type="text" name="img_slug[]" size="30" value="<?php echo $this->getSlug(); ?>" /></div>
			<div class="info_unit"><label><?php _e('Post Author','PhotoQ') ?>:</label><?php wp_dropdown_users( array('include' => $authors, 'name' => 'img_author[]', 'multi' => true, 'selected' => $selectedAuthor) ); ?></div>
			<input type="hidden" name="img_id[]" value="<?php echo $this->getId(); ?>" />
			<input type="hidden" name="img_position[]" value="<?php echo $this->getPosition(); ?>" />
		</div>
		<?php PhotoQHelper::showMetaFieldList($this->getId()); ?>
		<div class="wimpq_cats info_group"><?php PhotoQHelper::showCategoryCheckboxList($this->getId(), $this->_oc->getValue('qPostDefaultCat'), $this->getSelectedCats()); ?></div>
		<div class="clr"></div>
	<?php
		
	}
	
	
	
	
	
	/**
	 * Publish the Photo. Creates the resized images, inserts post data into database
	 *
	 * @return integer	The ID of the post created.
	 */
	function publish($timestamp = 0)
	{
		
		PhotoQHelper::debug('enter publish()');
		
		//create the resized images and move them into position
		foreach($this->_sizes as $size){
			if(!$this->rebuildSize($size)){//an error occurred
				return 0;
			}
		}
		
		PhotoQHelper::debug('thumbs created');
		
		//generate the post data and add it to database
		$postData = $this->_generatePostData($timestamp);
		if (!$postID = wp_insert_post($postData)) { //post did not succeed
			$this->cleanUpAfterError();
			return 0;
		}
		
		PhotoQHelper::debug('post inserted');
		
		//insert description
		add_post_meta($postID, $this->_descrFieldName, $this->descr, true);
		
		//insert full exif
		add_post_meta($postID, $this->_exifFullFieldName, $this->exif, true);
		
		//insert formatted exif
		add_post_meta($postID, $this->_exifFieldName, $this->getNiceExif(), true);
		
		//insert sizesFiled
		add_post_meta($postID, $this->_sizesFieldName, $this->generateSizesField(), true);
		
		//add path variable
		add_post_meta($postID, $this->_pathFieldName, $this->_sizes[$this->_oc->ORIGINAL_IDENTIFIER]->getPath(), true);
	
		//insert custom views
		foreach($this->_oc->getViewNames() as $currentViewName){
			if(!in_array($currentViewName, $this->_DEFAULT_VIEWS)){
				add_post_meta($postID, $currentViewName, $this->generateContent($currentViewName), true);
			}
		}
		
		//handle the other fields
		$fields = $this->_db->getAllFields();
		foreach ($fields as $field) {
			$fieldValue = $this->_db->getFieldValue($this->id, $field->q_field_id);
			add_post_meta($postID, $field->q_field_name, $fieldValue, true);
		}
		
		//increment the counter of photos posted through photoq
		$postedSinceLastReminder = get_option('wimpq_posted_since_reminded');
		if($postedSinceLastReminder != NULL)
			update_option('wimpq_posted_since_reminded',$postedSinceLastReminder+1);
		else{
			add_option('wimpq_posted_since_reminded', 1);
			add_option('wimpq_reminder_threshold', 50);
			add_option('wimpq_last_reminder_reset', time());
		}	
		PhotoQHelper::debug('leave publish()');
		
		return $postID;
					
	}
	
	
	function _raisePhotoNotFoundError(){
		$this->_errStack->push(PHOTOQ_QUEUED_PHOTO_NOT_FOUND,'error', array('title' => $this->title, 'imgname' => $this->imgname, 'path' => $this->_path));
	}
	
	
	
	
	
	
	
	function _generatePostData($timestamp){
		
		$post_author = $this->getAuthor();
		$post_status = $this->_oc->getValue('qPostStatus');
		$post_title = $this->title;
	
		//if a timestamp is given we set the post_date
		if($timestamp)
			$post_date = gmdate( 'Y-m-d H:i:s' , $timestamp );
		
		//the slug
		$post_name =  $this->slug;
	
		//the tags
		$tags_input =  rtrim($this->getTagString() . ',' . $this->getTagsFromExifString(),',');
	
		//category stuff
		$post_category = $this->_db->getCategoriesByImgId($this->id);
	
		// Make sure we set a valid category
		if (0 == count($post_category) || !is_array($post_category)) {
			$post_category = array($this->_oc->getValue('qPostDefaultCat'));
		}

		$varNames = array();
		if($this->_oc->isManaged('content')){
			$post_content = $this->generateContent();
			array_push($varNames,'post_content');
		}
		if($this->_oc->isManaged('excerpt')){
			$post_excerpt = $this->generateContent('excerpt');
			array_push($varNames,'post_excerpt');
		}
			
		
		$postData = compact($varNames, 'post_category','post_title','post_name','post_author', 'post_status', 'tags_input', 'post_date');
		//to safely insert values into db
		$postData = add_magic_quotes($postData);
		
		
		return $postData;
		
	}
	
	


}


class PhotoQPublishedPhoto extends PhotoQPhoto
{


	/**
	 * PHP5 type constructor
	 */
	function __construct($postID, $title, $descr = '', $exif = '', $path = '', $imgname = '', 
		$tags = '', $slug = '', $edited = false)
	{
		if(empty($path)) $path = get_post_meta($postID, $this->_pathFieldName, true);
		if(empty($descr)) $descr = get_post_meta($postID, $this->_descrFieldName, true);
		if(empty($exif)) $exif = get_post_meta($postID, $this->_exifFullFieldName, true);
		
		//read ymd and imgname from path
		$imgname = basename($path);
		$this->_yearMonthDir = array_pop(explode('/', dirname($path))) . "/";
		parent::__construct($postID, $title, $descr, $exif, $path, $imgname, $tags);
	}
	
	/**
	 * For published photos we also delete the thumbs.
	 *
	 */
	function delete()
	{
		foreach($this->_sizes as $size){
			$size->deleteResizedPhoto();
		}
		parent::delete();
	}

	/**
	 * Rebuild the entire post and all the thumbs of a published photo.
	 * @param $changedSizes
	 * @param $updateExif
	 * @param $changedViews
	 * @param $updateOriginalFolder
	 * @param $oldFolder
	 * @param $newFolder
	 * @return unknown_type
	 */
	function rebuild($changedSizes, $updateExif = true, $changedViews = array(),
		$updateOriginalFolder = false, $oldFolder = '', $newFolder = '', 
		$addedTags = array(), $deletedTags = array()){
		PhotoQHelper::debug('updatePath: ' . $oldFolder.', ' . $newFolder);
		if($updateOriginalFolder)
			$this->_updatePath($oldFolder,$newFolder);

		if($this->hasOriginal()){ //make sure it is not null due to an error when creating the photo
			
			foreach ($changedSizes as $changedSize){
				$this->rebuildByName($changedSize);
			}
			
			if(count($changedSizes) || $updateOriginalFolder)
				$this->_updateSizesField();

			//update the tags
			if(!empty($addedTags) || !empty($deletedTags))	
				$this->_updateTags($addedTags,$deletedTags);
				
			//update the formatted exif field
			if($updateExif){
				$this->_updateExif();
			}
			//also update the post content like we do for view changes
			if( $changedViews )
				$this->_updateViews($changedViews);
		}

		//PhotoQHelper::debug('rebuilt: ' . $this->getTitle());
		//PhotoQHelper::debug('path: ' . $this->_sizes['main']->getPath());
		//PhotoQHelper::debug('file exists: ' . file_exists($this->_sizes['main']->getPath()));
	}
	
	function _updateViews($changedViews, $customOnly = false){
		$updatePost = false;
		foreach($changedViews as $currentView){
			if(!in_array($currentView,$this->_DEFAULT_VIEWS)){
				$this->_updateCustomView($currentView);
			}else
				$updatePost = true;
		}
		if($updatePost && !$customOnly)//content or excerpt view changed -> update post
			$this->_updatePost();
	}
	
	/**
	 * Updates the field corresponding to custom view with given name.
	 *
	 */
	function _updateCustomView($name)
	{
		update_post_meta($this->id, $name, $this->generateContent($name));
	}

	/**
	 * Updates the content of an already published photo post.
	 *
	 * @return integer the ID of the post
	 */
	function _updatePost()
	{
		PhotoQHelper::debug('enter _updatePost()');
		$ID = $this->id;
		$varNames = array();
		if($this->_oc->isManaged('content')){
			$post_content = $this->generateContent();
			array_push($varNames,'post_content');
		}
		if($this->_oc->isManaged('excerpt')){
			$post_excerpt = $this->generateContent('excerpt');
			array_push($varNames,'post_excerpt');
		}
		$postData = compact('ID', $varNames);
		$postData = add_magic_quotes($postData);
		$res = wp_update_post($postData);
		//kill revisions
		$this->_db->removeRevisions($ID);	
		return $res;
	}
	
	/**
	 * Update the path replacing $old by $new in path meta field.
	 *
	 * @param string $old
	 * @param string $new
	 */
	function _updatePath($old, $new)
	{
		PhotoQHelper::debug('old: ' . $old . ' new: ' . $new);
		$this->_path = str_replace($old, $new, $this->_path);
		//convert backslashes (windows) to slashes
		$this->_path = str_replace('\\', '/', $this->_path);
		
		$this->imgname = basename($this->_path);
		
		update_post_meta($this->id, $this->_pathFieldName, $this->_path);
		
		//finally we need to re-init the image sizes as the path changed
		$this->initImageSizes();
	}
	
	/**
	 * Updates the tagsFromExif of the current post.
	 *
	 */
	function _updateTags($addedTagNames = array(), $deletedTagNames = array())
	{
		//create value array from name arrays first
		$addedTags = $this->_getExifValueArray($addedTagNames);
		$deletedTags = $this->_getExifValueArray($deletedTagNames);
		PhotoQHelper::debug('added: '. print_r($addedTagNames,true));
		PhotoQHelper::debug('deleted: '. print_r($deletedTagNames,true));
		//make sure we don't have double entries
		$this->_tags = array_unique($this->_tags);
		
		//remove tags that were deleted
		$this->_tags = array_diff($this->_tags, $deletedTags);
		
		//add tags that were added
		$this->_tags = array_unique(array_merge($this->_tags, $addedTags));
		
		//update the tags in the database
		wp_set_post_tags( $this->id, add_magic_quotes($this->_tags) );
		
		PhotoQHelper::debug($this->getName().' tags: ' . implode(',',$this->_tags) );
	}
	
	/**
	 * Updates the formatted exif of an already published photo post.
	 *
	 */
	function _updateExif()
	{
		update_post_meta($this->id, $this->_exifFieldName, $this->getNiceExif());
	}
	
	/**
	 * Helper function for the updateExif function. Takes an array of exif tags (keys)
	 * and returns an array with the corresponding Exif values for the current post.
	 * @param $keys
	 * @return array
	 */
	function _getExifValueArray($keys){
		$result = array();
		foreach($keys as $key){
			if(array_key_exists($key,$this->exif))
				$result[] = $this->exif[$key];
		}
		return $result;
	}
	
	/**
	 * Updates the field containing info on image sizes.
	 *
	 */
	function _updateSizesField()
	{
		update_post_meta($this->id, $this->_sizesFieldName, $this->generateSizesField());
	}
	
	function getOriginalDir()
	{
		return $this->_sizes[$this->_oc->ORIGINAL_IDENTIFIER]->getYearMonthDirPath();
	}
	
	function replaceImage($pathToNewImage){
		//new photo was uploaded, now replace the old one
		$this->delete();
		$this->_updatePath($this->getPath(), $pathToNewImage);
		$this->initImageSizes();

		//get new exif data
		$this->exif = PhotoQExif::readExif($pathToNewImage);
		//update full exif in database
		update_post_meta($this->id, $this->_exifFullFieldName, $this->exif);
		
		//rebuild the whole thing
		$this->rebuild($this->_oc->getImageSizeNames(),true,false);
	}
	
	/**
	 * 
	 * Called whenever a photo post is edited and saved in the wordpress editor but before the
	 * database write. If the content changed, we sync the change to the description custom field 
	 * and put images and stuff back into the_content and the_excerpt.
	 * @param $data	array the data to be written to the database
	 * @return array the updated data
	 */	
	function syncPostUpdateData($data){
		PhotoQHelper::debug('enter syncPostUpdateData()');
		//get the description, add formatting, e.g. replace line breaks with <p>
		$this->descr = apply_filters('the_content', $data['post_content']);
		//sync it with the field
		update_post_meta($this->id, $this->_descrFieldName, $this->descr);
		//put photos back into excerpt and content
		if($this->_oc->isManaged('content'))
			$data['post_content'] = $this->generateContent();
		if($this->_oc->isManaged('excerpt'))
			$data['post_excerpt'] = $this->generateContent('excerpt');
		//update all custom views
		$this->_updateViews($this->_oc->getViewNames(), true);
		PhotoQHelper::debug('leave syncPostUpdateData()');
		return $data;
	}
	
	

	
	/**
	 * Our own little parser as there doesn't seem to be a reasonable one that works
	 * with both PHP4 and PHP5. A bit cumbersome and certainly not nice but it seems
	 * to work.
	 *
	 * @param string $content
	 * @return string
	 */
	function getInlineDescription($content, $className = 'photoQDescr'){
		$descr = '';
		$photoQDescrTagsInnerHTML = array(); 
		$pTags = PhotoQHelper::getHTMLTags('div', $content);
		PhotoQHelper::debug('pTags: ' . print_r($pTags,true));
		
		foreach($pTags as $pTag){
			$matches = array();
			$found = preg_match('#^(<div.*?class="'.$className.'".*?>)#',$pTag,$matches);
			if($found){
				//remove the p start and end tag, the rest is the description.
				array_push($photoQDescrTagsInnerHTML, str_replace($matches[1],'',substr($pTag,0,strlen($pTag)-6)));
			}
		}
		
		PhotoQHelper::debug('photoQDescrTagsInnerHTML: ' . print_r($photoQDescrTagsInnerHTML,true));
		
		//if we have more than one p.photoQDescr tag, it means that there were several
		//lines created in the editor -> wrap each one with a p tag.
		$numDescrTags = count($photoQDescrTagsInnerHTML);
		if($numDescrTags == 1)
			$descr = $photoQDescrTagsInnerHTML[0];
		else
			for ($i = 0; $i < $numDescrTags; $i++){
				if($photoQDescrTagsInnerHTML[$i] !== '')
					$descr .= "<p>$photoQDescrTagsInnerHTML[$i]</p>";
			}
		
		PhotoQHelper::debug('descr:' . $descr);
		return $descr;
	}
	
}

/**
 * A photo published under photoq < 1.5 that needs to be imported.
 *
 */
class PhotoQImportedPhoto extends PhotoQPublishedPhoto
{
	

	/**
	 * PHP5 type constructor
	 */
	function __construct($postID, $title, $descr = '', $path = '', $imgname, 
		$tags = '', $slug = '', $edited = false)
	{
		if(empty($path)) $path = get_post_meta($postID, 'path', true);
		
		//correct the path value if needed. on windows machines we might
		//find ourselves with all backslashes removed
		$path = str_replace(ABSPATH, '', trim($path)); //try to remove standard abspath
		$absNoSlash = str_replace('\\', '', ABSPATH); //create the crippled abspath
		$path = str_replace($absNoSlash, '', trim($path)); //try to remove crippled abspath
		$path = ABSPATH . $path; //add correct abspath
		
		if(empty($descr)) $descr = get_post_meta($postID, 'descr', true);
		
		//if it is still empty, the descr was inlined, we need to get it back
		if(empty($descr)){
			//we are now trying to find the description
			$post = get_post($postID);
			$descr = $this->getInlineDescription($post->post_content, 'photo_description');
		}

		//get the exif information
		$exif = serialize(PhotoQExif::readExif($path));
		
		parent::__construct($postID, $title, $descr, $exif, $path);
	}
	
	
	
	
	
	function upgrade()
	{
		//create the resized images and move them into position
		foreach($this->_sizes as $size){
			if(!$this->rebuildSize($size, false)){//an error occurred
				return 0;
			}
		}
		
		//insert description
		add_post_meta($this->id, $this->_descrFieldName, $this->descr, true);
		
		//insert full exif
		add_post_meta($this->id, $this->_exifFullFieldName, $this->exif, true);
		
		//insert formatted exif
		add_post_meta($this->id, $this->_exifFieldName, $this->getNiceExif(), true);
		
		//insert sizesFiled
		add_post_meta($this->id, $this->_sizesFieldName, $this->generateSizesField(), true);
		
		//add path variable
		add_post_meta($this->id, $this->_pathFieldName, $this->_sizes[$this->_oc->ORIGINAL_IDENTIFIER]->getPath(), true);
		
		//delete old descr and path fields
		delete_post_meta($this->id, 'descr');
		delete_post_meta($this->id, 'path');
		
		//update content and excerpt
		$this->_updatePost();
	}
}

?>