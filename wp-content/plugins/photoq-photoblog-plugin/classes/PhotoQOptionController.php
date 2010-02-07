<?php

/**
 * Option controller subclass responsible for hanlding options of the PhotoQ plugin.
 * @author: M. Flury
 * @package: PhotoQ
 *
 */
class PhotoQOptionController extends OptionController
{
	var $ORIGINAL_IDENTIFIER = 'original';
	var $THUMB_IDENTIFIER = 'thumbnail';
	var $MAIN_IDENTIFIER = 'main';
	
	
	/**
	 * Reference to ErrorStack singleton
	 * @var object PEAR_ErrorStack
	 */
	var $_errStack;
	
	/**
	 * Reference to PhotoQDB singleton
	 * @var object PhotoQDB
	 */
	var $_db;
	
	
	var $_presetCategories;
	
	/**
	 * PHP5 type constructor
	 *
	 * @access public
	 */
	function __construct()
	{
		parent::__construct("wimpq_options", new PhotoQRenderOptionVisitor());
		
		//get the PhotoQ error stack for easy access and set it up properly
		$this->_errStack = &PEAR_ErrorStack::singleton('PhotoQ');
		
		//get the db object
		$this->_db =& PhotoQSingleton::getInstance('PhotoQDB');
		
		//get alternative original identifier if available
		$originalID = get_option( "wimpq_originalFolder" );
		if($originalID)
			$this->ORIGINAL_IDENTIFIER = $originalID;
			
		$this->_presetCategories = array(
					'photoblog' 	=> __('Photoblog Themes', 'PhotoQ'),
					'textblog'		=> __('Textblog Themes','PhotoQ'),
					'mixed'			=> __('Mixed (Text/Photoblog)','PhotoQ'),
					'commercial'	=> __('Commercial Themes', 'PhotoQ')
		);
			
		//establish default options
		$this->_defineAndRegisterOptions();
		
		//localize strings in js scripts etc. of option controller
		$this->localizeStrings(array(
				"switchLinkLabel" => __('Switch Sides', 'PhotoQ')
			)
		);
		
	}
	
	
	
	/**
	 * Defines all the plugin options and registers them with the OptionController.
	 *
	 * @access private
	 */
	function _defineAndRegisterOptions()
	{
		
		//define general tests not associated to options but that should be passed
		$this->addTest(new RO_SafeModeOffInputTest(array(&$this,'queueValidationError')));
		$this->addTest(new RO_GDAvailableInputTest(array(&$this,'queueValidationError')));
		$this->addTest(new RO_WordPressVersionInputTest(array(&$this,'queueValidationError'),'2.8.1','2.9'));

		//we try to define options that are used most frequently first so that they are found 
		//the quickest when sequentially searching through options. really need to look into 
		//alternative data structures as well to speed up this process.
		
		//path options
		if(!PhotoQHelper::isWPMU()){ //no imgdir and ftp setting in WPMU
			$imgdir =& new StrictValidationTextFieldOption(
				'imgdir',
				'wp-content',
				'',
				'',
				'<br />'. sprintf(__('Default is %s','PhotoQ'), '<code>wp-content</code>')
			);
			$imgdir->addTest(new DirExistsInputTest(array(&$this,'queueValidationError'),'',
			__('Image Directory not found','PhotoQ'). ': '));
			$imgdir->addTest(new FileWritableInputTest(array(&$this,'queueValidationError'),'',
			__('Image Directory not writable','PhotoQ'). ': '));
			$this->registerOption($imgdir);
			
			$enableFtp =& new CheckBoxOption(
				'enableFtpUploads',
				'0',
				__('Allow importing of photos from the following directory on the server','PhotoQ'). ': '
			);
			$enableFtp->addChild(
				new TextFieldOption(
					'ftpDir',
					'',
					'',
					'',
					'<br />'. sprintf(__('Full path (e.g., %s)','PhotoQ'),'<code>'.ABSPATH.'wp-content/ftp</code>')
				)
			);
			$this->registerOption($enableFtp);
		
		}//end if(!PhotoQHelper::isWPMU())
		
		$imagemagickPath =& new TextFieldSiteOption(
				'imagemagickPath',
				'',
				sprintf(_c('Absolute path to the ImageMagick convert executable. (e.g. %1$s ). Leave empty if %2$s is in the path.| example programname','PhotoQ'),'<code>/usr/bin/convert</code>','"convert"')
		);
		
		$this->registerOption($imagemagickPath);
		
		
		//image sizes
		
		$imageSizes =& new ImageSizeContainer(
			'imageSizes', 'ImageSizeOption', 
			array(&$this,'addImageSizeCallback'), array(&$this,'delImageSizeCallback'), 
			array('original'), array(),
			'',
			'<table width="100%" cellspacing="2" cellpadding="5" class="form-table noborder"><tr valign="top">
					<th scope="row">
						<label for="newExpComp-imageSizes">'.__('Name of new image size', 'PhotoQ').':</label>
					</th>
					<td>',
			'</td></tr></table>'
		);
		
		$imageSizes->addChild(new ImageSizeOption($this->THUMB_IDENTIFIER, '', '80', '60'), 0);
		$imageSizes->addChild(new ImageSizeOption($this->MAIN_IDENTIFIER), 0);
		
		$this->registerOption($imageSizes);
		
		
		$originalFolder =& new CompositeOption('originalFolder');
		$originalFolder->addChild(
			new CheckBoxOption(
				'hideOriginals',
				'0',
				__('Hide folder containing original photos. If checked, PhotoQ will attribute a random name to the folder.','PhotoQ'),
				'',
				''
			)
		);
		$this->registerOption($originalFolder);
		
		
		//next we define the views
		
		$contentView =& new PhotoQViewOption('content', true);
		$contentView->addChild(
			new CheckBoxOption(
				'inlineDescr',
				'1',
				__('Include photo description in post content (does not apply to freeform mode).','PhotoQ'),
				'<tr><th>'. __('Photo Description','PhotoQ'). ':</th><td>',
				'</td></tr>'
			)
		);
		$contentView->addChild(
			new CheckBoxOption(
				'inlineExif',
				'0',
				__('Include Exif data in post content (does not apply to freeform mode).','PhotoQ'),
				'<tr><th>'. __('Exif Meta Data','PhotoQ'). ':</th><td>',
				'</td></tr>'
			)
		);
	
		
		$excerptView =& new PhotoQViewOption('excerpt', true);
		
		
		$photoQViews = new RO_ExpandableCompositeOption(
			'views', 'PhotoQViewOption',
			array(&$this,'addViewCallback'), array(&$this,'delViewCallback'), 
			array(), array()
		);
		
		$photoQViews->addChild($contentView, 0);
		$photoQViews->addChild($excerptView, 0);
		$this->registerOption($photoQViews);
		
		
		//furhter options
		
		$cronOptions =& new CompositeOption('cronJobs');
		$cronOptions->addChild(
			new TextFieldOption(
				'cronFreq',
				'23',
				__('Cronjob runs every','PhotoQ'). ' ',
				'',
				__('hours','PhotoQ'),
				'3',
				'5'
			)
		);
		$cronOptions->addChild(
			new CheckBoxOption(
				'cronPostMulti',
				'0',
				__('Use settings of second post button for automatic posting.','PhotoQ'),
				'<p>', '</p>'
			)
		);
		if(!PhotoQHelper::isWPMU()){ //no imgdir and ftp setting in WPMU
			$cronOptions->addChild(
			new CheckBoxOption(
				'cronFtpToQueue',
				'0',
				__('When cronjob runs, automatically add FTP uploads to queue.','PhotoQ'),
				'<p>', '</p>'
				)
			);
		}
		$this->registerOption($cronOptions);

		$adminThumbs =& new CompositeOption('showThumbs', '1','','<table>','</table>');
		$adminThumbs->addChild(
			new TextFieldOption(
				'showThumbs-Width',
				'120',
				'',
				'<tr><td>'._c('Thumbs shown in list of published photos are maximum | ends with: px wide','PhotoQ'). '</td><td>',
				_c('px wide| starts with: thumbs ... are','PhotoQ'). ', ',
				'3',
				'3'
			)
		);
		$adminThumbs->addChild(
			new TextFieldOption(
				'showThumbs-Height',
				'60',
				'',
				' ',
				__('px high','PhotoQ'). '. <br/></td></tr>',
				'3',
				'3'
			)
		);
		$adminThumbs->addChild(
			new TextFieldOption(
				'photoQAdminThumbs-Width',
				'200',
				'',
				'<tr><td>'.__('Thumbs shown in PhotoQ edit dialogs are maximum','PhotoQ'). '</td><td>',
				__('px wide','PhotoQ'). ', ',
				'3',
				'3'
			)
		);
		$adminThumbs->addChild(
			new TextFieldOption(
				'photoQAdminThumbs-Height',
				'90',
				'',
				' ',
				__('px high','PhotoQ'). '. <br/></td></tr>',
				'3',
				'3'
			)
		);
		$adminThumbs->addChild(
			new TextFieldOption(
				'editPostThumbs-Width',
				'300',
				'',
				'<tr><td>'.__('Thumbs shown in WordPress post editing dialog are maximum','PhotoQ'). '</td><td>',
				__('px wide','PhotoQ'). ', ',
				'3',
				'3'
			)
		);
		$adminThumbs->addChild(
			new TextFieldOption(
				'editPostThumbs-Height',
				'400',
				'',
				' ',
				__('px high','PhotoQ'). '.</td></tr>',
				'3',
				'3'
			)
		);
		$this->registerOption($adminThumbs);
		
		$this->registerOption(
			new CheckBoxOption(
				'descrFromExif',
				'0',
				__('Get default description automatically from EXIF &ldquo;ImageDescription&rdquo; field.','PhotoQ') 
			)
		);
		
		$autoTitles = new CompositeOption('autoTitles');
		$autoTitles->addChild(
			new CheckBoxOption(
				'autoTitleFromExif',
				'0',
				__('Get auto title from EXIF &ldquo;ImageDescription&rdquo; field instead of filename, unless field is empty.','PhotoQ') . '<br/>'
			)
		);
		$autoTitles->addChild(
			new TextFieldOption(
				'autoTitleRegex',
				'', __('Custom Filter','PhotoQ'). ':', 
				'', 
				'<br/>
				<span class="setting-description">'. 
				sprintf(__('An auto title is a title that is generated automatically from the filename. By default PhotoQ creates auto titles by removing the suffix from the filename, replacing hyphens and underscores with spaces and by capitalizing the first letter of every word. You can specify an additional custom filter to remove more from the filename above. Perl regular expressions are allowed, parts of filenames that match the regex are removed (regex special chars %s need to be escaped with a backslash). Note that the custom filter is applied first, before any of the default replacements.','PhotoQ'),'<code>. \ + * ? [ ^ ] $ ( ) { } = ! < > | :</code>') 
				. '<br/>'.
				__('Examples: <code>IMG</code> to remove the string "IMG" from anywhere within the filename, <code>^IMG</code> to remove "IMG" from beginning of filename.','PhotoQ').'</span>'
			)
		);
		$autoTitles->addChild(
			new TextFieldOption(
				'autoTitleNoCapsShortWords',
				'2', 
				'<br/><br/>' . __('Do not capitalize words with','PhotoQ'). ' ', 
				'', 
				' ' . __('characters or less,', 'PhotoQ'),
				2,2
			)
		);
		$autoTitles->addChild(
			new TextFieldOption(
				'autoTitleCaps',
				'I', 
				' ' . __('except for the following words','PhotoQ'). ':<br/>', 
				'', 
				'
				<span class="setting-description">'. 
				__('(Separate words with commas)', 'PhotoQ') 
				. '</span><br/><br/>',
				100,200
			)
		);
		$autoTitles->addChild(
			new TextAreaOption(
				'autoTitleNoCaps',
				_c('for, and, nor, but, yet, both, either, neither, the, for, with, from, because, after, when, although, while|english words that are not capitalized', 'PhotoQ'), 
				' ' . __('Do not capitalize any of the following words (Separate words with commas)','PhotoQ'). ':<br/>', 
				'', 
				'',
				2,100
			)
		);
		$this->registerOption($autoTitles);
		
		
		$this->registerOption(
			new TextFieldOption(
				'postMulti',
				'999',
				__('Second post button posts ','PhotoQ'),
				'',
				__(' photos at once.','PhotoQ'),
				'3',
				'3'
			)
		);
		
		$this->registerOption(
			new CheckBoxOption(
				'foldCats',
				'0',
				__('Fold away category lists per default.','PhotoQ')
			)
		);
		
		$this->registerOption(
			new CheckBoxOption(
				'deleteImgs',
				'1',
				__('Delete image files from server when deleting post.','PhotoQ')
			)
		);

		$this->registerOption(
			new CheckBoxOption(
				'enableBatchUploads',
				'1',
				__('Enable Batch Uploads.','PhotoQ')
			)
		);

		$statusArray = array("draft", "private", "publish");
		$postStatus = new DropDownList(
				 'qPostStatus',
				 'publish',
				 __('This is the default status of posts posted via PhotoQ.','PhotoQ')
		);
		$postStatus->populate(PhotoQHelper::arrayCombine($statusArray,$statusArray));
		$this->registerOption($postStatus);
		
		$this->registerOption(
			new AuthorDropDownList(
				 'qPostAuthor',
				 '1',
				 __('PhotoQ will fall back to this author if no author can be determined by any other means. This is for example the case if photos are automatically added to the queue through cronjobs.','PhotoQ')
			)
		);
		
		$this->registerOption(
			new CategoryDropDownList(
				 'qPostDefaultCat',
				 '1',
				 __('This is the default category for posts posted via PhotoQ.','PhotoQ')
			)
		);
		
		$this->registerOption(
			new TextFieldOption(
				 'qPostDefaultTags',
				 '',
				 __('Every post posted via PhotoQ has these default tags:','PhotoQ')
			)
		);
		
		$roleOptions = new CompositeOption('specialCaps','','','<table><tr>','</tr></table>');
		$roleOptions->addChild(
			new PhotoQRoleOption(
				'editorCaps','editor',
				array('use_primary_photoq_post_button','use_secondary_photoq_post_button','reorder_photoq'),
				__('Editor','PhotoQ'),
				'<td>',
				'</td>'
			)
		);
		$roleOptions->addChild(
			new PhotoQRoleOption(
				'authorCaps','author',
				array('use_primary_photoq_post_button','use_secondary_photoq_post_button','reorder_photoq'),
				__('Author','PhotoQ'),
				'<td>',
				'</td>'
			)
		);
		
		$this->registerOption($roleOptions);
		
				//watermark options
		$watermark =& new CompositeOption('watermarkOptions');
		$watermarkPosition =& new RadioButtonList(
				'watermarkPosition',
				'BL',
				'',
				'<tr valign="top"><th scope="row">'. __('Position','PhotoQ'). ': </th><td>',
				'</td></tr>'
		);
		$valueLabelArray = array(
			'BR' => __('Bottom Right','PhotoQ'),
			'BL' => __('Bottom Left','PhotoQ'),
			'TR' => __('Top Right','PhotoQ'),
			'TL' => __('Top Left','PhotoQ'),
			'C' => __('Center','PhotoQ'),
			'R' => __('Right','PhotoQ'),
			'L' => __('Left','PhotoQ'),
			'T' => __('Top','PhotoQ'),
			'B' => __('Bottom','PhotoQ'),
			'*'  => __('Tile','PhotoQ')
		);
		$watermarkPosition->populate($valueLabelArray);
		$watermark->addChild($watermarkPosition);
		
		$watermark->addChild(
			new TextFieldOption(
				'watermarkOpacity',
				'100',
				'',
				'<tr valign="top"><th scope="row">'. __('Opacity','PhotoQ'). ': </th><td>',
				'%</td></tr>',
				'2'
			)
		);
		
		$watermark->addChild(
			new TextFieldOption(
				'watermarkXMargin',
				'20',
				__('left/right','PhotoQ'). ':',
				'<tr valign="top"><th scope="row">'. __('Margins','PhotoQ'). ': </th><td>',
				'px, ',
				'2',
				'2'
			)
		);
		
		$watermark->addChild(
			new TextFieldOption(
				'watermarkYMargin',
				'20',
				__('top/bottom', 'PhotoQ'). ':',
				'',
				'px<br/>('. __('Values smaller than one are interpreted as percentages instead of pixels.','PhotoQ'). ')</td></tr>',
				'2',
				'2'
			)
		);
		
		$this->registerOption($watermark);
		
		//build field checkbox options
		$this->registerOption(
			new CheckBoxOption(
				'fieldAddPosted',
				'1',
				__('Add to already posted as well.','PhotoQ')
			)
		);
		
		$this->registerOption(
			new CheckBoxOption(
				'fieldDeletePosted',
				'0',
				__('Delete from already posted as well.','PhotoQ')
			)
		);
		$this->registerOption(
			new CheckBoxOption(
				'fieldRenamePosted',
				'1',
				__('Rename already posted as well.','PhotoQ')
			)
		);

		
		
		
		
		
		//exif related settings
		//first the reorderable list of discovered exif tags
		$exifTags =& new RO_ReorderableList('exifTags');
		if($tags = get_option( "wimpq_exif_tags" )){
			foreach($tags as $key => $value){
				$exifTags->addChild(new PhotoQExifTagOption($key, $value));
			}
		}
		//localize strings
		$exifTags->localizeStrings(array(
				"selectedListLabel" => __('selected', 'PhotoQ'),
				"deselectedListLabel" => __('deselected', 'PhotoQ')
			)
		);
		$this->registerOption($exifTags);
		
		//now the exif display options
		$exifDisplayOptions =& new CompositeOption('exifDisplay');
		$exifDisplayOptions->addChild(
			new TextFieldOption(
				'exifBefore',
				attribute_escape('<ul class="photoQExifInfo">'),
				'',
				'<table class="optionTable"><tr><td>'. __('Before List','PhotoQ'). ': </td><td>',
				sprintf(__('Default is %s','PhotoQ'), '<code>'.attribute_escape('<ul class="photoQExifInfo">').'</code>') .'</td></tr>',
				'30'
			)
		);
		$exifDisplayOptions->addChild(
			new TextFieldOption(
				'exifAfter',
				attribute_escape('</ul>'),
				'',
				'<tr><td>'. __('After List','PhotoQ'). ': </td><td>',
				sprintf(__('Default is %s','PhotoQ'), '<code>'.attribute_escape('</ul>').'</code>') .'</td></tr>',
				'30'
			)
		);
		$exifDisplayOptions->addChild(
			new TextFieldOption(
				'exifElementBetween',
				'',
				'',
				'<tr><td>'. __('Between Elements','PhotoQ'). ': </td><td>',
				'</td></tr>',
				'30'
			)
		);
		$exifDisplayOptions->addChild(
			new TextAreaOption(
				'exifElementFormatting',
				attribute_escape('<li class="photoQExifInfoItem"><span class="photoQExifTag">[key]:</span> <span class="photoQExifValue">[value]</span></li>'),
				'',
				'<tr><td>'. __('Element Formatting','PhotoQ'). ': </td><td>
				<span class="setting-description">'
				.sprintf(__('You can specify the HTML that should be printed for each element here. Two shortags %1$s and %2$s are available. %1$s is replaced with the name of the EXIF tag, %2$s with its value. Here is an example, showing the default value: %3$s', 'PhotoQ'),'[key]','[value]','<code>'.attribute_escape('<li class="photoQExifInfoItem"><span class="photoQExifTag">[key]:</span> <span class="photoQExifValue">[value]</span></li>').'</code>').'
				</span></td></tr><tr><td/><td>',
				'</td></tr></table>',
				2, 75
			)
		);
		$this->registerOption($exifDisplayOptions);
		
		
		
		
		
		//overwrite default options with saved options from database
		$this->load();

		//populate lists of image sizes that depend on runtime stuff and cannot be populated before
		$this->_populateAllViews();
		
		//$contentView->populate($this->getImageSizeNames(),$this->ORIGINAL_IDENTIFIER == 'original');
		//$excerptView->populate($this->getImageSizeNames(),$this->ORIGINAL_IDENTIFIER == 'original');
	
		//check for existence of cache directory
		//convert backslashes (windows) to slashes
		$cleanAbs = str_replace('\\', '/', ABSPATH);
		$this->addTest( new DirExistsInputTest(
			array(&$this,'queueValidationError'),
			preg_replace('#'.$cleanAbs.'#', '', $this->getCacheDir()), 
			__('Cache Directory not found','PhotoQ'). ': ')
		);
		$this->addTest( new FileWritableInputTest(
			array(&$this,'queueValidationError'),
			preg_replace('#'.$cleanAbs.'#', '', $this->getCacheDir()), 
			__('Cache Directory not writeable','PhotoQ'). ': ')
		);
	}

	/**
	 * Helper function to populate all currently registered views
	 * @return unknown_type
	 */	
	function _populateAllViews(){
		$numKids = $this->_options['views']->countChildren();
		$imgSizeNames = $this->getImageSizeNames();
		for($i = 0; $i < $numKids; $i++){
			$currentView =& $this->_options['views']->getChild($i);
			$currentView->populate($imgSizeNames,$this->ORIGINAL_IDENTIFIER == 'original');
			//print_r($currentView);
		}
	}
	
	/**
	 * Helper function to unpopulate all currently registered views
	 * @return unknown_type
	 */	
	function _unpopulateAllViews(){
		$numKids = $this->_options['views']->countChildren();
		for($i = 0; $i < $numKids; $i++){
			$currentView =& $this->_options['views']->getChild($i);
			$currentView->unpopulate();
		}
	}
	

	/**
	 * Determine whether the view with the given name is managed by photoq, i.e. its settings
	 * is not 'none'.
	 * @param $viewName			string the view to check
	 * @return boolean
	 */
	function isManaged($viewName){
		return $this->getValue($viewName . 'View-type') !== 'none';
	}
	
	function onCronimportFtpUploadsToQueue(){
		return !PhotoQHelper::isWPMU() && $this->getValue('enableFtpUploads') && $this->getValue('cronFtpToQueue');
	}
	
	/**
	 * initialize stuff that depends on runtime configuration so that 
	 * what is displayed represents the changes from last update.
	 *
	 */
	function initRuntime()
	{
		//populate lists of image sizes that depend on runtime stuff and cannot be populated before
		$this->_unpopulateAllViews();
		$this->_populateAllViews();
		
		//test for presence of imageMagick
		$imagemagickTest = new PhotoQImageMagickPathCheckInputTest(array(&$this,'showImageMagickValError'));
		$imagemagickTest->validate($this->_options['imagemagickPath']);
	}
	
	function showImageMagickValError($valError){
		$this->_options['imagemagickPath']->setTextAfter('<br/>'. $valError);
	}
	
	/**
	 * Callback function that is called whenever a new image size is added in the PhotoQ Settings.
	 * @param $name	String	the name of the new image size
	 * @return true on success, false on failure
	 */
	function addImageSizeCallback($name){
		$imageSizes =& $this->_options['imageSizes'];
		if($name != 'original' && !array_key_exists($name, $imageSizes->getValue())){
			return true;
		}else{
			$this->_errStack->push(PHOTOQ_EXPCOMP_ENTRY_EXISTS,'error');
			return false;
		}
	}
	
	/**
	 * Callback function that is called whenever a image size is deleted in the PhotoQ Settings.
	 * @param $name	String	the name of the image size to be deleted
	 * @return true on success, false on failure
	 */
	function delImageSizeCallback($name)
	{
		$imageSizeDir = $this->getImgDir() . $name;
		//remove corresponding dirs from server
		if(!file_exists($imageSizeDir) || PhotoQHelper::recursiveRemoveDir($imageSizeDir)){
			return true;
		}else{
			$this->_errStack->push(PHOTOQ_IMGSIZE_DEL_FAILED, 'error', array('imgDir' => $imageSizeDir));
			return false;
		}
	}
	
	/**
	 * Callback function that is called whenever a new view is added in the PhotoQ Settings.
	 * @param $name	String	the name of the new view
	 * @return true on success, false on failure
	 */
	function addViewCallback($name, $allowDuplicates = false){
		//do not add duplicates
		$views =& $this->_options['views'];
		if(!$allowDuplicates && array_key_exists($name, $views->getValue())){
			$this->_errStack->push(PHOTOQ_VIEW_EXISTS,'error', array('viewname' => $name));
			return false;
		}
		//do not add if a meta field with same name exists
		$fieldNames = $this->_db->getFieldNames();
		if(in_array($name, $fieldNames)){
			$this->_errStack->push(PHOTOQ_FIELD_EXISTS,'error', array('fieldname' => $name));
			return false;
		}
		//add a custom field with the same name to all published photos. this field will hold the view.
		$this->_db->addFieldToPublishedPosts($name);
		
		return true;
	}
	
	/**
	 * Callback function that is called whenever a view is deleted in the PhotoQ Settings.
	 * @param $name	String	the name of the view to be deleted
	 * @return true on success, false on failure
	 */
	function delViewCallback($name)
	{
		//delete the corresponding custom fields from published photoq posts
		$this->_db->deleteFieldFromPublishedPosts($name);
		return true;
	}
	

	/**
	 * Returns the current qdir.
	 * @return unknown_type	String the current qdir.
	 */	
	function getQDir(){
		return $this->getImgDir().'qdir/';
	}
	
	/**
	 * Returns the cache directory used by phpThumb. This is now fixed to wp-content/photoQCache.
	 *
	 * @return string	The cache directory.
	 */
	function getCacheDir(){
		/*if(PhotoQHelper::isWPMU()){
			return BLOGUPLOADDIR . 'photoQCache/';
		}*/
		return str_replace('\\', '/', WP_CONTENT_DIR) . '/photoQCache/';
	}
	
	/**
	 * This is a folder where the user can place his/her own presets
	 * @return string
	 */
	function getMyPresetsDir(){
		return str_replace('\\', '/', WP_CONTENT_DIR) . '/myPhotoQPresets/';
	}
	
	function getPresetsDir(){
		return PHOTOQ_PATH.'presets/';
	}
	
	function getImgDir(){
		if(PhotoQHelper::isWPMU()){
			return BLOGUPLOADDIR;
		}else{
			// if path is relative, assumes relative to ABSPATH -> ABSPATH added
			// if path is absolute, returned as is
			$dir = path_join( ABSPATH, trim($this->getValue('imgdir')) );
			//prepend ABSPATH to $imgdir if it is not already there
			//$dirPath = str_replace(ABSPATH, '', trim($this->getValue('imgdir')));
			//$dir = rtrim(ABSPATH . $dirPath, '/');
			return str_replace('\\',  '/', $dir) . '/';
		}
	}
	
	function getFtpDir(){
		//for windows directories (e.g. c:/) we don't want a first slash
		$firstSlash = '/';
		if(preg_match('/^[a-zA-Z]:/', $this->getValue('ftpDir')))
			$firstSlash = '';
		return $firstSlash.trim($this->getValue('ftpDir'), '\\/').'/';
	}
	
	/**
	 * Getter for main identifier, can now be called statically
	 * @return unknown_type
	 */
	function getMainIdentifier()
	{
		$vars = get_class_vars(__CLASS__); 
		return $vars['MAIN_IDENTIFIER'];
		//return $this->MAIN_IDENTIFIER;
	}
	/**
	 * Getter for thumb identifier, can now be called statically
	 * @return unknown_type
	 */
	function getThumbIdentifier()
	{
		$vars = get_class_vars(__CLASS__);
		return $vars['THUMB_IDENTIFIER'];
		//return $this->THUMB_IDENTIFIER;
	}
	
	function getOriginalIdentifier()
	{
		return $this->ORIGINAL_IDENTIFIER;
	}
	
	function getPresetCategories()
	{
		return $this->_presetCategories;
	}
	
	
	/**
	 * Returns an array containing all image sizes.
	 *
	 * @return array	the names of all registered imageSizes
	 */
	function getImageSizeNames()
	{
		return array_keys($this->getValue('imageSizes'));
	}
	
	/**
	 * Returns an array containing all view names.
	 *
	 * @return array	the names of all registered views
	 */
	function getViewNames()
	{
		return array_keys($this->getValue('views'));
	}
	
	/**
	 * Returns an array containing names of views that changed during last update.
	 *
	 * @return array	the names of all changed views
	 */
	function getChangedViewNames($changedSizes = array(), $updateExif = false, $updateOriginalFolder = false){
		//get all the views that changed during the last update
		$changedViewNames = $this->_getChangedExpCompElements('views');
		
		//go through all views and check whether anything relevant to the view changed (e.g. the size of images
		//used inside the view) and an update of the view is necessary even if it didn't change itself.
		foreach($this->getViewNames() as $currentViewName){
			//if it is already in the list we don't have to check it
			if(!in_array($currentViewName, $changedViewNames)){
				//freeform views -> establish list of tags that make a change necessary, check whether view contains one of these
				if($this->getValue($currentViewName . 'View-type') == 'freeform' &&
					PhotoQHelper::containsAnyOfTheseShorttags(
						stripslashes(html_entity_decode($this->getValue($currentViewName . 'View-freeform'))),
						$this->_buildListOfRelevantTags($changedSizes,$updateExif,$updateOriginalFolder)
					)
				){
					$changedViewNames[] = $currentViewName;
				}elseif($this->getValue($currentViewName . 'View-type') == 'single' && 
					in_array($this->getValue($currentViewName . 'View-singleSize'), $changedSizes)
				){ //check single sizes -> did corresp. size change?
					$changedViewNames[] = $currentViewName;
				}elseif($this->getValue($currentViewName . 'View-type') == 'imgLink' && 
					(	
						in_array($this->getValue($currentViewName . 'View-imgLinkSize'), $changedSizes) ||
						in_array($this->getValue($currentViewName . 'View-imgLinkTargetSize'), $changedSizes)
					)
				){ //check img links -> did corresp. sizes change?
					$changedViewNames[] = $currentViewName;
				}elseif($currentViewName == 'content' && $updateExif && $this->getValue('inlineExif')){
					//finally if we have inlined exif and it changed we may need to update the content view
					$changedViewNames[] = $currentViewName;
				}
			}
		}
		return array_unique($changedViewNames);
	}
	
	/**
	 * Builds the list of shorttags that induce a view change of any view containing them
	 * @param $changedSizes			Array		the image sizes that changed in last update
	 * @param $updateExif			boolean		whether exif data changed in last update
	 * @param $updateOriginalFolder	boolean		whether original folder chagned in last update
	 * @return 						Array		list of tags to be checked
	 */
	function _buildListOfRelevantTags($changedSizes, $updateExif, $updateOriginalFolder){
		$tags2Test = array();
		if($updateExif) $tags2Test[] = 'exif';
		if($updateOriginalFolder){ 
			$tags2Test[] = 'imgUrl|original';
			$tags2Test[] = 'imgPath|original';
		}
		foreach ($changedSizes as $currentSize){
			$tags2Test[] = 'imgWidth|'.$currentSize;
			$tags2Test[] = 'imgHeight|'.$currentSize;
		}
		return $tags2Test;
	}
	
	/**
	 * Returns an array containing names of image sizes that changed during last update.
	 *
	 * @return array	the names of all changed imageSizes
	 */
	function getChangedImageSizeNames()
	{
		return $this->_getChangedExpCompElements('imageSizes');
	}
	
	/**
	 * Low level function that returns the new elements of an expandable composite plus all the elements that changed
	 * during the last update.
	 * @param $containerName String	the name of the expandable composite option
	 * @return array	containing names of elements that changed.
	 */
	function _getChangedExpCompElements($containerName){
		if($this->hasChanged($containerName)){
			//get new elements
			if(!is_array($this->getOldValues($containerName)))
				$oldVals = array();
			else
				$oldVals = $this->getOldValues($containerName);
			$oldNames = array_keys($oldVals);
			$currentNames = array_keys($this->getValue($containerName));
			$currentNames = array_filter($currentNames, array(&$this,'_filterDefaultNames_'.$containerName));
			$newNames = array_diff($currentNames,$oldNames);
			$containerOption =& $this->_options[$containerName];
			return array_unique(array_merge($newNames,$containerOption->getChangedChildrenNames()));
		}else
			return array();	
	}
	
	/**
	 * Returns false if the name given is a default name.
	 * @param $name
	 * @return unknown_type
	 */
	function _filterDefaultNames_imageSizes($name){
		$defaultNames = array($this->MAIN_IDENTIFIER, $this->THUMB_IDENTIFIER);
		return !in_array($name, $defaultNames);
	}
	/**
	 * Returns false if the name given is a default name.
	 * @param $name
	 * @return unknown_type
	 */
	function _filterDefaultNames_views($name){
		$defaultNames = array('content', 'excerpt');
		return !in_array($name, $defaultNames);
	}
	
	
	/**
	 * Returns an array containing names of imagesizes that have a watermark.
	 * @return array
	 */
	function getImageSizeNamesWithWatermark(){
		$imageSizes =& $this->_options['imageSizes'];
		return $imageSizes->getImageSizeNamesWithWatermark();
	}
	
	/**
	 * Goes through all exif tags that changed. Returns two arrays, the first
	 * one containing the names of tags that got added to tagfromexif, the
	 * the second one containing the names of those who got deleted.
	 * @return unknown_type
	 */
	function getAddedDeletedTagsFromExif(){
		$changedTags =& $this->_options['exifTags']->getChildrenWithAttribute();
		$added = array();
		$deleted = array();
		foreach($changedTags as $tag){
			//get the checkbox that determines tagFromExif status
			$checkBox =& $tag->getOptionByName($tag->getName().'-tag');
			if($checkBox->getValue() == 1)
				$added[] = $tag->getName();
			else
				$deleted[] = $tag->getName();			
		}
		return array($added, $deleted);
	}
	
	/*function getAllTagsFromExif(){
		$result = array();
		$allTags =& $this->_options['exifTags']->getChildrenWithAttribute('getValue');
		foreach($allTags as $tag)
			$result[] = $tag->getName();
		
		return $result;
	}*/
	
	
	function getOldValues($containerName)
	{
		$opt =& $this->_options[$containerName];
		return $opt->_oldValues;
	}
	
	/**
	 * Validate options and record any errors occuring
	 */
	/*function validateOptions(){
		//do the input validation
		$validationErrors = parent::validate();
		if(count($validationErrors)){
			foreach($validationErrors as $valError){
				$this->_errStack->push(PHOTOQ_ERROR_VALIDATION,'error', array(), $valError);
			}
		}
	}*/
	
	/**
	 * Callback called whenever an error fails validation
	 * @param $valError string the error message
	 * @return unknown_type
	 */
	function queueValidationError($valError)
	{
		$this->_errStack->push(PHOTOQ_ERROR_VALIDATION,'error', array(), $valError);
	}
	
	function renderListOfPresets(){
		
		
		$presetDirs = array(
					$this->getMyPresetsDir() => __('My PhotoQ Presets', 'PhotoQ')
		);
		
		foreach($this->_presetCategories as $key => $value){
			$presetDirs[$this->getPresetsDir().$key.'/'] = $value;
		}
		
		_e('Choose your Theme Preset: ', 'PhotoQ');
		echo '<select name="presetFile" id="presetFile">';
		
		foreach($presetDirs as $path => $displayName){
		
			$presetFilePaths = PhotoQHelper::getMatchingDirContent($path,'#\.xml$#');
			if(count($presetFilePaths))
				echo '<optgroup label="'.$displayName.'">';
			foreach ($presetFilePaths as $presetPath){
				echo '<option value="'.$presetPath.'">'.PhotoQHelper::niceDisplayNameFromFileName(basename($presetPath)).'</option>';
			}
			if(count($presetFilePaths))
				echo '</optgroup>';

		}
		echo '</select>';
	}
	
	/**
	 * Show array of options as rows of the table
	 * @param $optionArray
	 * @return unknown_type
	 */
	function showOptionArray($optionArray){
		foreach ($optionArray as $optName => $optLabel){
			echo '<tr valign="top">'. PHP_EOL;
			echo '   <th scope="row">'.$optLabel.'</th>'.PHP_EOL.'   <td>';
			$this->render($optName);
			echo '</td>'.PHP_EOL.'</tr>'. PHP_EOL;
		}
	}
	
	/**
	 * Imports options and fields from the XML file given.
	 * @param $xmlFilename	the file to import from.
	 * @return boolean	true on success, false on failure
	 */
	function importFromXML($xmlFilename){
		//parse the xml file
		$xmlParser = new PhotoQXMLParser($xmlFilename);
		$xmlParser->parse();

		//store the parsed options if they validate
		if($xmlParser->validate()){
				
			$optionArray = $xmlParser->getParsedOptions();
			
			$fieldArray = $xmlParser->getParsedFields();
			$catsArray = $xmlParser->getParsedCats();
			//right now we only support one default category
			$defaultCat = null;
			if(!empty($catsArray))
				$defaultCat = $catsArray[0];
				
			//delete views that are no longer used in the new settings
			$this->_deleteObsoleteViews($this->getViewNames(), $xmlParser->getViewNames());
			
			//delete image sizes that are no longer used in the new settings
			$this->_deleteObsoleteImageSizes($this->getImageSizeNames(), $xmlParser->getImageSizeNames());
			
			//add custom fields required by views setting of xml file,
			//conflicts with fields will not happen as this is already tested in validation.
			foreach ($xmlParser->getViewNames(true) as $view){
				$this->addViewCallback($view, true);
			}

			//it may happen that some fields are already there. Do not show an error in this case
			$this->_errStack->pushCallback(array('PhotoQErrorHandler', 'mutePHOTOQ_FIELD_EXISTS'));

			//create fields required by the xml file
			foreach($fieldArray as $fieldname){
				$this->_db->insertField($fieldname, true);
			}
			
			//add default tags to all photoq posts
			if(isset($optionArray['qPostDefaultTags']['qPostDefaultTags']) && !empty($optionArray['qPostDefaultTags']['qPostDefaultTags'])){
				$newTags = preg_split("/[\s]*,[\s]*/", $optionArray['qPostDefaultTags']['qPostDefaultTags']);
				$postIDs = $this->_db->getAllPublishedPhotoIDs();
				foreach($postIDs as $id){
					//update the tags in the database
			 		wp_set_post_tags( $id, add_magic_quotes($newTags), true );
				}
				//update all posts in the queue
				$qEntries = $this->_db->getQueueIDTagPairs();
				foreach($qEntries as $entry){
					$oldTags = preg_split("/[\s]*,[\s]*/", $entry->q_tags);
					$tagString = implode(',',array_unique(array_merge($newTags,$oldTags)));
					$this->_db->updateTags($entry->q_img_id, $tagString);	
				}
			}
			
			//same for default category
			if($defaultCat){
				//create it if it does not exist
				if(!category_exists($defaultCat)){
					$catID = wp_insert_category(array('cat_name' => $defaultCat));
					if ( is_wp_error($catID) )
						$defaultCat = NULL;	
				}else
					$catID = get_cat_id($defaultCat);
				//get all posts and add default category to list of cats
				$postIDs = $this->_db->getAllPublishedPhotoIDs();
				foreach($postIDs as $id){
					//update the tags in the database
			 		wp_set_object_terms($id, $defaultCat, 'category', true);
				}
				//update all posts in the queue
				if ( ! is_wp_error($catID) ){
					$qIds = $this->_db->getAllQueuedPhotoIDs();
					foreach($qIds as $id){
						if (!in_array($catID, $this->_db->getCategoriesByImgId($id)))
						$this->_db->insertCategory($id, $catID);
					}
				}
			}
			
			//store the imported options to the database
			$storedOptions = get_option($this->_optionsDBName);
			foreach($optionArray as $key => $val){
				if(!is_array($storedOptions) || array_key_exists($key,$storedOptions))
					$storedOptions[$key] = PhotoQHelper::arrayHtmlEntities($val);
			}
			update_option($this->_optionsDBName, $storedOptions);
			
			//reload to make the changes active
			$this->load();
			
			
			return true;

		}
		
		return false;
	}
	
	/**
	 * Delete custom fields associated to views that are no longer present in 
	 * the newly imported XML view settings
	 * @param $currentViews	array of current views
	 * @param $allParsedViews	array of views in the imported XML settings
	 * @return unknown_type
	 */
	function _deleteObsoleteViews($currentViews, $allParsedViews){
		$this->_deleteObsoleteElements('views', array(&$this,'delViewCallback'),$currentViews,$allParsedViews); 
		/*if(!empty($allParsedViews)){
			$obsoleteViews = array_diff($currentViews,$allParsedViews);
			print_r($obsoleteViews);
			foreach($obsoleteViews as $view){
				$this->delViewCallback($view);
				//also remove it from the options, otherwise it will still show
				//up until the page is refreshed.
				$viewOption =& $this->_options['views'];
				$viewOption->removeChild($view);
			}
		}*/
	}
	
	function _deleteObsoleteImageSizes($currentViews, $allParsedViews){
		$this->_deleteObsoleteElements('imageSizes', array(&$this,'delImageSizeCallback'),$currentViews,$allParsedViews); 
	}
	
	
	function _deleteObsoleteElements($compName, $compCallback, $currentViews, $allParsedViews){
		if(!empty($allParsedViews)){
			$obsoleteViews = array_diff($currentViews,$allParsedViews);
			foreach($obsoleteViews as $view){
				call_user_func_array($compCallback, array($view));
				//also remove it from the options, otherwise it will still show
				//up until the page is refreshed.
				$viewOption =& $this->_options[$compName];
				$viewOption->removeChild($view);
			}
		}
	}
	
}


/**
 * The PhotoQRenderOptionVisitor:: is responsible for rendering of the options. It 
 * renders every visited option in HTML.
 *
 * @author  M. Flury
 * @package PhotoQ
 */
class PhotoQRenderOptionVisitor extends RenderOptionVisitor
{
	
	
	 
	/**
	 * Method called whenever a
	 * ImageSizeOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object ImageSizeOption &$dropDownList	Reference to visited option.
	 */
	 function visitImageSizeOptionBefore(&$imageSize)
	 {
	 	//$deleteLink = '';
	 	/*if($imageSize->isRemovable()){
	 		$deleteLink = 'options-general.php?page=whoismanu-photoq.php&amp;action=deleteImgSize&amp;entry='.$imageSize->getName();
	 		$deleteLink = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($deleteLink, 'photoq-deleteImgSize' . $imageSize->getName()) : $deleteLink;
	 		$deleteLink = '<a href="'.$deleteLink.'" class="delete" onclick="return confirm(\'Are you sure?\');">Delete</a>';
	 	}*/
	 	print '<table width="100%" cellspacing="2" cellpadding="5" class="form-table noborder">
	 				<tr valign="top">
	 					<th class="imageSizeName"> ' .$imageSize->getName().'</th>
	 					<td></td>
	 				</tr>';
	 	
	 }
	 
	 /**
	 * Method called whenever a
	 * ImageSizeOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object ImageSizeOption &$imageSize	Reference to visited option.
	 */
	 function visitImageSizeOptionAfter(&$imageSize)
	 {
	 	print "</table>";
	 }
	 
	 
	 function visitPhotoQExifTagOptionBefore(&$option)
	 {
	 	print '<b>'.$option->getExifKey().'</b> ( '.$option->getExifExampleValue().' )<br/>'.PHP_EOL;
	 }
	 
	function visitPhotoQRoleOptionBefore(&$option)
	 {
	 	print $option->getTextBefore();
	 	print $option->getLabel().':'.PHP_EOL;
	 	print '<ul>'.PHP_EOL;
	 	
	 }
	 
	function visitPhotoQRoleOptionAfter(&$option)
	 {
	 	print '</ul>'.PHP_EOL;
	 	print $option->getTextAfter();
	 }
	 
	 
	 function visitPhotoQViewOptionBefore(&$imageSize)
	 {
	 	print '<table width="100%" cellspacing="2" cellpadding="5" class="form-table noborder">
	 				<tr valign="top">
	 					<th class="viewName"> ' .$imageSize->getName().'</th>
	 					<td></td>
	 				</tr>';
	 	
	 }
	 
	 function visitPhotoQViewOptionAfter(&$imageSize)
	 {
	 	print "</table>";
	 }
	 
	 	
}




class ImageSizeContainer extends RO_ExpandableCompositeOption
{
	/**
	 * Check whether we find a value for this option in the array pulled from
	 * the database. If so adopt this value. Pass the array on to all the children
	 * such that they can do the same.
	 *
	 * @param array $storedOptions		Array pulled from database.
	 * @access public
	 */
	/*function load($storedOptions)
	{
		if(is_array($storedOptions)){
			if(array_key_exists($this->getName(), $storedOptions)){
				$this->setValue($storedOptions[$this->getName()]);
			}
			//register all ImageSizes that can be added/removed on runtime
			foreach ($this->getValue() as $key => $value){
				//only add if not yet there and removable
				if(!$this->getOptionByName($key) && $value) $this->addChild(new ImageSizeOption($key, '1'));
			}
			parent::load($storedOptions);
		}
		
		

	}*/
	
	/**
	 * Stores own values in addition to selected childrens values in associative 
	 * array that can be stored in Wordpress database.
	 * 
	 * @return array $result		Array of options to store in database.
	 * @access public
	 */
	/*function store()
	{
		$result = array();
		$result[$this->_name] = $this->getValue();
		$result = array_merge($result, parent::store());
		return $result;
	}*/
	
	/**
	 * Add an option to the composite. And add its name to the list of names (= value of ImageSizeContainer)
	 * 
	 * @param object ReusableOption &$option  The option to be added to the composite.
	 * @return boolean	True if options could be added (composite), false otherwise.
	 * @access public
	 */
	/*function addChild(&$option)
	{	
		if(is_a($option, 'ImageSizeOption')){
			$newValue = $this->getValue();
			$newValue[$option->getName()] = $option->isRemovable();
			$this->setValue($newValue);
			return parent::addChild($option);
		}
		return false;
	}*/
	
	/**
	 * Remove an option from the composite.	
	 * 
	 * @param string $name  The option to be removed from the composite.
	 * @return boolean 		True if existed and removed, False otherwise.
	 * @access public
	 */
	/*function removeChild($name)
	{	
		$newValue = $this->getValue();
		if($newValue[$name]){ //only remove images sizes that are allowed to be removed
			unset($newValue[$name]);
			$this->setValue($newValue);
			return parent::removeChild($name);
		}
		return false;
	}*/
	
	/**
	 * Returns an array containing names of imagesizes that changed during
	 * last update.
	 * @return array
	 */
	/*function getChangedImageSizeNames(){
		return $this->_getImageSizeNamesWithAttribute();
	}*/
	
	/**
	 * Returns an array containing names of imagesizes that have a watermark.
	 * @return array
	 */
	function getImageSizeNamesWithWatermark(){
		return $this->getChildrenNamesWithAttribute('hasWatermark');
	}
	
	/**
	 * Low level function that allows to query image sizes through a callback function.
	 * Names of image sizes whose callback return true are returned in an array.
	 * @param $hasAttributeCallback the callback function to be called.
	 * @return array names of image sizes for which the callback returned true
	 */
	/*function _getImageSizeNamesWithAttribute($hasAttributeCallback = 'hasChanged'){
		$with = array();
		foreach($this->getChildrenWithAttribute($hasAttributeCallback) as $current)
			$with[] = $current->getName();
			
		return $with;
		
	}*/

	
}

class ImageSizeOption extends CompositeOption
{
	
	/**
	 * Default width of Image size.
	 *
	 * @access private
	 * @var integer
	 */
	var $_defaultWidth;
	
	/**
	 * Default height of Image size.
	 *
	 * @access private
	 * @var integer
	 */
	var $_defaultHeight;
	


	/**
	 * PHP5 type constructor
	 */
	function __construct($name, $defaultValue = '1', $defaultWidth = '700', $defaultHeight = '525')
	{
		parent::__construct($name, $defaultValue);
		
		$this->_defaultWidth = $defaultWidth;
		$this->_defaultHeight = $defaultHeight;
		
		$this->_buildRadioButtonList();
		
		
		$this->addChild(
			new TextFieldOption(
				$this->_name . '-imgQuality',
				'95',
				'',
				'<tr valign="top"><th scope="row">'.__('Image Quality','PhotoQ').': </th><td>',
				'%</td></tr>',
				'2'
			)
		);
		
		$this->addChild(
			new CheckBoxOption(
				$this->_name . '-watermark',
				'0',
				__('Add watermark to all images of this size.','PhotoQ'),
				'<tr valign="top"><th scope="row">'.__('Watermark','PhotoQ').':</th><td>',
				'</td></tr>'
			)
		);
		
	}
	
	
	
	function _buildRadioButtonList()
	{
		$imgConstr = new RadioButtonList(
				$this->_name . '-imgConstraint',
				'rect'
		);

		$maxDimImg = new RadioButtonOption(
				'rect',
				__('Maximum Dimensions','PhotoQ').': ',
				'<tr valign="top"><th scope="row">',
				'</th>'
		);
		$maxDimImg->addChild(
			new TextFieldOption(
				$this->_name . '-imgWidth',
				$this->_defaultWidth,
				'',
				'<td>',
				__('px wide','PhotoQ').', ',
				'4',
				'5'
			)
		);
		$maxDimImg->addChild(
			new TextFieldOption(
				$this->_name . '-imgHeight',
				$this->_defaultHeight,
				'',
				'',
				__('px high','PhotoQ').' ',
				'4',
				'5'
			)
		);
		$maxDimImg->addChild(
			new CheckBoxOption(
				$this->_name . '-zoomCrop',
				0,
				__('Crop to max. dimension','PhotoQ').'.&nbsp;)',
				'&nbsp;(&nbsp;',
				'</td></tr>'
			)
		);
		$imgConstr->addChild($maxDimImg);


		$smallestSideImg = new RadioButtonOption(
				'side',
				__('Smallest side','PhotoQ').': ',
				'<tr valign="top"><th scope="row">',
				'</th>'
		);
		$smallestSideImg->addChild(
			new TextFieldOption(
				$this->_name . '-imgSide',
				'525',
				'',
				'<td>',
				'px</td></tr>',
				'4',
				'5'
			)
		);
		$imgConstr->addChild($smallestSideImg);

		$fixedWidthImg = new RadioButtonOption(
				'fixed',
				__('Landscape Width','PhotoQ').': ',
				'<tr valign="top"><th scope="row">',
				'</th>'
		);
		$fixedWidthImg->addChild(
			new TextFieldOption(
				$this->_name . '-imgFixed',
				'525',
				'',
				'<td>',
				'px</td></tr>',
				'4',
				'5'
			)
		);
		$imgConstr->addChild($fixedWidthImg);

		$imgConstr->addChild(
			new RadioButtonOption(
				'noResize',
				__('Original Size','PhotoQ').': ',
				'<tr valign="top"><th scope="row">',
				'</th><td>'.__('Keep original image size, don\'t resize','PhotoQ').'.</td></tr>'
			)
		);
		
		
		
		$this->addChild($imgConstr);
	}
	
	
	
	
	/**
	 * Tests whether the ImageSize in question is removable of not.
	 *
	 * @return boolean
	 */
	/*function isRemovable()
	{
		return $this->getValue();
	}*/
	
	/**
	 * Returns boolean indicating whether this image size sports a watermark.
	 * @return boolean true if image size has watermark, false otherwise
	 */
	function hasWatermark(){
		$option = &$this->getOptionByName($this->_name.'-watermark');
		return $option->getValue(); 
	}
 	
	
	

}


class PhotoQViewOption extends CompositeOption
{
	
	var $_mainID;
	var $_thumbID;

	/**
	 * PHP5 type constructor
	 */
	function __construct($name, $withNoneField = false)
	{
		parent::__construct($name);
		
		$this->_mainID = PhotoQOptionController::getMainIdentifier();
		$this->_thumbID = PhotoQOptionController::getThumbIdentifier();
		
		//get the db object
		$this->_db =& PhotoQSingleton::getInstance('PhotoQDB');
		
		
		$this->_buildRadioButtonList($withNoneField);
	}
		
	
	function _buildRadioButtonList($withNoneField = false)
	{
		$viewType =& new RadioButtonList(
				$this->_name . 'View-type',
				'single'
		);

		//gives the option to disable managing of this view with photoq
		if($withNoneField){
			$viewType->addChild(
				new RadioButtonOption(
					'none',
					__('Empty, don\'t manage.','PhotoQ'),
					'<tr valign="top"><th scope="row">',
					'</th><td></td></tr>'
				)
			);
		}
		
		$singleImg =& new RadioButtonOption(
				'single',
				__('Single Photo','PhotoQ').': ',
				'<tr valign="top"><th scope="row">',
				'</th>'
		);
		$singleSize =& new DropDownList(
				$this->_name . 'View-singleSize',
				$this->_mainID,
				'',
				'<td>',
				'</td></tr>'
		);
		$singleImg->addChild($singleSize);
		$viewType->addChild($singleImg);
		
		
		$imgLink =& new RadioButtonOption(
				'imgLink',
				__('Image Link','PhotoQ').': ',
				'<tr valign="top"><th scope="row">',
				'</th>'
		);
		$imgLinkSize =& new DropDownList(
				$this->_name . 'View-imgLinkSize',
				$this->_thumbID,
				'',
				'<td>',
				__(' linking to ','PhotoQ')
		);		
		$imgLink->addChild($imgLinkSize);
		$imgLinkTargetSize =& new DropDownList(
				$this->_name . 'View-imgLinkTargetSize',
				$this->_mainID,
				'',
				'',
				''
		);
		$imgLink->addChild($imgLinkTargetSize);
		
		$imgLink->addChild(
			new TextFieldOption(
				$this->_name . 'View-imgLinkAttributes',
				attribute_escape('rel="lightbox"'),
				', '.__('link having following attributes','PhotoQ').': ',
				'',
				'<br />
				<span class="setting-description">'.__('Allows interaction with JS libraries such as Lightbox and Shutter Reloaded without modifying templates.','PhotoQ').'</span></td></tr>',
				'40'
			)
		);
		
		$viewType->addChild($imgLink);
		
		$freeform =& new RadioButtonOption(
			'freeform',__('Freeform', 'PhotoQ').': ',
			'<tr valign="top"><th scope="row">',
			'</th>'
		);
		$freeform->addChild(new TextAreaOption(
			$this->_name .'View-freeform',
			'',
			'',
			'<td>',
			'<br/><span class="setting-description">'.sprintf(__('HTML as well as the following shorttags are allowed: %s, where %s has to be replaced with the name of the existing image size (e.g. %s or %s) that you want to use.','PhotoQ'), $this->_createFreeformShorttagList(), '"sizeName"', '"main"', '"original"').'</span></td></tr>',
			7, 100	
		));
		
		$viewType->addChild($freeform);
		
		$this->addChild($viewType);
		
	}
	
	/**
	 * Helper to create comma separated list of shorttags allowed in freeform views.
	 * @return string	the list of shorttags that are allowed
	 */
	function _createFreeformShorttagList(){
		$allowedShorttags = array('title', 'descr', 'exif');
		//add the meta fields and the sizes options 
		$allowedShorttags = array_merge(
			$allowedShorttags, 
			$this->_db->getFieldNames(), 
			array('imgUrl|sizeName', 'imgPath|sizeName', 'imgWidth|sizeName', 'imgHeight|sizeName')
		);
		
		for($i = 0; $i<count($allowedShorttags); $i++)
			$allowedShorttags[$i] = '<code>['.$allowedShorttags[$i].']</code>';
		return implode(', ', $allowedShorttags);
	}
	
	/**
	 * Populate the lists of image sizes with the names of registered image sizes as key, value pair.
	 *
	 * @param array $imgSizeNames
	 * @access public
	 */
	function populate($imgSizeNames, $addOriginal = true)
	{
		//add the original as an option
		if($addOriginal)
			array_push($imgSizeNames,'original');
		
		$singleSize =& $this->getOptionByName($this->_name .'View-singleSize');
		$singleSize->populate(PhotoQHelper::arrayCombine($imgSizeNames,$imgSizeNames));
		
		$imgLinkSize =& $this->getOptionByName($this->_name .'View-imgLinkSize');
		$imgLinkSize->populate(PhotoQHelper::arrayCombine($imgSizeNames,$imgSizeNames));
		
		$imgLinkTargetSize =& $this->getOptionByName($this->_name .'View-imgLinkTargetSize');
		$imgLinkTargetSize->populate(PhotoQHelper::arrayCombine($imgSizeNames,$imgSizeNames));
		
	}
	
	/**
	 * Remove names of registered image sizes as key, value pair.
	 *
	 * @access public
	 */
	function unpopulate()
	{
		$singleSize =& $this->getOptionByName($this->_name .'View-singleSize');
		$singleSize->removeChildren();
		$imgLinkSize =& $this->getOptionByName($this->_name .'View-imgLinkSize');
		$imgLinkSize->removeChildren();
		$imgLinkTargetSize =& $this->getOptionByName($this->_name .'View-imgLinkTargetSize');
		$imgLinkTargetSize->removeChildren();
		
	}
	
	
	
	

}


/**
 * The PhotoQImageMagickPathCheckInputTest:: checks whether 
 * imagemagick path really leads to imagemagick.
 *
 * @author  M.Flury
 * @package PhotoQ
 */
class PhotoQImageMagickPathCheckInputTest extends InputTest
{
	
	/**
	 * Concrete implementation of the validate() method. This methods determines 
	 * whether input validation passes or not.
	 * @param object ReusableOption &$target 	The option to validate.
	 * @return String 	The error message created by this test.
	 * @access public
	 */
	function validate(&$target)
	{	
		require_once(PHOTOQ_PATH.'lib/phpThumb_1.7.9/phpthumb.class.php');
		// create phpThumb object
		$phpThumb = new phpThumb();
		$phpThumb->config_imagemagick_path = ( $target->getValue() ? $target->getValue() : null );
		//under windows the version check doesn't seem to work so we also check for availability of resize
		if ( !$phpThumb->ImageMagickVersion() && !$phpThumb->ImageMagickSwitchAvailable('resize') ) {
    		$errMsg = __("Note: ImageMagick does not seem to be installed at the location you specified. ImageMagick is optional but might be needed to process bigger photos, plus PhotoQ might run faster if you configure ImageMagick correctly. If you don't care about ImageMagick and are happy with using the GD library you can safely ignore this message.",'PhotoQ');
    		$this->raiseErrorMessage($errMsg);
			return false;
		}
		return true;
	}
	
	
}


class PhotoQExifTagOption extends CompositeOption
{
	var $_exifExampleValue;
		
	function __construct($exifKey, $exifExampleValue)
	{
		parent::__construct($exifKey);
		$this->_exifExampleValue = $exifExampleValue;
			
		$this->addChild(
			new TextFieldOption(
				$exifKey.'-displayName',
				'',
				__('Display Name','PhotoQ').': ',
				'',
				'<br/>',
				'20')
		);
		
		//whether to use it for tagFromExif
		$this->addChild(
			new CheckBoxOption(
				$exifKey.'-tag',
				'0', 
				__('Create post tags from EXIF data','PhotoQ').'', 
				'', 
				''
			)
		);
		
	}
	
	function getExifKey(){
		return $this->getName();
	}
	
	function getExifExampleValue(){
		return $this->_exifExampleValue;
	}

}


class PhotoQRoleOption extends RO_CapabilityCheckBoxList
{

	function __construct($name, $role = 'administrator', $defaultValue = '', $label = '',
				$textBefore = '', $textAfter = '')
	{
		parent::__construct($name, $role, $defaultValue, $label, $textBefore, $textAfter);
		
		$this->addChild(
			new RO_CheckBoxListOption(
				'use_primary_photoq_post_button',
				__('Allowed to use primary post button','PhotoQ'),
				'<li>',
				'</li>'
			)
		);
		$this->addChild(
			new RO_CheckBoxListOption(
				'use_secondary_photoq_post_button',
				__('Allowed to use secondary post button','PhotoQ'),
				'<li>',
				'</li>'
			)
		);
		$this->addChild(
			new RO_CheckBoxListOption(
				'reorder_photoq',
				__('Allowed to reorder queue','PhotoQ'),
				'<li>',
				'</li>'
			)
		);
		
	}

		
}		


/**
 * This our own (stupid) little parser for options that were saved in XML format.
 * Have to make my own as we don't want to rely on any PHP extensions that may not
 * be present on some server plus the whole XML stuff changed btw PHP4 and PHP5.
 * @author manu
 *
 */
class PhotoQXMLParser extends PhotoQObject{

	/**
	 * The file to parse from
	 * @var String
	 */
	var $_xmlFile;
	
	/**
	 * The meta fields found in the xml file
	 * @var array
	 */
	var $_parsedFields = array();
	
	/**
	 * The default categories found in the xml file
	 * @var array
	 */
	var $_parsedCats = array();
	
	/**
	 * Array of options found in the xml file
	 * @var array
	 */
	var $_parsedOptions = array();
	
	/**
	 * Reference to ErrorStack singleton
	 * @var object PEAR_ErrorStack
	 */
	var $_errStack;
	
	
	function __construct($xmlFile){
		
		$this->_xmlFile = $xmlFile;
		
		//get the PhotoQ error stack for easy access
		$this->_errStack = &PEAR_ErrorStack::singleton('PhotoQ');
		$this->_db =& PhotoQSingleton::getInstance('PhotoQDB');
	}

	/**
	 * This one is based on the WordPress import file wordpress.php
	 * @return unknown_type
	 */
	function parse(){
		//let's open the file for parsing
		$fp = fopen($this->_xmlFile, 'r');
		if ($fp) {
			
			//Two stacks, one for the options, one for their name. Needed while we 
			//traverse the option tree.
			$optionStack = array();
			$optionNameStack = array();
			
			//The current option and its name that is being processed
			$currentArray = array();
			$currentName = 'allOptions';
			
			//flags in case we need some state information about what is being parsed
			$doing_fields = false; //are we currently processing meta fields?
			$doing_cats = false; //...or default categories?
			$doing_options = false; //...or rather PhotoQ options?
			$doing_value = false; //...or is it maybe a multiline value?
			
			//keys and values of value array that were already processed
			$currentKey = '';
			$currentVal = '';
			//the currentValue, needed in case we have multiline values
			$currentValue = '';
			
			//parse the file line-by-line
			while ( !feof($fp) ) {
				$importline = rtrim(fgets($fp));
				
				if ( false !== strpos($importline, '<photoQFields>') ) {
					$doing_fields = true;
					continue;
				}
				if ( false !== strpos($importline, '</photoQFields>') ) {
					$doing_fields = false;
					continue;
				}
				if ( $doing_fields  ) {
					preg_match('#<field><name>(.*?)</name></field>#', $importline, $fieldname);
					$this->_parsedFields[] = attribute_escape($fieldname[1]);
				}
				
				if ( false !== strpos($importline, '<photoQDefaultCategories>') ) {
					$doing_cats = true;
					continue;
				}
				if ( false !== strpos($importline, '</photoQDefaultCategories>') ) {
					$doing_cats = false;
					continue;
				}
				if ( $doing_cats  ) {
					preg_match('#<category><name>(.*?)</name></category>#', $importline, $catname);
					$this->_parsedCats[] = attribute_escape($catname[1]);
				}

				if ( false !== strpos($importline, '<photoQOptions>') ) {
					$doing_options = true;
					continue;
				}
				if ( false !== strpos($importline, '</photoQOptions>') ) {
					$doing_options = false;
					continue;
				}
				if ($doing_options){
					if ( false !== strpos($importline, '<option ') ) {
						preg_match('#<option name="(.*?)".*>#', $importline, $optname);
						array_push($optionNameStack, $currentName);
						array_push($optionStack, $currentArray);
						$currentName = $optname[1];
						$currentArray = array();
						continue;
					}
					if ( false !== strpos($importline, '<arrayValue>') ) {
						array_push($optionNameStack, $currentName);
						array_push($optionStack, $currentArray);
						//$currentName = $optname[1];
						$currentArray = array();
						continue;
					}
					if ( false !== strpos($importline, '<entry>') ) {
						$currentKey = '';
						$currentVal = '';
						continue;
					}
					if ( false !== strpos($importline, '</entry>') ) {
						$currentArray[$currentKey] = $currentVal;
						continue;
					}
					if ( false !== strpos($importline, '<key>') ) {
						preg_match('#<key>(.*?)</key>#', $importline, $optval);
						$currentKey = $optval[1];
						continue;
					}
					if ( false !== strpos($importline, '<val>') ) {
						preg_match('#<val>(.*?)</val>#', $importline, $optval);
						$currentVal = $optval[1];
						continue;
					}
					if ( false !== strpos($importline, '<value>') && false !== strpos($importline, '</value>') ) {
						preg_match('#<value>(.*?)</value>#', $importline, $optval);
						//array_push($currentArray, $optval[1]);
						$currentArray[$currentName] = $this->_unhtmlentities(str_replace(array ('<![CDATA[', ']]>'), '', $optval[1]));
						continue;
					}
					if ( false !== strpos($importline, '<value>') ) {
						$doing_value = true;
						preg_match('#<value>(.*)#', $importline, $optval);
						//array_push($currentArray, $optval[1]);
						$currentValue = $this->_unhtmlentities(str_replace(array ('<![CDATA[', ']]>'), '', $optval[1]));
						continue;
					}
					if($doing_value){//multi-line value
						if ( false !== strpos($importline, '</value>') ) {
							$doing_value = false;
							preg_match('#(.*?)</value>#', $importline, $optval);
							$currentValue .= $this->_unhtmlentities(str_replace(array ('<![CDATA[', ']]>'), '', $optval[1]));
							$currentArray[$currentName] = $currentValue;
							continue;
						}else{
							$currentValue .= PHP_EOL . $this->_unhtmlentities(str_replace(array ('<![CDATA[', ']]>'), '', $importline)) . PHP_EOL;
							continue;
						}
					}
					if ( false !== strpos($importline, '</option>') ) {
						$oldName = $currentName;
						$oldArray = $currentArray;
						$currentName = array_pop($optionNameStack);
						$currentArray = array_pop($optionStack);
						$currentArray[$oldName] = $oldArray;
						continue;
					}
					if ( false !== strpos($importline, '</arrayValue>') ) {
						$oldArray = $currentArray;
						$currentName = array_pop($optionNameStack);
						$currentArray = array_pop($optionStack);
						$currentArray[$currentName] = $oldArray;
						continue;
					}
				}


			}

			fclose($fp);
		}
		
		//$currentArray now holds the whole option array
		$this->_parsedOptions = $currentArray;
	}
	
	/**
	 * Performs some sanitiy checks on the data parsed from the XML file.
	 * @return unknown_type
	 */
	function validate(){
		//check that only allowed options are being imported
		$allowed = array('imageSizes', 'views', 'exifDisplay', 'qPostDefaultCat', 'qPostDefaultTags');
		foreach(array_keys($this->_parsedOptions) as $optionName)
			if(!in_array($optionName,$allowed)){
				$this->_errStack->push(PHOTOQ_XML_DENIED_OPTION,'error', array('optionname' => $optionName));
				return false;
			}
				
		
		//check that views and image sizes contain the standard fixed elements.
		if(!$this->_expCompContainsRequiredFixedElements('views', array('content', 'excerpt'))){
			return false;
		}if(!$this->_expCompContainsRequiredFixedElements('imageSizes', array('main', 'thumbnail'))){
			return false;
		}
		//do not add a view if a meta field with same name exists already or is also requested
		$fieldNames = array_unique(array_merge($this->_parsedFields, $this->_db->getFieldNames()));
		$conflictingNames = array_intersect($fieldNames, $this->getViewNames());
		if(!empty($conflictingNames)){
			foreach($conflictingNames  as $conflictingName){
				$this->_errStack->push(PHOTOQ_FIELD_EXISTS,'error', array('fieldname' => $conflictingName));
			}
			return false;
		}
		return true;
		
	}

	function _unhtmlentities($string) { // From php.net for < 4.3 compat
		$trans_tbl = get_html_translation_table(HTML_ENTITIES);
		$trans_tbl = array_flip($trans_tbl);
		return strtr($string, $trans_tbl);
	}
	
	function _expCompContainsRequiredFixedElements($compositeName, $requiredElements){
		if(isset($this->_parsedOptions[$compositeName][$compositeName])){
			if(is_array($this->_parsedOptions[$compositeName][$compositeName])){
				//it has to contain every required element plus they cannot be removeable
				foreach ($requiredElements as $element){
					if(!$this->_containsFixedElement($compositeName,$element))
						return false;
				}
			}else
				return false;
		}
		return true;
	}
	
	function _containsFixedElement($compositeName,$element){
		$result = true;
		if(isset($this->_parsedOptions[$compositeName][$compositeName][$element])){
			if($this->_parsedOptions[$compositeName][$compositeName][$element])
				$result = false;
		}else{
			$this->_errStack->push(PHOTOQ_XML_IMPORT_MISSING_ELEMENT,'error', array('compName' => $compositeName, 'element' => $element));
			$result = false;
		}
		return $result;
	}
	
	function getParsedOptions(){
		return $this->_parsedOptions;
	}
	
	function getParsedFields(){
		return $this->_parsedFields;
	}
	
	function getParsedCats(){
		return $this->_parsedCats;
	}
	
	function getViewNames($onlyRemoveable = false){
		return $this->_getElementNames('views', $onlyRemoveable);
	}
	
	function getImageSizeNames($onlyRemoveable = false){
		return $this->_getElementNames('imageSizes', $onlyRemoveable);
	}
	
	function _getElementNames($expComp, $onlyRemoveable = false){
		$result = array();
		if(isset($this->_parsedOptions[$expComp][$expComp])){
			if(is_array($this->_parsedOptions[$expComp][$expComp])){
				foreach ($this->_parsedOptions[$expComp][$expComp] as $elem => $removeable){
					if(!$onlyRemoveable || $removeable){
						$result[] = $elem;
					}
				}
			}
		}
		return $result;
	}
				
	

}


?>
