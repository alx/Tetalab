<?php
class PhotoQDB extends PhotoQObject
{
	/**
	 * The wordpress database object to interface with wordpress database
	 * @var Object
	 * @access private
	 */
	var $_wpdb;
	
	/**
	 * Reference to ErrorStack singleton
	 * @var object PEAR_ErrorStack
	 */
	var $_errStack;

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
	 * Name of photoq database table that is used to keep batch processing data persistent.
	 * @var string
	 * @access public
	 */
	var $QBATCH_TABLE;
	
		/**
	 * Name of wordpress posts database table
	 * @var string
	 * @access public
	 */
	var $POSTS_TABLE;

	/**
	 * Name of wordpress database table relating posts to custom fields
	 * @var string
	 * @access public
	 */
	var $POSTMETA_TABLE;
	
	
	/**
	 * PHP5 type constructor
	 *
	 * @access public
	 */
	function __construct()
	{
		global $wpdb;
		
		//get the PhotoQ error stack for easy access
		$this->_errStack = &PEAR_ErrorStack::singleton('PhotoQ');
		
		
		// set wordpress database
		$this->_wpdb =& $wpdb;
		
		// some methods need access to options so instantiate an OptionController
		//don't do this here, otherwise we cannot use the db object inside the oc constructor
		//$this->_oc =& PhotoQSingleton::getInstance('PhotoQOptionController');
		
		// set names of database tables used and created by photoq
		$this->QUEUEMETA_TABLE = $wpdb->prefix."photoqmeta";
		$this->QUEUE_TABLE = $wpdb->prefix."photoq";
		$this->QFIELDS_TABLE = $wpdb->prefix."photoqfields";
		$this->QCAT_TABLE = $wpdb->prefix."photoq2cat";
		$this->QBATCH_TABLE = $wpdb->prefix."photoqbatch";
		
		// set names of wordpress database tables used by photoq
		$this->POSTS_TABLE = $wpdb->prefix."posts";
		$this->POSTMETA_TABLE = $wpdb->prefix."postmeta";
	}
	
	
	/**
	 * Inserts a new custom field into the database.
	 * 
	 * @param string $name		The name of the field to be created.
	 * @access public
	 */
	function insertField($name, $add2published = true)
	{
		//only add if field doesn't exist yet
		if(in_array($name, $this->getFieldNames()))
			$this->_errStack->push(PHOTOQ_FIELD_EXISTS,'error', array('fieldname' => $name));
		else{
			// instantiate an OptionController
			$oc =& PhotoQSingleton::getInstance('PhotoQOptionController');

			//do not add if a view with same name exists
			$viewNames = $oc->getViewNames();
			if(in_array($name, $viewNames)){
				$this->_errStack->push(PHOTOQ_VIEW_EXISTS,'error', array('viewname' => $name));
					
			}else{ // now we can add the field
					
				//remove whitespace as this will also be used as mysql column header
				$name = preg_replace('/\s+/', '_', $name);
				$this->_wpdb->query("INSERT INTO $this->QFIELDS_TABLE (q_field_name) VALUES ('$name')");

				//get the id assigned to this entry
				$fieldID = mysql_insert_id();

				//add also to metatable for all entries in the queue
				$results = $this->_wpdb->get_results("
					SELECT
					q_img_id
					FROM
					$this->QUEUE_TABLE
					WHERE
					1
				");

			if($results){

				foreach ($results as $queueEntry) {

					$insertQuery = "
						INSERT INTO $this->QUEUEMETA_TABLE 
						(q_fk_img_id, q_fk_field_id, q_field_value)
						VALUES ('$queueEntry->q_img_id', $fieldID, '')
					";
					$this->_wpdb->query($insertQuery);
				}

			}

			if($add2published){
				$this->addFieldToPublishedPosts($name);
			}
				
			$this->_errStack->push(PHOTOQ_INFO_MSG,'info', array(), sprintf(__('The field with name "%s" was successfully added.', 'PhotoQ'), $name));
			}
		}
	}

	/**
	 * Adds a custom field with name "$name" to all published photoq posts.
	 * Only adds to a post if a field with the same name does not yet exist for this post.
	 * @param $name	String	the name of the field to be added
	 * @return unknown_type
	 */
	function addFieldToPublishedPosts($name){
		//select all photoq posts that do not have the new field yet
		$results = $this->_wpdb->get_results(
			"SELECT ID FROM $this->POSTS_TABLE, $this->POSTMETA_TABLE WHERE 
				ID = post_id && meta_key = 'photoQPath' && ID NOT IN 
					(SELECT post_id FROM $this->POSTMETA_TABLE WHERE `meta_key` = '$name')
			");
		//add the field to each of these posts
		if($results){
			foreach ($results as $postEntry) {
				$insertQuery = "
						INSERT INTO $this->POSTMETA_TABLE 
						(post_id, meta_key, meta_value)
						VALUES ($postEntry->ID, '$name', ''
					)";
				$this->_wpdb->query($insertQuery);
			}
		}
	}
	
	/**
	 * Delete a custom field with name "$name" from all published photoq posts.
	 * @param $name	String	the name of the field to be deleted
	 * @return unknown_type
	 */
	function deleteFieldFromPublishedPosts($name){
		// this is a bit messy but we have to use the workaround from here 
		// http://www.xaprb.com/blog/2006/06/23/how-to-select-from-an-update-target-in-mysql/
		$deleteQuery = "DELETE FROM $this->POSTMETA_TABLE WHERE 
			meta_key = '$name' && post_id IN 
				(SELECT ID FROM 
					(SELECT ID, post_id, meta_key FROM $this->POSTS_TABLE, $this->POSTMETA_TABLE) AS photoQposts
				WHERE ID = post_id && meta_key = 'photoQPath')"; 
		$this->_wpdb->query($deleteQuery);
	}



	/**
	 * Remove a custom field from the database.
	 * 
	 * @param int $id		The id of the field to be removed.
	 * @access public
	 */	
	function removeField($id)
	{	
		// instantiate an OptionController
		$oc =& PhotoQSingleton::getInstance('PhotoQOptionController');
		
		//get the name before deleting
		$name = $this->_wpdb->get_var("SELECT q_field_name FROM $this->QFIELDS_TABLE WHERE q_field_id = $id");
	
		//delete DB entry
		$this->_wpdb->query("DELETE FROM $this->QFIELDS_TABLE WHERE q_field_id = '$id'");
	
		//delete also from metatable
		$this->_wpdb->query("DELETE FROM $this->QUEUEMETA_TABLE WHERE q_fk_field_id = '$id'");
	
		if($oc->getValue('fieldDeletePosted')){
			//delete from already posted posts
			$this->deleteFieldFromPublishedPosts($name);
			//$this->_wpdb->query("DELETE FROM $this->POSTMETA_TABLE WHERE meta_key = '$name'");
		}
		
		$this->_errStack->push(PHOTOQ_INFO_MSG,'info', array(), sprintf(__('The field with name "%s" was successfully deleted.', 'PhotoQ'), $name));
	
	} 
	
	/**
	 * Rename an exising custom field.
	 * 
	 * @param int $id				The id of the field to be renamed.
	 * @param string $newName		The new name of the field to be renamed.
	 * @access public
	 */
	function renameField($id, $newName)
	{
		// TODO: prohibit two fields with same name

		// instantiate an OptionController
		$oc =& PhotoQSingleton::getInstance('PhotoQOptionController');
		
		//get the old name
		$oldName = $this->_wpdb->get_var("SELECT q_field_name FROM $this->QFIELDS_TABLE WHERE q_field_id = $id");
	
		//remove whitespace as this will also be used as mysql column header
		$newName = preg_replace('/\s+/', '_', $newName);
	
		//update DB entry
		$this->_wpdb->query("UPDATE $this->QFIELDS_TABLE SET q_field_name = '$newName' WHERE q_field_id = '$id'");
	
		if($oc->getValue('fieldRenamePosted')){
			//update already posted posts
			$this->_wpdb->query("UPDATE $this->POSTMETA_TABLE SET meta_key = '$newName' WHERE meta_key = '$oldName'");
		}
	
	}
	
	function getAllFields()
	{
		return $this->_wpdb->get_results("
			SELECT * FROM $this->QFIELDS_TABLE
			WHERE 1 ORDER BY q_field_name
		");
	}
	
	function getFieldNames()
	{
		$fields = $this->getAllFields();
		$result = array();
		foreach ($fields as $field) {
			$result[] = $field->q_field_name;
		}
		return $result;
	}
	
	/**
	 * As so many other people, we hate the new revision feature of wordpress ;-)
	 * We don't store any revisions of photoQ posts. This function removes all
	 * revisions of post with id $postID.
	 *
	 * @param unknown_type $postID
	 * @return unknown
	 */
	function removeRevisions($postID)
	{
		return $this->_wpdb->get_results("
			DELETE FROM $this->POSTS_TABLE
			WHERE post_type = 'revision' AND post_parent = $postID
		");
		
	}
	
	function getAllPublishedPhotos()
	{
		$photos = array();
		$results = $this->_wpdb->get_results("
			SELECT ID, post_title, meta_value FROM $this->POSTS_TABLE, $this->POSTMETA_TABLE 
			WHERE $this->POSTS_TABLE.ID = $this->POSTMETA_TABLE.post_id AND $this->POSTMETA_TABLE.meta_key = 'photoQPath'");
		foreach ($results as $result)
			$photos[] =& new PhotoQPublishedPhoto($result->ID, $result->post_title, '', '', $result->meta_value);
		
		return $photos;
	}
	
	function getAllPublishedPhotoIDs()
	{
		return $this->_wpdb->get_col("
			SELECT ID FROM $this->POSTS_TABLE, $this->POSTMETA_TABLE 
			WHERE $this->POSTS_TABLE.ID = $this->POSTMETA_TABLE.post_id AND $this->POSTMETA_TABLE.meta_key = 'photoQPath'");
		
	}
	
	/**
	 * 
	 * @param $postID
	 * @return object PhotoQPublishedPhoto
	 */
	function &getPublishedPhoto($postID)
	{
		$result = $this->_wpdb->get_row("
			SELECT post_title, meta_value FROM $this->POSTS_TABLE, $this->POSTMETA_TABLE 
			WHERE $this->POSTS_TABLE.ID = '$postID' AND $this->POSTS_TABLE.ID = $this->POSTMETA_TABLE.post_id AND $this->POSTMETA_TABLE.meta_key = 'photoQPath'");
		if(is_null($result)){
			$this->_errStack->push(PHOTOQ_POST_NOT_FOUND,'error', array('id' => $postID));
			return NULL;
		}
		
		return new PhotoQPublishedPhoto($postID, $result->post_title, '', '', $result->meta_value, '', PhotoQHelper::getArrayOfTagNames($postID));
		
	}
	
	/**
	 * Used to import photos from photoq < 2.5
	 *
	 * @return array of photos to import
	 */
	function getAllPhotos2Import()
	{
		$photos = array();
		$results = $this->_wpdb->get_results("
			SELECT ID, post_title, meta_value FROM $this->POSTS_TABLE, $this->POSTMETA_TABLE 
			WHERE $this->POSTS_TABLE.ID = $this->POSTMETA_TABLE.post_id AND $this->POSTMETA_TABLE.meta_key = 'path'");
		foreach ($results as $result)
			$photos[] =& new PhotoQImportedPhoto($result->ID, $result->post_title, '', $result->meta_value);
		
		return $photos;
	}
	
	function setQueuePosition($id, $position){
		$this->_wpdb->query("UPDATE  $this->QUEUE_TABLE SET q_position = '$position' WHERE q_img_id = '$id'");	
	}
		
	function getQueueByPosition()
	{
		return $this->_wpdb->get_results("
		SELECT
		*
		FROM
		$this->QUEUE_TABLE
		WHERE
		1
		ORDER BY q_position
		");
	}
	
	function getQueueIDTagPairs()
	{
		return $this->_wpdb->get_results("
		SELECT
		q_img_id, q_tags
		FROM
		$this->QUEUE_TABLE
		WHERE
		1
		");
	}
	
	function getAllQueuedPhotoIDs(){
		return $this->_wpdb->get_col("SELECT q_img_id FROM $this->QUEUE_TABLE WHERE 1");
	}
	
	/**
	 * Sorts the queue according to the given criterion.
	 * @param $criterion
	 * @return unknown_type
	 */
	function sortQueue($criterion){
		//get the sorted ids of the images in the queue.
		$sortedIds = $this->_wpdb->get_col("SELECT q_img_id
			FROM $this->QUEUE_TABLE ORDER BY " . $this->_getSortOrderByClause($criterion));
		
		//randomize?
		if($criterion === 'random') shuffle($sortedIds);
		
		//sort the database accordingly
		$this->_multiRowSort($sortedIds);
	}
	
	/**
	 * Returns the ORDER BY clause that is used when ordering the queue positions.
	 * @param $criterion
	 * @return unknown_type
	 */
	function _getSortOrderByClause($criterion = 'id'){
		$order = "q_img_id";
		switch($criterion){
			case "date_desc":
				$order = "q_date DESC, q_imgname, q_img_id";
				break;
			case "date_asc":
				$order = "q_date ASC, q_imgname, q_img_id";
				break;
			case "title_asc":
				$order = "q_title ASC, q_date, q_imgname, q_img_id";
				break;
			case "title_desc":
				$order = "q_title DESC, q_date, q_imgname, q_img_id";
				break;
			case "filename_asc":
				$order = "q_imgname ASC, q_date, q_imgname, q_img_id";
				break;
			case "filename_desc":
				$order = "q_imgname DESC, q_date, q_imgname, q_img_id";
				break;	
		}
		return $order;
	}
	
	/**
	 * Updates DB such that positions correspond to the ordering of the IDs of the array given.
	 * Done in a server friendly way, in only one single query.
	 * @param $sortedIds
	 * @return unknown_type
	 */
	function _multiRowSort($sortedIds){
		$multiRowSortQuery = "UPDATE $this->QUEUE_TABLE SET q_position = CASE q_img_id ";
		foreach($sortedIds as $pos => $id){
			$multiRowSortQuery .= "WHEN '$id' THEN '$pos' ";
		}
		$multiRowSortQuery .= "ELSE q_position END";
		$this->_wpdb->query($multiRowSortQuery);
	}
	
	function updateTags($imgId, $tags){
		$this->_wpdb->query("UPDATE $this->QUEUE_TABLE SET q_tags = '$tags' WHERE q_img_id = '$imgId'");
	}
	
	
	// TODO maybe make this a method of a photo object
	function getFieldValue($imgID, $fieldID)
	{
		return $this->_wpdb->get_var("SELECT
				q_field_value
				FROM
				$this->QUEUEMETA_TABLE
				WHERE
				q_fk_img_id = $imgID && q_fk_field_id = $fieldID
		");
	}
	
	// TODO: same here, could be part of a photo object
	function getCategoriesByImgId($id)
	{
		return $this->_wpdb->get_col("SELECT category_id
		FROM $this->QCAT_TABLE
		WHERE q_fk_img_id = $id");
	}

	function insertCategory($id, $catId)
	{
		$this->_wpdb->query("
			INSERT INTO $this->QCAT_TABLE
			(q_fk_img_id, category_id)
			VALUES
			($id, $catId)
		");
	}
	
	
	
	/**
	 * Insert a new batch into the database and return its id.
	 * @return unknown_type
	 */
	function insertBatch(){
		//$_SERVER['REQUEST_TIME'] not available in PHP4
		$timestamp = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
		
		if(!$this->_wpdb->query('INSERT INTO '.$this->QBATCH_TABLE.' (timestamp) VALUES ('.$timestamp.')'))
			return FALSE;
		
		//get the id assigned to this newly created entry
		return mysql_insert_id();
	}
	
	/**
	 * Update batch with given id in the database
	 * @param $id
	 * @param $batchSets
	 * @return unknown_type
	 */
	function updateBatch($id, $batchSets){
		//PhotoQHelper::debug('updateBatch: before ' . print_r(($batchSets),true));
		//PhotoQHelper::debug('updateBatch: serialized ' . print_r(serialize($batchSets),true));
		//PhotoQHelper::debug('updateBatch: unserialized ' . print_r(unserialize(serialize($batchSets)),true));
		$this->_wpdb->query("UPDATE $this->QBATCH_TABLE SET batch='".serialize($batchSets)."' WHERE bid = '$id'");
	}
	
	/**
	 * Remove batch with specified id from the database
	 * @param $id int	id to remove
	 * @return unknown_type
	 */
	function deleteBatch($id){
		//also remove those that are older than 1 day
		$this->_wpdb->query("DELETE FROM $this->QBATCH_TABLE WHERE bid = '$id' OR timestamp < " . (time() - 86400) );
	}
	
	/**
	 * Returns the batch sets associated with batch of given id.
	 * @param $id
	 * @return unknown_type
	 */
	function getBatchSets($id){
		$setObj = ($this->_wpdb->get_row("SELECT batch FROM $this->QBATCH_TABLE WHERE bid='$id'"));
		PhotoQHelper::debug('db getBatchSets: ' . print_r(($setObj->batch),true));
		PhotoQHelper::debug('db getBatchSets unser: ' . print_r(unserialize($setObj->batch),true));
		
		return unserialize($setObj->batch);
	}
	
	/**
	 * Check whether a certain column exists in a certain table
	 *
	 * @param string $tableName
	 * @param string $colName
	 * @return boolean
	 */
	function colExists($tableName, $colName){
		global $wpdb;
		// Fetch the table column structure from the database
		$colStructures = $wpdb->get_results("DESCRIBE $tableName;");	
		// Check for existence of column $colName
		$colFound = false;
		foreach($colStructures as $colStruct){
			if((strtolower($colStruct->Field) == $colName)){
				$colFound = true;
				break;
			}
		}
		return $colFound;
	}
	
	/**
	 * Check whether a certain database table exists.
	 *
	 * @param string $tableName
	 * @return boolean
	 */
	function tableExists($tableName){
		global $wpdb;
		$tables = $wpdb->get_col("SHOW TABLES;");
		$tableFound = false;
		foreach($tables as $table){
			if(strtolower($table) == $tableName){
				$tableFound = true;
				break;
			}
		}
		return $tableFound;
	}
	
	
	/**
	 * Upgrades/Installs database tables. Contains the table definitions.
	 *
	 * @param string $currentVersion
	 * @access public
	 */
	function upgrade($currentVersion)
	{
		global $wpdb;
		
		//update roles and capabilities
		$this->_setupRoles();
		
		//these steps are required to upgrade to 1.5.2 with the new q_img_id key
		//because the standard wordpress delta does not do the job correctly.
		
		//add the new key to the queue table
		if($this->tableExists($this->QUEUE_TABLE) && !$this->colExists($this->QUEUE_TABLE, 'q_img_id')){
			$wpdb->query("ALTER TABLE $this->QUEUE_TABLE DROP PRIMARY KEY");
			$wpdb->query("ALTER TABLE $this->QUEUE_TABLE ADD COLUMN q_img_id bigint(20) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY  (q_img_id)");	
		}
		
		
		//add new column for q_fk_img_id to queue meta table and match it with queue table
		if($this->tableExists($this->QUEUEMETA_TABLE) && !$this->colExists($this->QUEUEMETA_TABLE, 'q_fk_img_id')){
			//add column
			$wpdb->query("ALTER TABLE $this->QUEUEMETA_TABLE ADD COLUMN q_fk_img_id bigint(20) NOT NULL default '0'");	
		
			//populate the new ids with correct values
			$results = $wpdb->get_results("
				SELECT
				*
				FROM
				$this->QUEUE_TABLE
				WHERE 1"
			);
			if($results){
				foreach ($results as $img) {
					$update_meta_query = "UPDATE $this->QUEUEMETA_TABLE SET q_fk_img_id = $img->q_img_id
					WHERE q_fk_imgname = '".$img->q_imgname."'";
					$wpdb->query($update_meta_query);
				}
					
			}
			
			//delete the q_fk_imgname column and set the new primary key
			$wpdb->query("ALTER TABLE $this->QUEUEMETA_TABLE DROP PRIMARY KEY");
			$wpdb->query("ALTER TABLE $this->QUEUEMETA_TABLE ADD PRIMARY KEY  (q_fk_img_id,q_fk_field_id)");
			$wpdb->query("ALTER TABLE $this->QUEUEMETA_TABLE DROP q_fk_imgname");
		
		}
		
		
		//do the same for the cat table
		//add new column for q_fk_img_id to cat table and match it with queue table
		if($this->tableExists($this->QCAT_TABLE) && !$this->colExists($this->QCAT_TABLE, 'q_fk_img_id')){
			//add column
			$wpdb->query("ALTER TABLE $this->QCAT_TABLE ADD COLUMN q_fk_img_id bigint(20) NOT NULL default '0'");	
		
			//populate the new ids with correct values
			$results = $wpdb->get_results("
				SELECT
				*
				FROM
				$this->QUEUE_TABLE
				WHERE 1"
			);
			if($results){
				foreach ($results as $img) {
					$update_meta_query = "UPDATE $this->QCAT_TABLE SET q_fk_img_id = $img->q_img_id
					WHERE q_imgname = '".$img->q_imgname."'";
					$wpdb->query($update_meta_query);
				}
					
			}
			
			//delete the q_imgname column
			$wpdb->query("ALTER TABLE $this->QCAT_TABLE DROP q_imgname");
		
		}
		
		//determine charset/collation stuff same way wordpress does
		if ( $wpdb->supports_collation() ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}
		
		//echo "creating Table: ".$this->QUEUE_TABLE;
	
		$sql = "
		CREATE TABLE $this->QUEUE_TABLE (
		q_img_id bigint(20) NOT NULL AUTO_INCREMENT,
		q_position int(10) NOT NULL default '0',
		q_title varchar(200) default '',
		q_imgname varchar(200) NOT NULL default '',
		q_slug varchar(200) default '',
		q_descr text,
		q_tags text,
		q_exif text,
		q_date datetime,
		q_edited tinyint default 0,
		q_fk_author_id bigint(20) unsigned NOT NULL default '0',
		PRIMARY KEY  (q_img_id)
		) $charset_collate;";
		$this->_upgradeDB($this->QUEUE_TABLE, $sql, $currentVersion);
	
		//echo "<br>creating Table: ".$this->QCAT_TABLE;
	
		$sql = "
		CREATE TABLE $this->QCAT_TABLE (
		rel_id bigint(20) NOT NULL AUTO_INCREMENT,
		category_id bigint(20) NOT NULL default '0',
		q_fk_img_id bigint(20) NOT NULL default '0',
		PRIMARY KEY  (rel_id)
		) $charset_collate;";
		$this->_upgradeDB($this->QCAT_TABLE, $sql, $currentVersion);
		
		//echo "<br>creating Table: ".$this->QFIELDS_TABLE;
	
		$sql = "
		CREATE TABLE $this->QFIELDS_TABLE (
		q_field_id bigint(20) NOT NULL AUTO_INCREMENT,
		q_field_name varchar(200) NOT NULL default '',
		PRIMARY KEY  (q_field_id)
		) $charset_collate;";
		$this->_upgradeDB($this->QFIELDS_TABLE, $sql, $currentVersion);
	
	
		//echo "<br>creating Table: ".$this->QUEUEMETA_TABLE;
	
		$sql = "
		CREATE TABLE $this->QUEUEMETA_TABLE (
		q_fk_img_id bigint(20) NOT NULL default '0',
		q_fk_field_id bigint(20) NOT NULL default '0',
		q_field_value text,
		PRIMARY KEY  (q_fk_img_id,q_fk_field_id)
		) $charset_collate;";
		$this->_upgradeDB($this->QUEUEMETA_TABLE, $sql, $currentVersion);
		
		
		$sql = "
		CREATE TABLE $this->QBATCH_TABLE (
		bid int(10) NOT NULL AUTO_INCREMENT,
		timestamp int(11) NOT NULL,
		batch longtext,
		PRIMARY KEY  (bid)
		) $charset_collate;";
		$this->_upgradeDB($this->QBATCH_TABLE, $sql, $currentVersion);
		
	}
	
	
	/**
	 * Define what role has what capability. Called on database upgrades
	 * @return unknown_type
	 */
	function _setupRoles(){
		$capRolesArray = array(
			'access_photoq' => array('administrator','editor','author'),
			'manage_photoq_options' => array('administrator'),
			'use_primary_photoq_post_button' => array('administrator','editor','author'),
			'use_secondary_photoq_post_button' => array('administrator', 'editor'),
			'reorder_photoq' => array('administrator', 'editor', 'author')
		);

		//remove any old capabilities that might interfere with the above
		$photoQRoles = array('administrator','editor','author');
		$photoQCaps = array_keys($capRolesArray);
		foreach($photoQRoles as $roleName){
			foreach($photoQCaps as $capName){
				$currentRole = get_role($roleName);
				if(!empty($currentRole))
					$currentRole->remove_cap($capName);	
			}
		}
		//add the capabilities
		foreach($capRolesArray as $capName => $roleNames){
			foreach ($roleNames as $roleName){
				$currentRole = get_role($roleName);
				if ( !empty( $currentRole ) )
				$currentRole->add_cap($capName);
			}
		}
	}
	
		
	
	/**
	 * Upgrades the Wordpress Database Table. 
	 * Done according to the instructions given here: 
	 * 
	 * http://codex.wordpress.org/Creating_Tables_with_Plugins
	 *
	 * @param string $table	The name of the table to update.
	 * @param string $sql	The query to run.
	 * @param string $currentVersion The current PhotoQ version.
	 * @access private
	 */
	function _upgradeDB($table, $sql, $currentVersion) {
		
		if($this->_wpdb->get_var("show tables like '$table'") != $table 
					|| $currentVersion != get_option( "wimpq_version" )) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			if(get_option( "wimpq_version" )){
				update_option( "wimpq_version", $currentVersion);
			}else
				add_option("wimpq_version", $currentVersion);
		}
		
	}
	
	function deleteQueueEntry($id, $position){

		//delete DB entry
		$this->_wpdb->query("DELETE FROM $this->QUEUE_TABLE WHERE q_img_id = $id");
		//delete cat entries
		$this->_wpdb->query("DELETE FROM $this->QCAT_TABLE WHERE q_fk_img_id = $id");
		//delete field entries
		$this->_wpdb->query("DELETE FROM $this->QUEUEMETA_TABLE WHERE q_fk_img_id = $id");
		//update queue positions
		$this->_wpdb->query("UPDATE  $this->QUEUE_TABLE SET q_position = q_position-1 WHERE q_position > '$position'");

	}

	
}
?>
