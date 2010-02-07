<?php
/**
 * @package PhotoQ
 */


/**
 * The PhotoQ:: class is mainly a wrapper for the PhotoQ WordPress Photoblog Plugin.
 * By grouping everything inside this class, we prevent name clashes with built-in
 * WordPress functions and other WordPress plugins.
 *
 * @author  M.Flury
 * @package PhotoQ
 */
class PhotoQ extends PhotoQObject
{

	/**
	 * The current version of PhotoQ
	 *
	 * @var string
	 * @access private
	 */
	var $_version = '1.8.3';
	
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
	 * The queue that uploaded photos
	 *
	 * @var object
	 * @access private
	 */
	var $_queue;
	
	/**
	 * Reference to ErrorStack singleton
	 * @var object PEAR_ErrorStack
	 */
	var $_errStack;
	
	/**
	 * Holds the directory to which the next upload is directed.
	 * @var unknown_type
	 */
	var $_uploadDir;
	
	
	/**
	 * Name of main photoq database table, holds posts in queue
	 * @var string
	 * @access public
	 */
	var $QUEUE_TABLE;

	/**
	 * Name of photoq database table holding meta field names
	 * @var string
	 * @access public
	 */
	var $QFIELDS_TABLE;

	/**
	 * Name of photoq database table relating posts in queue to categories
	 * @var string
	 * @access public
	 */
	var $QCAT_TABLE;

	/**
	 * Name of photoq database table relating posts in queue to meta fields
	 * @var string
	 * @access public
	 */
	var $QUEUEMETA_TABLE;
	
	/**
	 * Name of wordpress posts database table
	 * @var string
	 * @access public
	 */
	var $POSTS_TABLE;
	
	

	/**
	 * PHP5 type constructor
	 */
	function __construct()
	{

		global $wpdb;

		//get the PhotoQ error stack for easy access
		$this->_errStack = &PEAR_ErrorStack::singleton('PhotoQ');
		
		PhotoQHelper::debug('-----------start plugin-------------');
		// load text domain for localization
		load_plugin_textdomain('PhotoQ', '', 'photoq-photoblog-plugin/lang');

		// set names of database tables used and created by photoq
		$this->QUEUEMETA_TABLE = $wpdb->prefix."photoqmeta";
		$this->QUEUE_TABLE = $wpdb->prefix."photoq";
		$this->QFIELDS_TABLE = $wpdb->prefix."photoqfields";
		$this->QCAT_TABLE = $wpdb->prefix."photoq2cat";
		$this->POSTS_TABLE = $wpdb->prefix."posts";
		
		// setting up database
		$this->_db =& PhotoQSingleton::getInstance('PhotoQDB');
		
		// setting up options
		$this->_oc =& PhotoQSingleton::getInstance('PhotoQOptionController');
				
		// actions and filters are next

		// Upgrade the plugin after version changes
		add_action('init', array(&$this, '_autoUpgrade'));
		
		
		// Insert the _actionAddAdminPages() sink into the plugin hook list for 'admin_menu'
		add_action('admin_menu', array(&$this, '_actionAddAdminPages'));

		// function executed when a post is deleted
		add_action ( 'delete_post', array(&$this, '_actionCleanUp'));

		// Hook into the 'wp_dashboard_setup' action to setup the photoq dashboard widget
		add_action('wp_dashboard_setup', array(&$this, '_actionAddDashboardWidget') );
		add_action('admin_print_styles-index.php', array(&$this, '_actionEnqueueDashboardStyles'), 1);
		
		// the next two hooks are used to show a thumb in the manage post section
		add_filter('manage_posts_columns', array(&$this, '_filterAddThumbToListOfPosts'));
		add_action('manage_posts_custom_column', array(&$this, '_actionInsertThumbIntoListOfPosts'), 10, 2);

		
		// filter to show change photo form in post editing
		add_filter('edit_form_advanced', array(&$this, '_filterShowChangePostedPhotoBox'));

		// Only show description in content field when editing
		add_filter('edit_post_content', array(&$this, '_filterPrepareEditorContent'), 100, 2);
		// Get description back
		add_filter('wp_insert_post_data', array(&$this, '_filterPostProcessEditedPost'), 100, 2);
		
				
		register_activation_hook(PHOTOQ_PATH . 'whoismanu-photoq.php', array(&$this, 'activatePlugin'));
		register_deactivation_hook(PHOTOQ_PATH . 'whoismanu-photoq.php', array(&$this, 'deactivatePlugin'));

		
		add_filter('favorite_actions', array(&$this, '_filterAddFavoriteActions'));
		add_filter('contextual_help', array(&$this, '_filterAddContextualHelp'), 100, 2);
		

		/*foreach( $_POST as $key => $value){
			PhotoQHelper::debug("POST $key: ".print_r($value,true)." <br />");
			}

		foreach( $_GET as $key => $value){
			PhotoQHelper::debug("GET $key: ".print_r($value,true)." <br />");
			}
		*/
		
		
		PhotoQHelper::debug('leave __construct()');
		
	}
	

	/**
	 * this is the sink function for the 'admin_menu' hook.
	 * It hooks up the options and management admin panels.
	 */
	function _actionAddAdminPages()
	{
		global $post;
		// Add a new menu under Options:
		$options = add_options_page(__('PhotoQ Options','PhotoQ'), 'PhotoQ', 'manage_photoq_options', 'whoismanu-photoq.php', array(&$this, 'options_page'));
		// Add a new menu under Posts:
		$manage = add_submenu_page('post-new.php', __('Manage PhotoQ', 'PhotoQ'), 'PhotoQ', 'access_photoq', 'whoismanu-photoq.php', array(&$this, 'manage_page'));
		
		//adding javascript and other stuff to header
		
		//have to load it on every page until wordpress gets fixed, otherwise it won't work on translated versions
		add_action('admin_print_scripts-' . $options, array(&$this, 'addCSS'), 1);
		add_action('admin_print_scripts-' . $manage, array(&$this, 'addCSS'), 1);
		add_action('admin_print_scripts-' . $manage, array(&$this, 'addHeaderCode'), 1);
		
		//load the scripts and styles of the OptionController
		add_action("admin_print_styles-$options", array(&$this->_oc, 'enqueueStyles'), 1);
		add_action("admin_print_scripts-$options", array(&$this->_oc, 'enqueueScripts'), 1);
		
	}
	
	/**
	 * Registers the photoq dashboard widget
	 * @return unknown_type
	 */
	function _actionAddDashboardWidget() {
		if(current_user_can( 'access_photoq' ))
			wp_add_dashboard_widget('dashboard_photoq', 'PhotoQ', array(&$this,'showPhotoQDashboard'));
	}
	
	/**
	 * Load the CSS style-sheets needed
	 * @return unknown_type
	 */
	function _actionEnqueueDashboardStyles()
	{
		wp_enqueue_style('photoq-dashboard', plugins_url('photoq-photoblog-plugin/css/photoq-dashboard.css'));	
	}

	/**
	 * Callback function that displays the dashboard widget
	 * @return unknown_type
	 */
	function showPhotoQDashboard() {
		$this->initQueue(); //lazy initialization of queue
		
		$qLen = $this->_queue->getLength();
		printf(__('Number of photos in the queue: %s', 'PhotoQ'), "<b>$qLen</b>");
		if($qLen){
			echo '<h5>' . __('Next Photos to be published', 'PhotoQ').':</h5>';
			$noTop = min(3,$qLen);
			echo '<div class="table"><table><tbody>';
			for ($i = 0; $i < $noTop; $i++){
				$currentPhoto =& $this->_queue->getQueuedPhoto($i);
				$first = $i == 0 ? ' class="first"' : '';
				echo '<tr'.$first.'><td><img src="'.
				$currentPhoto->getAdminThumbURL(
				$this->_oc->getValue('showThumbs-Width'),
				$this->_oc->getValue('showThumbs-Height')
				).'" alt="'.$currentPhoto->getTitle().'" />
					</td>
					<td>'.$currentPhoto->getTitle().'</td>
					</tr>';
			}
			echo '</tbody></table></div>';
		}
		echo '<form method="post" action="edit.php?page=whoismanu-photoq.php">';
		if($qLen)
			$go2QBtn = '<input type="submit" class="button" name="show"
				value="'. __('Edit Queue', 'PhotoQ').'" />';
			echo $go2QBtn;
		
		$add2QBtn = '<input type="submit" class="button-primary action" name="add_entry"
			value="'. __('Add Photos to Queue', 'PhotoQ').'" />';
		echo $add2QBtn.'</form>';
		echo '<br class="clear"/>';
	}
		
	
	
	
	/**
	 * Injects a form to change the photo above the wordpress wysywig editor.
	 * The technique used here was shamelessly copied from the yapb plugin. So
	 * kudos to its author johannes jarolim for figuring this one out :-)
	 * @return unknown_type
	 */
	function _filterShowChangePostedPhotoBox(){
		global $post;
		if($this->isPhotoPost($post->ID)){
			$photo =& $this->_db->getPublishedPhoto($post->ID);
			
			//we are ready to show the form
			require_once(PHOTOQ_PATH.'panels/changePostedPhotoForm.php');
		}
	}

	
	/**
	 * A posted photo is being edited. Keep only its description in the editor.
	 * @param $data
	 * @param $postID
	 * @return unknown_type
	 */
	function _filterPrepareEditorContent($data, $postID)
	{
		PhotoQHelper::debug('enter _filterPrepareEditorContent()');
		if($this->isPhotoPost($postID) && $this->_oc->isManaged('content')){
			$post = get_post($postID);
			$photo =& new PhotoQPublishedPhoto($post->ID, $post->post_title);
			$data = $photo->getDescription();	
		}
		PhotoQHelper::debug('leave _filterPrepareEditorContent()');
		return $data;
	}
	
	/**
	 * Runs if photo post is saved in editor. Is executed before the database write.
	 * We here sync all the fields and update images if any were changed.
	 * @param $data
	 * @param $postarr
	 * @return unknown_type
	 */
	function _filterPostProcessEditedPost($data, $postarr)
	{
		PhotoQHelper::debug('enter _filterPostProcessEditedPost()');
		
		if($_POST['saveAfterEdit']){//only execute if we come from the editor
			$postID = $postarr['ID'];
			
			// verify this came from our screen and with proper authorization,
			// because save_post can be triggered at other times
			if ( !wp_verify_nonce( $_POST['photoqEditPostFormNonce'], 'photoqEditPost'.$postID )) {
				return $data;
			}

			PhotoQHelper::debug('passed check of nonce');

			if ( 'page' == $_POST['post_type'] ) {
				if ( !current_user_can( 'edit_page', $postID ))
				return $data;
			} else {
				if ( !current_user_can( 'edit_post', $postID ))
				return $data;
			}

			PhotoQHelper::debug('passed authentication');
			
			// OK, we're authenticated we can now start to change post data
			if($this->isPhotoPost($postID)){
				PhotoQHelper::debug('is photo post');
				$post = get_post($postID);
				$photo =& new PhotoQPublishedPhoto($post->ID, $post->post_title);
			
				//upload a new photo if any
				if(array_key_exists('Filedata', $_FILES) && !empty($_FILES['Filedata']['name'])){
					//PhotoQHelper::debug(print_r($_FILES,true));
					//PhotoQHelper::debug('path: ' . $photo->getOriginalDir());
	 				if($newPath = $this->_handleUpload($photo->getOriginalDir())){
	 					$photo->replaceImage($newPath);
	 				}
		 		}
		 		//sync the content to description and put photos back into content and excerpt
				$data = $photo->syncPostUpdateData($data);
			}
			
		}
		PhotoQHelper::debug('leave _filterPostProcessEditedPost()');
		return $data;
	}
		
	

	/**
	 * sink function for the 'add_options_page' hook.
	 * displays the page content for the 'PhotoQ Options' submenu
	 */
	function options_page()
	{
		
		

		/*foreach( $_POST as $key => $value){
			echo "$key: $value <br />";
		}*/
		$this->createDirIfNotExists($this->_oc->getCacheDir(), true);


		//we are inserting a field into the database
		if (isset($_POST['showWatermarkUploadPanel'])) {
			check_admin_referer('wimpq_options-nonce','wimpq_options-nonce');
			//show watermark upload panel
			require_once(PHOTOQ_PATH.'panels/uploadWatermark.php');

		}elseif (isset($_POST['showUpgradePanel'])) {
			check_admin_referer('wimpq_options-nonce','wimpq_options-nonce');
			//show upgrade panel
			require_once(PHOTOQ_PATH.'panels/upgrade.php');

		}elseif (isset($_POST['showMoveImgDirPanel'])) {
			check_admin_referer('wimpq_options-nonce','wimpq_options-nonce');
			//show upgrade panel
			require_once(PHOTOQ_PATH.'panels/upgrade-move-imgdir.php');

		}else{ //all of these show the options panel

			$oldImgDir = $this->_oc->getImgDir();
			
			
			//was there a full rebuild requested by the user?
			$rebuildAll = isset($_POST['rebuildAll']);
			
			// are we importing settings from XML?
			if(isset($_POST['importXML'])){
				check_admin_referer('photoqImportXML-nonce','photoqImportXML-nonce');
				$xmlFilename = esc_attr($_POST['presetFile']);
				//if the xml is successfully imported, rebuild everything
				if($this->_oc->importFromXML($xmlFilename)){
					$rebuildAll = true;
				}else
					$this->_errStack->push(PHOTOQ_XML_IMPORT_FAILED, 'error', array('filename' => $xmlFilename));
			}
			
			
			//creating batch processor
			$bp =& new PhotoQBatchProcessor($this);

			//boolean indicating whether a watermark is being uploaded
			$watermarkUpoaded = isset($_POST['uploadWatermark']);

			if ( $this->_oc->wasFormSubmitted() || isset($_POST['addImageSize']) ) {
				
				//var_dump($this->_oc->_options['views']->_children[0]->_children[0]->_children[3]->_children[0]);
				//var_dump($this->_oc);
				$this->_oc->update();		
				
				//var_dump($this->_oc->_options['views']->_children[0]->_children[0]->_children[3]->_children[0]->_value);
				
				
				//var_dump($this->_oc);	
				$this->_errStack->push(PHOTOQ_INFO_MSG,'info', array(), __('Options saved.', 'PhotoQ'));
			}
			elseif($watermarkUpoaded){
				check_admin_referer('wimpq_options-nonce','wimpq_options-nonce');
				$this->uploadWatermark();
			}
				
			
			//check whether image sizes changed. If so rebuild the thumbs and update the posts accordingly.
			$changedSizes = $this->_oc->getChangedImageSizeNames();
		
			//if the watermark changed we refresh all images that have watermarks plus all
			//images that were changed by sth else. 
			if($this->_oc->hasChanged('watermarkOptions') || $watermarkUpoaded)
				$changedSizes = array_unique(array_merge($changedSizes, $this->_oc->getImageSizeNamesWithWatermark()));
				
			//if full rebuild was selected we rebuild everything
			if($rebuildAll)
				$changedSizes = $this->_oc->getImageSizeNames();
				

			if(!empty($changedSizes) || $this->_oc->hasChanged(array('imgdir','views','exifTags','exifDisplay','originalFolder'))){
				
				//we need to rebuild photos/posts. this is time consuming -> prepare for batch processing
			
				//first determine what needs to be rebuilt
				$updateExif = $rebuildAll ? true : $this->_oc->hasChanged('exifTags') || $this->_oc->hasChanged('exifDisplay');
				$addDelTagFromExifArray = $this->_oc->getAddedDeletedTagsFromExif();
				
				$updateOriginalFolder = $rebuildAll ? true : ($this->_oc->hasChanged('originalFolder')  || $this->_oc->hasChanged('imgdir'));
				

					
				$changedViews = $this->_oc->getChangedViewNames($changedSizes, $updateExif, $updateOriginalFolder);
				//if full rebuild was selected we rebuild everything
				if($rebuildAll || $this->_oc->hasChanged('imgdir'))
					$changedViews = $this->_oc->getViewNames();
			
				
				PhotoQHelper::debug('update exif: ' .$updateExif.' update original folder: '. $updateOriginalFolder);
				PhotoQHelper::debug('changed exif: ' .$this->_oc->hasChanged('exifTags').' changed content:' . $this->_oc->hasChanged('contentView') .' changed excerpt: '. $this->_oc->hasChanged('excerptView'));
				
				//these two operations should not take too long so do them outside the batch
				if($publishedPhotoIDs = $this->_db->getAllPublishedPhotoIDs()){
					$oldNewFolderName = $this->_rebuildFileSystem($updateOriginalFolder, $changedSizes, $this->_oc->hasChanged('imgdir'), $oldImgDir);

					//create the array of operations this batch consists of and register it  with the batch processor
					$batchOperations = array(
					array(
						'batchRebuildPublished',
					array(	$publishedPhotoIDs,
					$changedSizes, $updateExif,
					$changedViews, $updateOriginalFolder,
					$oldNewFolderName[0], $oldNewFolderName[1],
					$addDelTagFromExifArray[0],$addDelTagFromExifArray[1]
					),
					array()
					)
					);
					
					PhotoQHelper::debug('registering batch sets');
					if( $bp->registerSet(new PhotoQBatchSet($batchOperations)) ){
						if(!empty($changedSizes))
							$this->_errStack->push(PHOTOQ_INFO_MSG,'info', array(), __('Updating following image sizes:', 'PhotoQ') . ' ' . implode(", ", $changedSizes));
						if(!empty($changedViews))
							$this->_errStack->push(PHOTOQ_INFO_MSG,'info', array(), __('Updating following views:', 'PhotoQ') . ' ' . implode(", ", $changedViews));
							
						$this->_errStack->push(PHOTOQ_INFO_MSG,'info', array(), __('Updating all published Photos...', 'PhotoQ') );
					}
				}
			}
					


			if(isset($_POST['upgradePhotoQ'])){

				check_admin_referer('wimpq_options-nonce','wimpq_options-nonce');

				$this->upgradeFrom12();

			}
			
			elseif (isset($_POST['fixPermissions'])) {
				check_admin_referer('wimpq_options-nonce','wimpq_options-nonce');
				//show upgrade panel
				$this->fixPermissions();
			}

			/*elseif(isset($_POST['moveOldImgDir'])){
				check_admin_referer('wimpq_options-nonce','wimpq_options-nonce');
				$status = $this->moveOldImgDir();
			}*/

			elseif(isset($_POST['removeOldYMFolders'])){
				check_admin_referer('wimpq_options-nonce','wimpq_options-nonce');
				foreach($this->getOldYMFolders() as $path)
				PhotoQHelper::recursiveRemoveDir($path);
				$this->_errStack->push(PHOTOQ_INFO_MSG,'info', array(), 
											__('Cleaned old folder structure.'));
			}

			//we are inserting a field into the database
			elseif (isset($_POST['addField'])) {
				check_admin_referer('wimpq_options-nonce','wimpq_options-nonce');
				$this->_db->insertField(esc_attr($_POST['newFieldName']), $this->_oc->getValue('fieldAddPosted'));
			}

			//we are renaming a field
			elseif(isset($_POST['rename_field'])){
				//check for correct nonce first
				check_admin_referer('wimpq_options-nonce','wimpq_options-nonce');
				$this->_db->renameField(esc_attr($_POST['field_id']), esc_attr($_POST['field_name']));
			}

			//we are deleting a field from the database
			elseif (isset($_GET['action']) && $_GET['action'] == 'delete') {
				//check for correct nonce first
				check_admin_referer('photoq-deleteField'.esc_attr($_GET['entry']));
				$this->_db->removeField( esc_attr($_GET['entry']) );
			}
				
			//show status of above operations up to here
			if(isset($status)) $status->show();

			//do input validation on options
			$this->_oc->validate();

			//make sure we have freshest data possible.
			$this->_oc->initRuntime();
			
			//check whether there are batches to be processed
			if($bp->haveBatch()){
				//there are -> show progress bar and
				//communicate the id of the current batch to the javascript
				?>
				
				<script type="text/javascript">
					var batchId =  <?php echo $bp->getId(); ?>;
					var ajaxNonce = "<?php echo wp_create_nonce( 'photoq-batchProcess' ); ?>";
					var ajaxUrl = "<?php echo plugins_url('photoq-photoblog-plugin/whoismanu-photoq-ajax.php'); ?>";
				</script>
				<?php
			}
				
			PhotoQErrorHandler::showAllErrors($this->_errStack);
			
			//show options panel
			require_once(PHOTOQ_PATH.'panels/options.php');
		}

	}


	/**
	 * sink function for the 'add_management_page' hook.
	 * displays the page content for the 'Manage PhotoQ' submenu
	 */
	function manage_page()
	{
		
		
		PhotoQHelper::debug('enter manage_page()');
		
		$this->initQueue(); //lazy initialization of queue
				
		//do some inital setup
		$this->createDirIfNotExists($this->_oc->getCacheDir(), true);
			
		if ( isset($_POST['add_entry']) || $this->_isFtpUpload() ) {
			//a photo will be added
			
			$this->createDirIfNotExists($this->_oc->getQDir());
			require_once(PHOTOQ_PATH.'panels/upload.php');
			
		}elseif (isset($_POST['edit_batch'])) {
		
			PhotoQHelper::debug('manage_page: load edit-batch panel');
			if($this->_isFtpUpload()){
				foreach ($_POST['ftpFiles'] as $ftpFile)
					$this->uploadPhoto(basename($ftpFile), '', $this->_oc->getValue('qPostDefaultTags'), '', $ftpFile);
				//refresh the queue
				$this->_queue->load();
			}
			require_once(PHOTOQ_PATH.'panels/edit-batch.php');
			PhotoQHelper::debug('manage_page: edit-batch panel loaded');
		
		}elseif (isset($_POST['batch_upload'])) {
			
			if($_POST['batch_upload']){ //check for correct nonce first
				check_admin_referer('photoq-uploadBatch');
			}
			$this->uploadPhoto($_FILES['Filedata']['name'], '', $this->_oc->getValue('qPostDefaultTags'), '');
			if(!$_POST['batch_upload']){
				//show errors if any
				PhotoQErrorHandler::showAllErrors($this->_errStack);
				//refresh the queue as a photo was added
				$this->_queue->load();
				require_once(PHOTOQ_PATH.'panels/edit-batch.php');
			}
		}else{
			if (isset($_POST['save_batch'])) {
					
				PhotoQHelper::debug('manage_page: start saving batch');
	
				//check for correct nonce first
				check_admin_referer('photoq-saveBatch','saveBatchNonce');
	
				//uploaded file info is stored in arrays
				$no_upl = count(PhotoQHelper::arrayAttributeEscape($_POST['img_title']));
				
				
				$qLength = $this->_queue->getLength();
	
				for ($i = 0; $i<$no_upl; $i++) {
					$this->update_queue(esc_attr($_POST['img_id'][$i]), esc_attr($_POST['img_title'][$i]), $_POST['img_descr'][$i], esc_attr($_POST['tags_input'][$i]), esc_attr($_POST['img_slug'][$i]), esc_attr($_POST['img_author'][$i]), esc_attr($_POST['img_position'][$i]), esc_attr($_POST['img_position'][$i]), esc_attr($_POST['img_parent'][$i]), $qLength, $i);
				}
	
				PhotoQHelper::debug('manage_page: batch saved');
					
			}
				
			if (isset($_POST['update_queue'])) {
				//check for correct nonce first
				check_admin_referer('photoq-updateQueue');
				$this->update_queue(esc_attr($_POST['img_id']), esc_attr($_POST['img_title']), $_POST['img_descr'], esc_attr($_POST['tags_input']), esc_attr($_POST['img_slug']), esc_attr($_POST['img_author']), esc_attr($_POST['img_position']), esc_attr($_POST['img_old_position']),esc_attr($_POST['img_parent'][0]), esc_attr($_POST['q_length']));
			}
				
			if (isset($_GET['action']) && $_GET['action'] == 'delete') {
				//check for correct nonce first
				check_admin_referer('photoq-deleteQueueEntry' . esc_attr($_GET['entry']));
				$status = $this->_queue->deletePhotoById(esc_attr($_GET['entry']));
			}
			
			if (isset($_GET['action']) && $_GET['action'] == 'rebuild') {
				//check for correct nonce first
				$postID = esc_attr($_GET['id']);
				check_admin_referer('photoq-rebuildPost' . $postID);
				$photo = &$this->_db->getPublishedPhoto($postID);
				if($photo)
					$photo->rebuild($this->_oc->getImageSizeNames());
				$status =& new PhotoQStatusMessage(__("Photo post with id $postID rebuilt."));
			}

			//the donation dialog appeared and the user either clicked on the "No, Thanks" or "Already Donated" link
			if (isset($_GET['action']) && ($_GET['action'] == 'nothanks') || ($_GET['action'] == 'alreadydid')){
				update_option('wimpq_posted_since_reminded', 0);
				$reminderThreshold = get_option('wimpq_reminder_threshold');
				$then = get_option('wimpq_last_reminder_reset');
				if($_GET['action'] == 'alreadydid' && time() - $then > 86400)
					$reminderThreshold *= 2; //don't bother guys who donated too often, exponential increase
				update_option('wimpq_reminder_threshold', $reminderThreshold);
				update_option('wimpq_last_reminder_reset', time());
			}
			
			//the first photo of the queue is being published
			if (isset($_POST['post_first'])) {
				//check for correct nonce first
				check_admin_referer('photoq-manageQueue', 'manageQueueNonce');
				$status = $this->_queue->publishTop();
			}
			
			if (isset($_POST['post_multi'])) {
				//check for correct nonce first
				check_admin_referer('photoq-manageQueue', 'manageQueueNonce');
				$status = $this->_queue->publishMulti($this->_oc->getValue('postMulti'));
			}
				
			if (isset($_POST['clear_queue'])) {
				//check for correct nonce first
				check_admin_referer('photoq-manageQueue', 'manageQueueNonce');
				$this->_queue->deleteAll();
			}
			
			if (isset($_POST['sort_queue'])) {
				//check for correct nonce first
				check_admin_referer('photoq-manageQueue', 'manageQueueNonce');
				$this->_queue->sort($_POST['sort_criterion']);
			}
				
			//refresh the queue as it might have changed because of above operations
			$this->_queue->load();
			
			//show donation reminder
			$this->_showReminder();
			
			//show status message if any
			if(isset($status)) $status->show();
			
			//show errors if any
			PhotoQErrorHandler::showAllErrors($this->_errStack);
				
			/*show the manage panel*/
			require_once(PHOTOQ_PATH.'panels/manage.php');
				
				
		}
		PhotoQHelper::debug('leave manage_page()');
	
		/*$timer =& PhotoQSingleton::getInstance('PhotoQTimers');
		$timer->start('photoQFullExec');
		print_r($timer->stop('photoQFullExec'));
		*/
		
	}


	/******************************************************/
	/******************** options page ********************/
	/******************************************************/
	
	/*
	 * display the list of currently used metafields
	 */
	function showMetaFields()
	{
		if($results = $this->_db->getAllFields()){
			$i = 0; //used to alternate styles
			foreach ($results as $field_entry) {

				echo '<tr valign="top"';
				if(($i+1)%2) {echo ' class="alternate"';}
				echo '>';
				if ($_GET['action'] == 'rename' && $_GET['entry'] == $field_entry->q_field_id ) {
					echo '<td><p><input type="text" name="field_name" size="15" value="'.$field_entry->q_field_name.'"/></p></td>';
					echo '<td><input type="hidden" name="field_id" size="15" value="'.$field_entry->q_field_id.'"/>&nbsp;</td><td><p><input type="submit" class="button-secondary" name="rename_field" value="Rename field &raquo;" /></p></td>';
				}else{
					echo '<td>'.$field_entry->q_field_name.'</td>';
					echo '<td><a href="options-general.php?page=whoismanu-photoq.php&amp;action=rename&amp;entry='.$field_entry->q_field_id.'" class="edit">Rename</a></td>';

					$delete_link = 'options-general.php?page=whoismanu-photoq.php&amp;action=delete&amp;entry='.$field_entry->q_field_id;
					$delete_link = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($delete_link, 'photoq-deleteField' . $field_entry->q_field_id) : $delete_link;
					echo '<td><a href="'.$delete_link.'" class="delete" onclick="return confirm(\'Are you sure?\');">Delete</a></td>';
				}
				echo '</tr>';

				$i++;
			}

		}else{
			echo '<tr valign="top"><td colspan="3">'. __('No fields so far. You can add some if you like.','PhotoQ').'</td></tr>';
		}
	}
	
	
	/******************************************************/
	/******************** manage page *********************/
	/******************************************************/
	
	
	/***** functions to manage photo queue *****/
	
	//uploads a photo, creates thumbnail and puts it to the end of the queue
	function uploadPhoto($title, $descr, $tags, $slug, $oldPath = '')
	{
		global $wpdb, $user_ID;
	
		PhotoQHelper::debug('enter uploadPhoto() ' . $title);
	
		//get the current user
		if ( empty($post_author) )
			$post_author = $user_ID;
			
		//we still didn't get an author -> set it to default
		if ( empty($post_author) )
			$post_author = $this->_oc->getValue('qPostAuthor');
		
		
		//put uploaded file into qdir
		$file = $this->_handleUpload($this->_oc->getQDir(), $oldPath);
		
		//PhotoQHelper::debug(print_r($file,true));
		
		//check for errors
		if ( !$file )
			return false;
				
		//get exif meta data
		$exif = PhotoQExif::readExif($file);
		
		$dateTime = $exif['DateTimeOriginal'];
		$exifDescr = $exif['ImageDescription'];
		// use EXIF image description if none was provided
		if (empty($descr) && $this->_oc->getValue('descrFromExif'))
			$descr = $exifDescr;
			
		if(!empty($exifDescr) && $this->_oc->getValue('autoTitleFromExif'))
			$title = $exifDescr;
		
		$exif = serialize($exif);

		PhotoQHelper::debug('uploadPhoto: got EXIF');
		PhotoQHelper::debug('DateTimeOriginal: ' . $dateTime );
		$filename = basename($file);
		
		//make nicer titles
		$title = addslashes($this->makeAutoTitle($title));
		
		PhotoQHelper::debug('uploadPhoto: created auto title');
		
		$this->initQueue(); //lazy initialization of queue
		
		
		//add photo to queue
		if(!$result = $wpdb->query("INSERT INTO $this->QUEUE_TABLE (q_title, q_imgname, q_position, q_slug, q_descr, q_tags, q_exif, q_date, q_fk_author_id) VALUES ('$title', '$filename', '".($this->_queue->getLength())."', '$slug', '$descr', '$tags', '$exif', '$dateTime', '$post_author')"))
			return false;
		
					
		//get the id assigned to this entry
		$imgID = mysql_insert_id();
	
		
		PhotoQHelper::debug('uploadPhoto: post added to DB. ID: '.$imgID);
		

		// Insert categories
		$post_categories = apply_filters('category_save_pre', PhotoQHelper::arrayAttributeEscape($_POST['post_category']));

		// Check to make sure there is a category, if not just set it to some default
		if (!$post_categories) $post_categories[] = $this->_oc->getValue('qPostDefaultCat');
		
		foreach ($post_categories as $post_category) {
			// Double check it's not there already
			$exists = $wpdb->get_row("SELECT * FROM $this->QCAT_TABLE WHERE q_fk_img_id = $imgID AND category_id = $post_category");

			if (!$exists)
				$this->_db->insertCategory($imgID, $post_category);
		
		}

		//handle the fields
		$results = $wpdb->get_results("SELECT * FROM $this->QFIELDS_TABLE WHERE 1");
		
		$fieldValue = '';
		if($results){
			foreach ($results as $field_entry) {
				//the common info box for ftp uploads submits an array we don't want to use here
				if(!is_array($_POST["$field_entry->q_field_name"]))
					$fieldValue = $_POST["$field_entry->q_field_name"];
				$insert_meta_query = "INSERT INTO $this->QUEUEMETA_TABLE (q_fk_img_id, q_fk_field_id, q_field_value)
					VALUES ($imgID, $field_entry->q_field_id, '".$fieldValue."')";
				$wpdb->query($insert_meta_query);
			}
		}
	
		PhotoQHelper::debug('leave uploadPhoto()');
		
		return new PhotoQStatusMessage(sprintf(_c('Successfully uploaded. \'%1$s\' added to queue at position %2$d.|filename postion'), $filename, $this->_queue->getLength() + 1), 'PhotoQ');

	}

		
	//updates a queue entry
	function update_queue($id, $title, $descr, $tags, $slug, $authorID, $position, $old_position, $parent, $qLength, $pnum = 0)
	{
		global $wpdb;
		PhotoQHelper::debug('enter update_queue(), position: ' . $position . ', old position: ' .$old_position . ', qlen: ' .$qLength);
	
		if($position < 0)
			$position = 0;
		if($position >= $qLength)
			$position = $qLength-1;
	
		if($position < $old_position){
			$wpdb->query("UPDATE  $this->QUEUE_TABLE SET q_position = q_position+1 WHERE q_position >= '$position' AND q_position < '$old_position'");
		}
		if($position > $old_position){
			$wpdb->query("UPDATE  $this->QUEUE_TABLE SET q_position = q_position-1 WHERE q_position <= '$position' AND q_position > '$old_position'");
		}
		
		PhotoQHelper::debug("UPDATE  $this->QUEUE_TABLE SET q_position = '$position', q_title = '$title', q_descr = '$descr', q_tags = '$tags', q_slug = '$slug', q_fk_author_id = '$authorID', q_edited = 1 WHERE q_img_id = $id");
	
		$wpdb->query("UPDATE  $this->QUEUE_TABLE SET q_position = '$position', q_title = '$title', q_descr = '$descr', q_tags = '$tags', q_slug = '$slug', q_fk_author_id = '$authorID', q_edited = 1 WHERE q_img_id = $id");
	
		/*update categories*/
		//$q_id = preg_replace('/\./','_',$id); //. in post vars become _
	
		// Now it's category time!
		// Check to make sure there is a category, if not just set it to some default
		$post_categories = $_POST['post_category'][$id];
		if (!$post_categories) $post_categories[] = $this->_oc->getValue('qPostDefaultCat');
		$post_categories = apply_filters('category_save_pre', PhotoQHelper::arrayAttributeEscape($post_categories));
		// First the old categories
		$old_categories = $this->_db->getCategoriesByImgId($id);
		// Delete any?
		foreach ($old_categories as $old_cat) {
			if (!is_array($post_categories) || !in_array($old_cat, $post_categories)) // If a category was there before but isn't now
			$wpdb->query("DELETE FROM $this->QCAT_TABLE WHERE q_fk_img_id = $id AND category_id = $old_cat LIMIT 1");
		}
	
		// Add any?
		if(is_array($post_categories)){
			foreach ($post_categories as $new_cat) {
				if (!in_array($new_cat, $old_categories))
					$this->_db->insertCategory($id, $new_cat);
			}
		}
		
		//handle the fields
		$results = $wpdb->get_results("
		SELECT
		*
		FROM
		$this->QFIELDS_TABLE
		WHERE 1");
	
		if($results){
			foreach ($results as $field_entry) {
				$update_meta_query = "UPDATE $this->QUEUEMETA_TABLE SET q_field_value = '".$_POST["$field_entry->q_field_name"][$pnum]."'
				WHERE q_fk_img_id = $id && q_fk_field_id = $field_entry->q_field_id";
				$wpdb->query($update_meta_query);
			}
				
		}
	
		PhotoQHelper::debug('leave update_queue()');
	
	}

	
	

	//called by cronjob file
	function cronjob()
	{
		global $wpdb;
	
		$this->initQueue(); //lazy initialization of queue
		
		PhotoQHelper::debug('enter cronjob()');
	
		//add ftp dir to queue if corresponding option is set
		if( $this->_oc->onCronimportFtpUploadsToQueue() ){
			$ftpDir = $this->_oc->getFtpDir();
			if (is_dir($ftpDir)) {
				$ftpDirContent = PhotoQHelper::getMatchingDirContent($ftpDir,'#.*\.(jpg|jpeg|png|gif)$#i');
				foreach ($ftpDirContent as $ftpFile)
					$this->uploadPhoto(basename($ftpFile), '', '', '', $ftpFile);
					
				//reload the queue to get newly uploaded photos
				$this->_queue->load();
			}
		}
		
		//echo "Testing Cron Job";
		//echo "Cron frequency: ".$this->_oc->getValue('cronFreq')." <br />";
		

		
		//calculate time in hours since last post
	
		$currentTime = strtotime(gmdate('Y-m-d H:i:s', (time() + (get_option('gmt_offset') * 3600))));
		//echo "Current time: $currentTime <br>";
		//echo 'Current time: '. date('Y-m-d H:i:s', $currentTime) ."<br />";
	
		$lastTime = $wpdb->get_var("SELECT post_date FROM $this->POSTS_TABLE WHERE post_status = 'publish' ORDER BY post_date DESC");
		if($lastTime){
			//echo "last string: ". $lastTime ."<br />";
			$lastTime = strtotime($lastTime);
		}else{
			PhotoQHelper::debug('cronjob: lastTime was null');
			$lastTime = 0; //somewhere way back in the past, when time started ;-)
		}
	
		//echo "Last post: $lastTime <br />";
		//echo 'Last post: '. date('Y-m-d H:i:s', $lastTime) ."<br />";
	
	
		$timeDifferenceSeconds = $currentTime - $lastTime;
		//echo "seconds = $timeDifferenceSeconds <br />";
	
		$timeDifferenceHours = round($timeDifferenceSeconds / 3600);
		//echo "Diff: $timeDifferenceHours <br />";
	
		if($timeDifferenceHours >= $this->_oc->getValue('cronFreq'))
			if($this->_oc->getValue('cronPostMulti'))
				$this->_queue->publishMulti($this->_oc->getValue('postMulti'));
			else
				$this->_queue->publishTop();
	
			
		PhotoQHelper::debug('leave cronjob()');
	}
	
	/*sink function executed whenever a post is deleted. Takes post id as argument.
	 Deletes the corresponding image and thumb files from server if post is deleted.*/
	function _actionCleanUp($id)
	{
		//only do this when specific option is set
		if($this->_oc->getValue('deleteImgs')){
			if($this->isPhotoPost($id)){
				$post = get_post($id);
				$photo =& new PhotoQPublishedPhoto($post->ID, $post->title);
				$photo->delete();
			}
		}
	}
	
	
	
	/**
	 * Load dbx javascript
	 */
	function addHeaderCode ()
	{
		if (function_exists('wp_enqueue_script')) {
			if($this->_oc->getValue('enableBatchUploads')){
				wp_enqueue_script('swfu-callback', plugins_url('photoq-photoblog-plugin/js/swfu-callback.js'),array('jquery','swfupload'),'20080217');
				wp_localize_script( 'swfu-callback', 'swfuCallbackL10n', array(
	  				'cancelConfirm' => __('Are you sure you want to cancel the upload?', 'PhotoQ'),
					'allUp' => __('All files uploaded.', 'PhotoQ'),
					'select' => __('Select Photos...', 'PhotoQ'),
					'uploading' => __('Uploading', 'PhotoQ'),
					'file' => __('The file', 'PhotoQ'),
					'isZero' => __('has a size of zero.', 'PhotoQ'),
					'invType' => __('has an invalid filetype.', 'PhotoQ'),
					'exceed' => __('exceeds the upload file size limit of', 'PhotoQ'),
					'ini' => __('KB in your php.ini config file.', 'PhotoQ'),
					'tooMany' => __('You have attempted to queue too many files.', 'PhotoQ'),
					'queueEmpty' => __('Upload Queue is empty', 'PhotoQ'),
					'addMore' => __('Add more...', 'PhotoQ'),
					'queued' => __('photos queued for upload', 'PhotoQ'),
					'cancelled' => __('cancelled', 'PhotoQ'),
					'progressBarUrl' => plugins_url('photoq-photoblog-plugin/imgs/progressbar_v12.jpg')
				));
			}
			
			wp_enqueue_script('ajax-queue', plugins_url('photoq-photoblog-plugin/js/ajax-queue.js'), array('jquery-ui-sortable'),'20080302');
			wp_localize_script('ajax-queue', 'ajaxQueueL10n', array(
				'allowReorder' => current_user_can( 'reorder_photoq' )
			));
		}
	
			
		if($this->_oc->getValue('enableBatchUploads') && ( isset($_POST['add_entry']) || isset($_POST['update_photos']) ) ){
				
			$uploadLink = get_bloginfo('wpurl').'/wp-admin/edit.php?page=whoismanu-photoq.php';
			$uploadLink = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($uploadLink, 'photoq-uploadBatch') : $uploadLink;
			//flash doesn't seem to like encoded ampersands, so convert them back here
			$uploadLink = str_replace('&#038;', '&', $uploadLink);
	
			?>
	
			<script type="text/javascript">
				//<![CDATA[
			
				var swfu; 
				var uplsize = 0;
				
				
				window.onload = function () { 
					swfu = new SWFUpload({ 
						debug: false,
						upload_url : "<?php echo $uploadLink; ?>", 
						flash_url : "<?php echo includes_url('js/swfupload/swfupload.swf'); ?>", 
						file_size_limit : <?php echo PhotoQHelper::getMaxFileSizeFromPHPINI();?>,	// max allowed by php.ini
						file_queue_limit: 0,
						file_types : "*.jpg;*.gif;*.png",
						file_types_description: "Web Image Files...",
						post_params : { "auth_cookie" : "<?php if ( is_ssl() ) echo $_COOKIE[SECURE_AUTH_COOKIE]; else echo $_COOKIE[AUTH_COOKIE]; ?>",
										"batch_upload" : "1",
										"_wpnonce" : "<?php echo wp_create_nonce('photoq-uploadBatch'); ?>" },
						file_queue_error_handler : fileQueueError,
						file_queued_handler : fileQueued, 
						file_dialog_complete_handler : fileDialogComplete, 
						upload_start_handler : uploadStart,
						upload_progress_handler : uploadProgress,
						upload_error_handler : uploadError,
						upload_success_handler : uploadSuccess,
						upload_complete_handler : uploadComplete,
						button_text: '<span class="button"><?php _e('Select Photos...', 'PhotoQ'); ?><\/span>',
						button_text_style: '.button { color: #ffffff; text-align: center; font-size: 11px; font-weight: bold; font-family:"Lucida Grande","Lucida Sans Unicode",Tahoma,Verdana,sans-serif; }',
						button_height: "22",
						button_width: "134",
						button_image_url: '<?php echo plugins_url('photoq-photoblog-plugin/imgs/upload.png'); ?>',
						button_placeholder_id: "flash-browse-button"
					}); 
					
				};
	
				//]]>
							
			</script>
		
			<?php
	} //if($this->_oc->getValue('enableBatchUploads'))
	
	// the following are needed to pass stuff to the ajax js
	?>
	
		<script type="text/javascript">
			var ajaxUrl = "<?php echo plugins_url('photoq-photoblog-plugin/whoismanu-photoq-ajax.php'); ?>";
		</script>
	
	<?php
	
	}
	
	function addCss(){
		?>
	
		<link
		rel="stylesheet"
		href="<?php echo plugins_url('photoq-photoblog-plugin/css/photoq.css');?>"
		type="text/css" />
	
		<?php
		
		wp_enqueue_script('mini-postbox', plugins_url('photoq-photoblog-plugin/js/mini-postbox.js'), array('jquery'),'20080808');
		wp_enqueue_script('batch-progress', plugins_url('photoq-photoblog-plugin/js/batch-progress.js'), array('jquery'),'20090316');	
		wp_localize_script( 'batch-progress', 'batchProgressL10n', array(
	  		'abortStr' => __('Aborting batch processing due to following error:', 'PhotoQ'),
			'doneStr' => __('Updating done.', 'PhotoQ'),
			'waitStr1' => _c('Please wait, updating', 'PhotoQ'),
			'waitStr2' => _c('complete.', 'PhotoQ'),
			'progressBarUrl' => plugins_url('photoq-photoblog-plugin/imgs/progressbar_v12.jpg')
		));
		
	}
	
	
	/**
	 * Checks whether a post is a photo post. A post is considered a photopost if it has a custom
	 * field called photoQPath.
	 *
	 * @param unknown $postID The id of the post to be checked
	 * @return boolean True if the post is photo post
	 * @access public
	 */
	function isPhotoPost($postID)
	{
		$photoQPath = get_post_meta($postID, 'photoQPath', true);
		if(empty($photoQPath)) return false;
		return true;
	}
	
	/**
	 * Adds a link to the photo queue to the favorites action menu
	 * @param $actions
	 * @return unknown_type
	 */
	function _filterAddFavoriteActions($actions){
		$newActions = array(
		'post-new.php?page=whoismanu-photoq.php' => array(__('Show PhotoQ','PhotoQ'), 'edit_posts')
		);
		return array_merge($actions,$newActions);
	}
	
	/**
	 * Adds a link to the contextual help menu
	 * @param $actions
	 * @return unknown_type
	 */
	function _filterAddContextualHelp($text, $screen){
		if($screen == 'settings_page_whoismanu-photoq' || $screen = 'posts_page_whoismanu-photoq'){
			$text .= '<br/><a href="http://www.whoismanu.com/photoq-wordpress-photoblog-plugin/" target="_blank">'.__('PhotoQ Documentation','PhotoQ').'</a><br/>';
			$text .= '<a href="http://www.whoismanu.com/forum/" target="_blank">'.__('PhotoQ Support Forum','PhotoQ').'</a>';
		}
		return $text;
	}
    
	
	/**
	 * This is a filter hooked into the manage_posts_columns WordPress hook. It adds a new column
	 * header for the thumbnail column to the column headers of the manage post list.
	 *
	 * @param string $content	the list of column headers.
	 *
	 * @returns string          the list of column headers including the new column.
	 * @access public
	 */
	function _filterAddThumbToListOfPosts($content)
	{
		$result = array();
		foreach( $content as $key => $value){
			//add thumb column before the title column
			if($key == "title")
				$result["photoQPhoto"] = "Photo";
			
			$result[$key] = $value;
			//add actions after date column
			if( $key == "date"  && current_user_can( 'access_photoq' ) )
				$result["photoQActions"] = "PhotoQ Actions";
	
		}
		return $result;
	}
	
	
	/**
	 * This is an action hooked into the manage_posts_custom_column WordPress hook. It displays an
	 * additional column in the manage post list containing the thumbnail for photo posts.
	 *
	 * @param string $content     The name of the column to be displayed.
	 * @param string $postID	  The id of the post for which we want to show the photo
	 * @access public
	 */
	function _actionInsertThumbIntoListOfPosts($colName, $postID){
		if($colName == "photoQPhoto"){
			if($this->isPhotoPost($postID)){
				$photo =& $this->_db->getPublishedPhoto($postID);
				echo '<img src="'. 
					$photo->getAdminThumbURL(
						$this->_oc->getValue('showThumbs-Width'), 
						$this->_oc->getValue('showThumbs-Height')
					).'" alt="'.$photo->getTitle().'" />';
				
			}else
				echo "No Photo";
		}
		if($colName == "photoQActions"){
			if($this->isPhotoPost($postID)){
				$rebuildLink = 'edit.php?page=whoismanu-photoq.php&action=rebuild&id='.$postID;
				$rebuildLink = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($rebuildLink, 'photoq-rebuildPost' . $postID) : $rebuildLink;
				echo '<a href="'.$rebuildLink.'" title="Rebuild this photo and its post content.">Rebuild</a>';
			}
		}
	}
		
		
	/**
	 * Generates automatic title form filename. Removes suffix,
	 * replaces underscores by spaces and capitalizes only first
	 * letter of any word.
	 *
	 * @param string $filename
	 * @return string
	 */
	function makeAutoTitle($filename){
		//remove custom stuff
		$title = preg_replace('/'.stripslashes($this->_oc->getValue('autoTitleRegex')).'/', '', $filename);
		//remove suffix
		$title = preg_replace('/\..*?$/', '', $title);
		//replace underscores and hyphens with spaces
		$replaceWithWhiteSpace = array('-', '_');
		$title = str_replace($replaceWithWhiteSpace, ' ', $title);
		//proper capitalization
		$title = ucwords(strtolower($title));
		
		PhotoQHelper::debug('makeAutoTitle: standard stuff and custom filter done ' . $title);
		
		//uncapitalize user defined words
		$noCaps = explode(',', str_replace(' ', '', $this->_oc->getValue('autoTitleNoCaps')));
		foreach($noCaps as $toLower){
			$title = PhotoQHelper::strIReplace(' '.$toLower.' ', strtolower(' '.$toLower.' '), $title);
		}
		
		PhotoQHelper::debug('makeAutoTitle: uncapped user defined ' . $title);
		
		//uncapitalize short words
		$words = explode(' ', $title);
		$titleLen = count($words);
		for($i = 0; $i < $titleLen; $i++){
			if(strlen($words[$i]) <= $this->_oc->getValue('autoTitleNoCapsShortWords'))
				$words[$i] = strtolower($words[$i]);
			if($i == $titleLen-1)//capitalize last word
				$words[$i] = ucfirst($words[$i]);
		}
		$title = implode(' ', $words);
		
		PhotoQHelper::debug('makeAutoTitle: uncapped short ' . $title);
		
		//recapitalize user defined excepted words
		$caps = explode(',', str_replace(' ', '', $this->_oc->getValue('autoTitleCaps')));
		foreach($caps as $toUpper){
			$title = PhotoQHelper::strIReplace(' '.$toUpper.' ', strtoupper(' '.$toUpper.' '), $title);
		}
		
		PhotoQHelper::debug('makeAutoTitle: leaving now ' . $title);
		
		//recapitalize first letter of name
		return ucfirst($title);
	}
	
	/**
	 * Creates directory with path given if it does not yet exist. If an error occurs it
	 * is displayed.
	 *
	 * @param string $dir	The path of the directory to be created.
	 */
	function createDirIfNotExists($dir, $silent=false){
		//create $dir if does not exist yet
		if( !PhotoQHelper::createDir($dir) && !$silent){
			$status =& new PhotoQErrorMessage(__("Error when creating $dir directory. Please check your PhotoQ settings."));
			$status->show();
		}
	}
	
	/**
	 * Moves uploaded file to $destDir. If $oldPath is given by copying from there
	 * (used in case of ftp uploads). Otherwise the wordpress built-in upload handler
	 * is called that copies from the temporary upload directory.
	 *
	 * @param string $destDir
	 * @param string $oldPath
	 * @return array	containing info on uploaded file.
	 */
	function _handleUpload($destDir, $oldPath = ''){
		$destDir = rtrim($destDir,'/\\');
		//if on windows backslashes need to be there otherwise wp upload function is choking.
		//we really need to find a better solution for this.
		$cleanAbs = str_replace('\\', '/', ABSPATH);
		$destDir = str_replace($cleanAbs,ABSPATH,$destDir);
		
		PhotoQHelper::debug('destDir: '. $destDir);
		if($oldPath === ''){
			
			//prepare for upload -> set photoq upload dirs
			$this->_uploadDir = $destDir;
			add_filter( 'upload_dir', array(&$this, '_filterPhotoQUploadDir') );
		
			//set the options that we override
			$overrides = array('action'=>'save');
			$overrides['test_form'] = false; //don't test the form, swfupload is not (yet) able to send additional post vars.
			$overrides['mimes'] = apply_filters('upload_mimes', 
				array (
					'jpg|jpeg|jpe' => 'image/jpeg',
					'gif' => 'image/gif',
					'png' => 'image/png',
					'bmp' => 'image/bmp',
					'tif|tiff' => 'image/tiff'
				)
			);
	
			PhotoQHelper::debug('uploadPhoto: start upload');
	
			//upload the thing
			$file = wp_handle_upload($_FILES['Filedata'], $overrides);
			
			PhotoQHelper::debug(print_r($file, true));
	
			//reset upload options
			remove_filter( 'upload_dir', array(&$this, '_filterPhotoQUploadDir') );
			
		}else{ /* ftp upload */
			$newPath = $destDir . '/' . basename($oldPath);
			//move file if we have permissions, otherwise copy file
			//suppress warnings if original could not be deleted due to missing permissions
			$ok = @PhotoQHelper::moveFileIfNotExists($oldPath, $newPath);
			if(!$ok) $file['error'] = sprintf(__('Unable to move %1$s to %2$s', 'PhotoQ'),$oldPath,$newPath);
			$file['file'] = $newPath;	
		}
		
		
		//check for errors
		if ( isset($file['error']) ){// return new PhotoQErrorMessage($file['error']);
			$this->_errStack->push(PHOTOQ_FILE_UPLOAD_FAILED,'error', array('errMsg' => $file['error']));
			return false;
		}
		
	
		//get the path to the new file
		$path = $file['file'];
		
		PhotoQHelper::debug('uploadPhoto: upload ok, path: '. $path);
		
		
		return $path;
	}
	
	/**
	 * Called before a file is uploaded. We replace the standard WP upload location 
	 * with the one we set in $this->_uploadDir before calling the upload function.
	 * @param $uploads
	 * @return unknown_type
	 */
	function _filterPhotoQUploadDir( $uploads ) {
		
		$dir = $this->_uploadDir;
		$url = PhotoQHelper::getRelUrlFromPath($dir);
	
		$bdir = $dir;
		$burl = $url;

		$subdir = '';
	
		$dir .= $subdir;
		$url .= $subdir;

		$uploads = array( 'path' => $dir, 'url' => $url, 'subdir' => $subdir, 'basedir' => $bdir, 'baseurl' => $burl, 'error' => false );

		// Make sure we have an uploads dir
		if ( ! wp_mkdir_p( $uploads['path'] ) ) {
			$message = sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $uploads['path'] );
			$uploads['error'] = $message;
		}
		
		return $uploads;
	}
	
	/**
	 * Are we currently doing an ftp upload?
	 * @return boolean
	 */
	function _isFtpUpload(){
		return !PhotoQHelper::isWPMU() && isset($_POST['ftp_upload']);
	}
	

	/**
	 * Handles uploading of a new watermark image.
	 *
	 */
	function uploadWatermark(){
		//watermark images can have different suffixes, but we only want one watermark file at a time.
		//instead of finding them all we just delete and recreate the directory.
		$wmDir = $this->_oc->getImgDir().'photoQWatermark/';
		PhotoQHelper::recursiveRemoveDir($wmDir);
		PhotoQHelper::createDir($wmDir);
		//put uploaded file into watermark directory
		if(!$file = $this->_handleUpload($wmDir)){
			//$this->_errStack->push(PHOTOQ_FILE_UPLOAD_FAILED,'error', array('errMsg' => $file['error']));
			delete_option( 'wimpq_watermark' );
		}else{
			$pathParts = PhotoQHelper::pathInfo($file);
			$newPath = preg_replace("#".$pathParts['filename']."#", 'watermark', $file);
			PhotoQHelper::moveFile($file, $newPath);

			if(get_option( 'wimpq_watermark' ))
				update_option('wimpq_watermark', $newPath);
			else
				add_option('wimpq_watermark', $newPath);

			//this is now done via batch processing
			//$this->rebuildPublished($this->_oc->getImageSizeNamesWithWatermark(), false, false, false);
			$this->_errStack->push(PHOTOQ_INFO_MSG,'info', array(),
				__('New Watermark successfully uploaded. Updating image sizes including watermark...'));
		}
	}
	
	/**
	 * Display current watermark <img> tag or the string 'None' if there is no watermark.
	 *
	 */
	function showCurrentWatermark(){
		$path = get_option( "wimpq_watermark" );
		if(!$path)
			_e('None', 'PhotoQ');
		else{
			$size = getimagesize($path);
			echo '<img class="watermark" width="'.$size[0].'" height="'.$size[1].'" alt="PhotoQ Watermark" src="'. PhotoQHelper::getRelUrlFromPath($path) .'" />';
		}
	}
	
	/**
	 * Change "original" folder name to a random string if desired.
	 *
	 */
	function _updateOriginalFolderName($oldImgDir){
		$newName = 'original';
		if($this->_oc->getValue('hideOriginals')){
			//generate a random name
			$newName .= substr(md5(rand()),0,8);
		}
		$this->_oc->ORIGINAL_IDENTIFIER = $newName;
		
		//update option plus get old name
		$oldName = get_option( "wimpq_originalFolder" );
		if($oldName)
			update_option( "wimpq_originalFolder", $newName);
		else{
			$oldName = 'original';
			add_option("wimpq_originalFolder", $newName);
		}
		
		return array($oldImgDir.$oldName, $this->_oc->getImgDir().$newName);
	}
	
	/**
	 * This function is called when rebuilding all published photos via batch processing
	 * that spreads the rebuilding over several http calls to prevent script timeouts. 
	 * the state contains the number of photos that were already processed in previous calls,
	 * this persistent info allows us to keep track of what still needs to be done.
	 * @param $ids Array contains ids of all published photoq posts
	 * @param $changedSizes
	 * @param $updateExif
	 * @param $changedViews
	 * @param $updateOriginalFolder
	 * @param $oldFolder
	 * @param $newFolder
	 * @param $state
	 * @return unknown_type
	 */
	function batchRebuildPublished($ids, $changedSizes, $updateExif, $changedViews,
		$updateOriginalFolder, $oldFolder, $newFolder, $addedTags, $deletedTags, &$state){

		//PhotoQHelper::debug('enter batchRebuildPublished()');
		
		if(empty($state))
			$state = array('numProcessed' => 0);
		else
			$state['numProcessed']++;

		//get the id of the photo to be rebuilt in this function call
		$currentId = $ids[$state['numProcessed']];

		//if we are changing original dir it is normal to get some photo not found errors
		//we therefore silence these in this case
		if($updateOriginalFolder)
			$this->_errStack->pushCallback(array('PhotoQErrorHandler', 'mutePHOTOQ_PHOTO_NOT_FOUND'));
		
		//rebuild it
		$photo = &$this->_db->getPublishedPhoto($currentId);
		
		PhotoQHelper::debug('photo: ' . $photo->getTitle());
		//re-enable all errors
		//if($updateOriginalFolder)
		//	$this->_errStack->popCallback();
		
		if($photo)
			$photo->rebuild($changedSizes, $updateExif, $changedViews,
				$updateOriginalFolder, $oldFolder, $newFolder, $addedTags, $deletedTags);
				
		//PhotoQHelper::debug('leave batchRebuildPublished()');
		//the percentage of job done is given by the ratio of processed and total published
		return new PhotoQBatchStatus(($state['numProcessed']+1)/count($ids),$state);
			
			
	}
	
	function rebuildPublished($changedSizes, $updateExif, $changedViews, $updateOriginalFolder)
	{
		$publishedPhotos = $this->_db->getAllPublishedPhotos();
		
		$oldNewFolderName = $this->_rebuildFileSystem($updateOriginalFolder, $changedSizes);

		//get all photo posts, foreach size, rebuild the photo
		foreach ( $publishedPhotos as $photo ){
			$photo->rebuild( $changedSizes, $updateExif, $changedViews, 
				$updateOriginalFolder, $oldNewFolderName[0], $oldNewFolderName[1]);
		}
	}
	
	function _rebuildFileSystem($updateOriginalFolder,$changedSizes, $imgDirChanged, $oldImgDir){
		
		//check whether imgdir changed. if so we have to move all the existing stuff and rebuild the content
		if($imgDirChanged){
			$this->_moveImgDir($oldImgDir, false);
		}

		$oldNewFolderName = array('','');
		if($updateOriginalFolder){
			$oldNewFolderName = $this->_updateOriginalFolderName($oldImgDir);
			PhotoQHelper::moveFile($oldNewFolderName[0], $oldNewFolderName[1]);
		}
			
		//remove the image dirs
		foreach ($changedSizes as $changedSize){
			PhotoQHelper::recursiveRemoveDir($this->_oc->getImgDir() . $changedSize . '/');
		}
		PhotoQHelper::debug('oldNewFolderName: ' . print_r($oldNewFolderName,true));
		
		return $oldNewFolderName;
	}
	
	
	
	/**
	 * Runs any automatic upgrading things when changing between versions.
	 *
	 */
	function _autoUpgrade(){
		
		if($this->_version != get_option( "wimpq_version" )){
			
			//upgrade to 1.8.2, we need to put the queue back in order
			//plus the numbering should start at 0
			if($this->_version == '1.8.2'){
				if($results = $this->_db->getQueueByPosition()){
					foreach ($results as $position => $qEntry) {
						$this->_db->setQueuePosition($qEntry->q_img_id, $position);
					}
				}
			}
			
			// upgrade to 1.8. the structure of views changed, we don't want to force a rebuild on our users
			// so we deal with it here, adjusting the old views to the new ones
			$oldOptionArray = get_option('wimpq_options');
			if(!isset($oldOptionArray['views'])){				
				$views = array();
				$views['views'] = array('content' => 0, 'excerpt' => 0);
				//copy the old settings over
				$views['content'] = $oldOptionArray['contentView'];
				$views['excerpt'] = $oldOptionArray['excerptView'];
				//store the new views setting
				$oldOptionArray['views'] = $views;
				//remove the old guys
				unset($oldOptionArray['contentView']);
				unset($oldOptionArray['excerptView']);

				update_option('wimpq_options', $oldOptionArray);

				//reload to make the changes active
				$this->_oc->load();
			}
			
			// upgrade to 1.5.2 requires removing content of old photoq cache directory
			// if upgrading from 1.5 ...
			$oldPhotoQPath = str_replace('photoq-photoblog-plugin','whoismanu-photoq',PHOTOQ_PATH);
			$oldCachePath = $oldPhotoQPath . 'cache';
			if(file_exists($oldCachePath)){
				PhotoQHelper::recursiveRemoveDir($oldCachePath);
			}
			
			// ...or removing content of cache directory in other location if upgrading from 1.5.1
			$oldCachePath = PHOTOQ_PATH . 'cache';
			if(file_exists($oldCachePath)){
				PhotoQHelper::recursiveRemoveDir($oldCachePath);
			}
			
			// upgrade to 1.5.2 requires content rebuild because p tags changed to divs
			if($this->_version == '1.5.2' && $this->_oc->getValue('inlineDescr')){
				//get all photo posts, foreach size, rebuild the content
				foreach ( $this->_db->getAllPublishedPhotos() as $photo ){
					@$photo->rebuild( array(), false, true, false, '', '');
				}
			}
			
			// upgrade the database tables
			$this->_db->upgrade($this->_version);
		}	
	}
	


	/**
	 * Upgrade pre photoq 1.5 photos to 1.5
	 *
	 */
	function upgradeFrom12(){

		foreach ( $this->_db->getAllPhotos2Import() as $photo ){

			$photo->upgrade();

		}

	}

	/**
	 * Fix PhotoQ file and folder permissios such that they match the ones from WP.
	 * @return unknown_type
	 */
	function fixPermissions(){
		//get permissions of imgdir = permissions of directories
		$stat = @stat( $this->_oc->getImgDir() );
		$dirPerms = $stat['mode'] & 0007777;  // Get the permission bits.
		
		//get permissions for files
		$filePerms = $stat['mode'] & 0000666;
		
		//change all files inside imgdir
		$topLevelDirs = $this->_getOldImgDirContent($this->_oc->getImgDir());
		foreach($topLevelDirs as $dir)
			$this->_recursiveChmod($dir, $dirPerms, $filePerms);
		
		//change all files inside cache dir
		$this->_recursiveChmod($this->_oc->getCacheDir(), $dirPerms, $filePerms);
		
	}
	
	function _recursiveChmod($dir, $dirPerms, $filePerms){
		@chmod($dir, $dirPerms);
		
		//get all visible files inside dir
		$match = '#^[^\.]#';//exclude hidden files starting with .
		$visibleFiles = PhotoQHelper::getMatchingDirContent($dir, $match);
		
		foreach($visibleFiles as $file){
			//echo $file .'<br/>';
			if(is_dir($file)){
				@chmod($file, $dirPerms);
				$this->_recursiveChmod($file, $dirPerms, $filePerms);	
			}else{
				@chmod($file, $filePerms);	
			}	
		}
	}
	
	
	/**
	 * Something like this will be used to allow users to switch imgdir
	 *
	 */
	
	function _moveImgDir($oldImgDir, $includingOriginal = true){
		//$oldImgDir = $this->_oc->getOldValues('imgdir');
		
		//move all dirs to the new place
		$newImgDir = $this->_oc->getImgDir();
		$dirs2move = $this->_getOldImgDirContent($oldImgDir, $includingOriginal);
		foreach( $dirs2move as $dir2move){
			$moveTo = $this->_oc->getImgDir().basename($dir2move);
			if(!PhotoQHelper::moveFileIfNotExists($dir2move, $moveTo)){
					$this->_errStack->push(PHOTOQ_COULD_NOT_MOVE, 'error', array('source' => $dir2move, 'dest' => $moveTo));
			}//else
			// @todo we might want to use sth like this to make it more flexible for users who 
			//already messed up with their imgdir
			//	PhotoQHelper::mergeDirs($dir2move, $moveTo);
		}
		
		//update the watermark directory database entry
		$oldWatermarkPath = get_option( "wimpq_watermark" );
		if($oldWatermarkPath){
			$oldWMFolder = $oldImgDir.'photoQWatermark/';
			$newWMFolder = $newImgDir.'photoQWatermark/';
			$newWatermarkPath = str_replace($oldWMFolder, $newWMFolder, $oldWatermarkPath);
			update_option( "wimpq_watermark", $newWatermarkPath);
		}
		
		/*
		//$publishedPhotos = $this->_db->getAllPublishedPhotos();
		
		
		
		
		//get all photo posts, foreach size, rebuild the photo
		foreach ( $this->_db->getAllPublishedPhotos() as $photo ){
			$photo->rebuild( array(), false, true, 
				true, ($oldImgDir).$this->_oc->ORIGINAL_IDENTIFIER, $this->_oc->getImgDir().$this->_oc->ORIGINAL_IDENTIFIER);
		}
		*/
	}
	
	
	/**
	 * Get a list of old (pre photoq 1.5) year-month folders.
	 *
	 * @return array
	 */
	function getOldYMFolders()
	{
		$match = '#^2[0-9]{3}_[01][0-9]$#';
		return PhotoQHelper::getMatchingDirContent($this->_oc->getImgDir(), $match);
	}
	
	/**
	 * Get content of old imgdir so we know what to move
	 *
	 * @return array
	 */
	function _getOldImgDirContent($oldImgDir, $includingOriginal = true)
	{
		//determine which folders we are allowed to move
		$allowedFolders  = array('qdir','photoQWatermark', 'myPhotoQPresets');
		if($includingOriginal)
			$allowedFolders[] = $this->_oc->getOriginalIdentifier();
		//only thing allowed to be moved are folders related to photoq
		$allowedFolders = array_merge($allowedFolders, $this->_oc->getImageSizeNames());
		for($i = 0; $i<count($allowedFolders); $i++)
			$allowedFolders[$i] = $oldImgDir . $allowedFolders[$i];
		
		//get all visible files from old img dir
		$match = '#^[^\.]#';//exclude hidden files starting with .
		$visibleFiles = PhotoQHelper::getMatchingDirContent($oldImgDir, $match);
		
		//folders that are in both array will be moved
		return array_intersect($allowedFolders, $visibleFiles);
		
	}
	
	
	/**
	 * Only used by updates from pre 1.5
	 * @param $oldImgDir
	 * @return unknown_type
	 */
	function _getOldImgDir($oldImgDir)
	{
		$newImgDir = $this->_oc->getImgDir();
		return str_replace('wp-content', $oldImgDir, $newImgDir);
	}
	
	function showFtpFileList(){
		$ftpDir = $this->_oc->getFtpDir();
		echo '<p>' . sprintf(__('Import the following photos from: %s', 'PhotoQ'), "<code> $ftpDir </code>") . '</p>';
		if (!is_dir($ftpDir)) {
    		$errMsg = new PhotoQErrorMessage("The directory <code>". $ftpDir . "</code> does not exist on your server.");
			$errMsg->show();
		}else{
			$ftpDirContent = PhotoQHelper::getMatchingDirContent($ftpDir,'#.*\.(jpg|jpeg|png|gif)$#i');
				foreach ($ftpDirContent as $file)
					echo '<input type="checkbox" name="ftpFiles[]" value="'. $file .'" checked="checked" /> '.basename($file).'<br/>';
		
			}
	}
	
	/**
	 * If more than 50 photos have been posted since the last time the reminder has been shown and if more than 100 days
	 * have elapsed since then, the reminder is shown.
	 * @return unknown_type
	 */
	function _showReminder(){
		$postedSinceLastReminder = get_option('wimpq_posted_since_reminded');
		$reminderThreshold = get_option('wimpq_reminder_threshold');
		if($reminderThreshold < 50)
			$reminderThreshold = 50;
		$then = get_option('wimpq_last_reminder_reset');
		$now = time();
		if($postedSinceLastReminder > $reminderThreshold && $now - $then > 100 * 86400){
			$ppBtn = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
						<input type="hidden" name="cmd" value="_s-xclick" />
						<input type="hidden" name="hosted_button_id" value="467690" />
						<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" name="submit" alt="PayPal - The safer, easier way to pay online!" />
						<img alt="PayPal - The safer, easier way to pay online!" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
					</form>';
			
			echo '<div class="updated fade">';
			echo '<p>'.__('PhotoQ is free software. Still, countless of hours went into its development and made PhotoQ what it is today. Have you ever thought of giving something back?', 'PhotoQ').'</p>';
			echo '<p>'.__('A donation is an easy way of showing the developer your appreciation.', 'PhotoQ').'</p>';
			echo '<br/><div class="donate">'.__('Yes, I support PhotoQ and would like to make a donation','PhotoQ').': </div><div class="donate ppal">'.$ppBtn.'</div><div class="nothanks"><a href="edit.php?page=whoismanu-photoq.php&amp;action=nothanks">'.__('No, thanks','PhotoQ').'</a></div><div class="nothanks"><a href="edit.php?page=whoismanu-photoq.php&amp;action=alreadydid">'.__('I already donated','PhotoQ').'</a></div>';
			echo '<br class="clr" /></div>';
		}
	}

	/**
	 * Hook called upon activation of plugin. 
	 * Installs/Upgrades the database tables.
	 *
	 */
	function activatePlugin()
	{
		PhotoQHelper::debug('enter activatePlugin()');
		$this->_db->upgrade($this->_version);
		PhotoQHelper::debug('leave activatePlugin()');
	}
	
	
	/**
	 * Hook called upon deactivation of plugin.
	 *
	 */
	function deactivatePlugin()
	{
		PhotoQHelper::debug('plugin deactivated');		
	}
	
	/**
	 * Returns the current version of PhotoQ
	 *
	 * @return string	The current version.
	 */
	function getVersion()
	{
		return $this->_version;
	}
	
	
	/**
	 * Process previously stored BatchSets
	 * @param $id integer	The id of the batch to be executed
	 * @return PhotoQBatchResult the result of the operation
	 */
	function executeBatch($id){
		PhotoQHelper::debug('enter executeBatch()');
		$timer =& PhotoQSingleton::getInstance('PhotoQTimers');
		$timer->start('batchProcessing');
		$bp =& new PhotoQBatchProcessor($this, $id);
		PhotoQHelper::debug('calling process()');
		return $bp->process();
	}
	
	function initQueue(){
		if(!isset($this->_queue))//lazy initialization, creating queue
			$this->_queue =& PhotoQSingleton::getInstance('PhotoQQueue');
	}


}//End Class PhotoQ



?>