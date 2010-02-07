<?php
class PhotoQHelper extends PhotoQObject
{

	function createDir($path)
	{
		$created = true;
		if (!file_exists($path)) {
			//use built-in wp function -> we have same directory permissions
			//as standard wp created directories
			$created = wp_mkdir_p($path);
		}
		return $created;
	}

	function removeDir($path)
	{
		$removed = true;
		if (file_exists($path)) {
			$removed = rmdir($path);
		}
		return $removed;
	}

	/**
	 * Remove directory and all its content recursively.
	 *
	 * @param string $filepath
	 * @return boolean
	 */
	function recursiveRemoveDir($filepath)
	{
		if (is_dir($filepath) && !is_link($filepath))
		{
			if ($dh = opendir($filepath))
			{
				while (($sf = readdir($dh)) !== false)
				{
					if ($sf == '.' || $sf == '..')
					{
						continue;
					}
					if (!PhotoQHelper::recursiveRemoveDir($filepath.'/'.$sf))
					{
						$rmError = new PhotoQErrorMessage($filepath.'/'.$sf.' could not be deleted.');
						$rmError->show();
					}
				}
				closedir($dh);
			}
			return rmdir($filepath);
		}
		if(file_exists($filepath))
		return unlink($filepath);
		else
		return false;
	}

	function getArrayOfTagNames($postID){
		return PhotoQHelper::getArrayOfTermNames($postID, 'get_the_tags');
	}
	function getArrayOfCategoryNames($postID){
		return PhotoQHelper::getArrayOfTermNames($postID, 'get_the_category');
	}
	function getArrayOfTermNames($postID, $funcName = 'get_the_tags'){
		$terms = $funcName($postID);
		$result = array();
		if ( !empty( $terms ) ) {
			foreach ( $terms as $term )
			$result[] = $term->name;
		}
		return $result;
	}

	/**
	 * Returns matching content from a directory.
	 *
	 * @param string $path			path of the directory.
	 * @param string $matchRegex	regex a filename should match.
	 * @return array	path to files that matched
	 */
	function getMatchingDirContent($path, $matchRegex)
	{
		$path = rtrim($path, '/') . '/';
		$result = array();
		if ( $handle = opendir($path) ) {
			while (false !== ($file = readdir($handle))) {
				if (preg_match($matchRegex, $file)) { //only include files matching regex
					array_push($result, $path.$file);
				}
			}
			closedir($handle);
		}
		//sort alphabetically
		sort($result);
		return $result;
	}

	/**
	 * Generates automatic name for display from filename. Removes suffix,
	 * replaces underscores, dashes and dots by spaces and capitalizes only first
	 * letter of any word.
	 *
	 * @param string $filename
	 * @return string
	 */
	function niceDisplayNameFromFileName($filename){
		//remove suffix
		$displayName = preg_replace('/\.[^\.]*$/', '', $filename);
		//replace underscores and hyphens with spaces
		$replaceWithWhiteSpace = array('-', '_', '.');
		$displayName = str_replace($replaceWithWhiteSpace, ' ', $displayName);
		//proper capitalization
		$displayName = ucwords(strtolower($displayName));
		return $displayName;
	}



	/**
	 * Moves $oldfile to $newfile, overwriting $newfile if it exists. We have to use
	 * this function instead of the builtin PHP rename because the latter does not work as expected
	 * on Windows (cf comments @ http://ch2.php.net/rename). Returns TRUE on success, FALSE on failure.
	 *
	 * @param string $oldfile The path to the file to be moved
	 * @param string $newfile The path where $oldfile should be moved to.
	 *
	 * @return boolean TRUE if file is successfully moved
	 *
	 * @access public
	 */
	function moveFile($oldfile,$newfile)
	{
		if (!rename($oldfile,$newfile)) {
			if (copy ($oldfile,$newfile)) {
				unlink($oldfile);
				return TRUE;
			}
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Same as above but returns error if file already exists at destination.
	 *
	 * @param string $oldfile The path to the file to be moved.
	 * @param string $newfile The path where $oldfile should be moved to.
	 * @return boolean TRUE if file successfully moved
	 */
	function moveFileIfNotExists($oldfile, $newfile)
	{
		if(!file_exists($newfile))
		return PhotoQHelper::moveFile($oldfile,$newfile);
		else
		return FALSE;
	}

	function mergeDirs($oldfile, $newfile){
		if(!file_exists($newfile)){
			return PhotoQHelper::moveFile($oldfile,$newfile);
		}else
		if(is_dir($oldfile) && is_dir($newfile)){
			$oldfile = rtrim($oldfile,'/').'/';
			$newfile = rtrim($newfile,'/').'/';
			//get all visible files from old img dir
			$match = '#^[^\.]#';//exclude hidden files starting with .
			$visibleFiles = PhotoQHelper::getMatchingDirContent($oldfile, $match);
			foreach($visibleFiles as $file2merge){
				PhotoQHelper::mergeDirs($file2merge, str_replace($oldfile,$newfile,$file2merge));
				/*if(!$res){
				 return false;
					}*/
			}
		}else{
			return false;
		}
	}

	/**
	 * PHP built-in array_combine only works for PHP5.
	 * This function should do more or less the same and
	 * also work with PHP4.
	 *
	 * @param array $keys
	 * @param array $values
	 * @return array
	 */
	function arrayCombine($keys, $values) {
		$out = array();
			
		$keys = array_values($keys);
		$values = array_values($values);
			
		foreach( $keys as $index => $key ) {
			$out[(string)$key] = $values[$index];
		}
			
		return $out;
	}

	/**
	 * PHP built-in str_ireplace only works for PHP5.
	 * This function should do more or less the same and
	 * also work with PHP4. Other option would have been
	 * to include the corresponding lib from PEAR::PHP_Compat
	 * @param $needle
	 * @param $str
	 * @param $haystack
	 * @return unknown_type
	 */
	function strIReplace($needle, $str, $haystack) {
		$needle = preg_quote($needle, '/');
		return preg_replace("/$needle/i", $str, $haystack);
	}


	/**
	 * PHP built-in pathinfo() does not have filename field
	 * under PHP4. This is a fix for this.
	 *
	 * @param string $path
	 * @return array
	 */
	function pathInfo($path){
		$pathParts = pathinfo($path);
		// if php4
		if(!isset($pathParts['filename'])){
			$pathParts['filename'] = substr($pathParts['basename'], 0,strpos($pathParts['basename'],'.'));
		}
		return $pathParts;
	}

	/**
	 * Converts absolute path to relative url
	 *
	 * @param string $path
	 * @return string
	 */
	function getRelUrlFromPath($path)
	{
		//replace WP_CONTENT_DIR with WP_CONTENT_URL
		$wpcd = str_replace('\\', '/', WP_CONTENT_DIR);
		if(strpos($path, $wpcd) === 0)//it starts with WP_CONTENT_DIR
		return str_replace($wpcd, WP_CONTENT_URL, $path);

		//convert backslashes (windows) to slashes
		$abs = str_replace('\\', '/', ABSPATH);
		$path = str_replace('\\', '/', $path);
		//remove ABSPATH
		$relUrl = str_replace($abs, '', trim($path));
		//remove slashes from beginning
		//echo "<br/> relURl: $relUrl </br>";
		return trailingslashit( get_option( 'siteurl' ) ) . preg_replace('/^\/*/', '', $relUrl);
	}

	/**
	 * Reduces multidimensional array to single dimension.
	 *
	 * @param array $in
	 * @return array
	 */
	function flatten($in){
		$out = array();
		if(is_array($in)){
			foreach ($in as $key => $value){
				if(is_array($value)){
					unset($in[$key]);
					$out = array_merge($out,PhotoQHelper::flatten($value));
				}else
				$out[$key] = $value;
			}
		}
		return $out;
	}


	/**
	 * Gets an array of all the <$tag>content</$tag> tags contained in $string.
	 *
	 * @param string $tag
	 * @param string $string
	 * @return array
	 */
	function getHTMLTags($tag, $string)
	{
		$result = array();
		$bufferedOpen = array();
		$offset = 0;
		$nextClose = strpos($string, "</$tag>", $offset);
		while($nextClose !== false){
			$nextOpen = strpos($string, "<$tag", $offset);
			$offset = $nextClose;
			while($nextOpen < $nextClose && $nextOpen !== false){
				array_push($bufferedOpen,$nextOpen);
				$nextOpen = strpos($string, "<$tag", $nextOpen+1);
			}
			//we got a pair
			$start = array_pop($bufferedOpen);
			array_push($result,substr($string,$start,$nextClose-$start+strlen($tag)+3));
			$nextClose = strpos($string, "</$tag>", $nextClose+1);
		}
		return $result;
	}


	/**
	 * Fills in shorttags into the format string specified
	 * @param $format
	 * @param $keyValArray
	 * @return unknown_type
	 */
	function formatShorttags($format, $tagValArray)
	{
		foreach ($tagValArray as $tag => $val)
		$format = str_replace("[$tag]",$val,$format);
		return $format;
	}

	/**
	 * Determines whether the given shorttag is part of the formatting string given.
	 * @param $format
	 * @param $tag
	 * @return unknown_type
	 */
	function containsShorttag($format, $tag){
		return strpos($format, $tag) !== false;
	}

	/**
	 * Given array of shorttags, checks whether the format string contains least one of them.
	 * @param $format
	 * @param $tags
	 * @return unknown_type
	 */
	function containsAnyOfTheseShorttags($format, $tags){
		foreach ($tags as $tag){
			if(PhotoQHelper::containsShorttag($format,$tag)){
				return true;
			}
		}
		return false;
	}


	/**
	 * Get the maximum allowable file size in KB from php.ini
	 *
	 * @return integer the maximum size in kilobytes
	 */
	function getMaxFileSizeFromPHPINI()
	{
		$max_upl_size = strtolower( ini_get( 'upload_max_filesize' ) );
		$max_upl_kbytes = 0;
		if (strpos($max_upl_size, 'k') !== false)
		$max_upl_kbytes = $max_upl_size;
		if (strpos($max_upl_size, 'm') !== false)
		$max_upl_kbytes = $max_upl_size * 1024;
		if (strpos($max_upl_size, 'g') !== false)
		$max_upl_kbytes = $max_upl_size * 1024 * 1024;

		return $max_upl_kbytes;
	}


	/**
	 * Logs message $msg to a file if debbugging is enabled.
	 *
	 * @param string $msg   The message to be logged to the file.
	 *
	 * @access public
	 */
	function debug($msg)
	{
		if(PHOTOQ_DEBUG_LEVEL >= PHOTOQ_LOG_MESSAGES){
			require_once realpath(PHOTOQ_PATH.'lib/Log-1.9.11/Log.php');
			$conf = array('mode' => 0777, 'timeFormat' => '%X %x');
			$logger = &Log::singleton('file', PHOTOQ_PATH.'log/out.log', '', $conf);
			$logger->log($msg);
		}
	}

	/**
	 * Escapes an entire array to prevent SQL injection.
	 * @param $array the array to be escaped.
	 * @return Array the escaped array
	 */
	function arrayAttributeEscape($array){
		if(is_array($array))
		return array_map("attribute_escape",$array);
		else
		return attribute_escape($array);
	}

	/**
	 * Encodes all string elements of a (possibly nested) array using htmlentities.
	 * @param $array the array whose elements are to be encoded.
	 * @return Array the encoded array
	 */
	function arrayHtmlEntities($array){
		if(is_array($array))
		return array_map(array('PhotoQHelper', 'arrayHtmlEntities'),$array);
		else{
			if(is_string($array))
			return htmlentities($array);
			else
			return $array;
		}
	}


	/**
	 * Outputs the category list where the user can select categories.
	 * @param $q_id int id of the queued photo for which to choose cats
	 * @param $default int id of the default photoq category
	 * @param $selectedCats array category ids of categories that should appear selected
	 */
	function showCategoryCheckboxList( $q_id = 0, $default = 0, $selectedCats = array() ) {

		if(!$selectedCats)
		{
			// No selected categories, set to default
			$selectedCats[] = $default;
		}

		//$q_id = preg_replace('/\./','_',$q_id); //. in post vars become _

		// check the fold option
		$oc =& PhotoQSingleton::getInstance('PhotoQOptionController');
		$closed = $oc->getValue('foldCats') ? 'closed' : '';

		echo '<div class="postbox '.$closed.'">';
		echo '<h3 class="postbox-handle"><span>'.__('Categories','PhotoQ').'</span></h3>';
		echo '<div class="inside">';
		echo '<ul>';
		//$this->category_checklist(0,0,$selectedCats,$q_id);
		wp_category_checklist( 0, 0, $selectedCats, false, new Walker_PhotoQ_Category_Checklist($q_id));

		echo '</ul></div></div>';
	}

	/**
	 * Shows the list of meta fields
	 * @param $id int if given shows the meta field of queued photo with this id.
	 */
	function showMetaFieldList($id = 0){
		$db =& PhotoQSingleton::getInstance('PhotoQDB');
		if($results = $db->getAllFields()){
			echo '<div class="info_group">';
				
			foreach ($results as $field_entry) {
				if($id){
					//get posted values if any from common info
					$field_value = attribute_escape(stripslashes($_POST[$field_entry->q_field_name][0]));
					if(empty($field_value)){
						//get the stored values
						$field_value = $this->_db->getFieldValue($id, $field_entry->q_field_id);
					}
				}
				echo '<div class="info_unit">'.$field_entry->q_field_name.':<br /><textarea style="font-size:small;" name="'.$field_entry->q_field_name.'[]" cols="30" rows="3"  class="uploadform">'.$field_value.'</textarea></div>';
			}
				
			echo '</div>';
		}
	}

	/**
	 * Checks whether we are dealing with standard WP or WPMU
	 * @return boolean
	 */
	function isWPMU(){
		return function_exists( 'is_site_admin' );
	}




	/**
	 * Checks whether a post is a photo post. A post is considered a photopost if the same image
	 * appears in the content and the excerpt part
	 *
	 * @param object $post The post to be checked
	 * @return boolean True if the post is photo post
	 * @access public
	 */
	/*function isPhotoPost($post)
	 {
		$imgTags = $this->getHtmlTags($post->post_excerpt, "img");
		if(!count($imgTags)){
		return false;
		}
		foreach($imgTags as $thumb){
		$attributes = $this->getAttributesFromHtmlTag($thumb);
		$thumbName = basename($attributes['src']);
		$expectedImgName = basename($this->getImgPathFromThumbPath($attributes['src'], $post));
		if($thumbName == $expectedImgName)
		return false;//it didn't have the thumb_identifier

		//now check whether the content part has an image tag with $expectedImgName
		if(!$this->getImgTagByName($post->post_content, $expectedImgName))
		return false;
		}

		return true;
		}*/


	/**
	 * Extracts attribute - value pairs from HTML tags.
	 *
	 * @param string $tag The tag to get the pairs from.
	 * @return Array associative array containing attribute value pairs.
	 * @access public
	 *
	 */
	/*function getAttributesFromHtmlTag($tag)
	 {
		$result = array();

		if(PhotoQHelper::isSingleHtmlTag($tag)){
		//get and return the attribute->value pairs
		preg_match_all('/(\w+)=\"([^\"]+)\"/', $tag, $attributes, PREG_SET_ORDER);
		foreach ($attributes as $attr){
		$result[$attr[1]] = $attr[2];
		}
		}
		return $result;
		}*/


	/**
	 * Checks whether the string contains one single HTML tag and nothing else. Single HTML tag if
	 * one single opening bracket at beginning and one single closing bracket that is at the end of
	 * the string.
	 *
	 * @param string $string The string to be checked.
	 * @return boolean True if the string contains one single HTML tag.
	 * @access public
	 *
	 */
	/*function isSingleHtmlTag($string)
	 {
		return preg_match('/^<[^<^>]*>$/',$string);
		}*/

	/**
	 * Returns all HTML tags of a given type found in a string.
	 *
	 * @param string $string The string to be searched.
	 * @param string $tag The type of tag to look for.
	 * @return Array array containing the img tags.
	 * @access public
	 *
	 */
	/*function getHtmlTags($string, $tag)
	 {
		preg_match_all("/<$tag [^<^>]*>/", $string, $foundTags, PREG_PATTERN_ORDER);
		return $foundTags[0];
		}*/

	/**
	 * Constructs image path from thumb paths.
	 *
	 * @param string $thumbPath The path of the thumbnail.
	 * @param object $post The post to which the thumbnail belongs.
	 * @return mixed string path of the corresponding image if transformation
	 * succeeds, null if we don't know how to do the backwards transfromation.
	 *
	 * @access public
	 *
	 */
	/*function getImgPathFromThumbPath($thumbPath, $post)
	 {
		$imgPath = preg_replace("/.".$this->_oc->getThumbIdentifier()."/",'',$thumbPath);
		if ($thumbPath == $this->getThumbPathFromImgPath($imgPath)){
		return $imgPath;
		}
		else{
		//there was a non standard transformation, we have to look through the
		//whole post and match the image path via the backwards transformation
		$imgTags = $this->getHtmlTags($post->post_content, "img");
		foreach($imgTags as $img){
		$attributes = $this->getAttributesFromHtmlTag($img);
		$imgPath = $attributes['src'];
		if ( $thumbPath == $this->getThumbPathFromImgPath($imgPath)){
		return $imgPath;
		}
		}
		return null;
		}
		}*/


	/*returns the name of the thumbnail path from the image path*/
	/*function getThumbPathFromImgPath($imgPath) {
		// If no filters change the filename, we'll do a default transformation.
		$thumb = preg_replace('!(\.[^.]+)?$!', ".".$this->_oc->getThumbIdentifier() . '$1', basename($imgPath), 1);
		return str_replace(basename($imgPath), $thumb, $imgPath);
		}*/

	/**
	 * Looks in $string whether it finds an img tag with an image of filename $name.
	 *
	 * @param string $string look for img tag in this string.
	 * @param string $name the name to look for.
	 * @return mixed null if not found, img tag if found.
	 * @access public
	 *
	 */
	/*function getImgTagByName($string, $name)
	 {
		$imgTags = $this->getHtmlTags($string, "img");
		foreach($imgTags as $img){
		$attributes = $this->getAttributesFromHtmlTag($img);
		$imgName = basename($attributes['src']);
		if($imgName == $name){
		return $img;
		}
		}

		return null;
		}*/

	/**
	 * Checks whether an <img> tag is part of a link, i.e. a child of an <a> tag.
	 *
	 * @param string $string The string in which to check
	 * @param string $imgTag The <img> tag to check
	 * @return boolean True if it is part of a link, False if not
	 * @access public
	 *
	 */
	/*function isPartOfLink($string,$imgTag)
	 {
		foreach($this->getAllLinks($string) as $link){
		if(preg_match("#$imgTag#",$link))
		return true;
		}
		return false;
		}*/

	/**
	 * Returns all links (<a href="bla.html">bla bla</a>) contained in a string.
	 *
	 * @param string $string	The string in which to look for links.
	 * @return Array 			Array of links found
	 * @access public
	 *
	 */
	/*function getAllLinks($string)
	 {
		preg_match_all('#<a.*?</a>#', $string, $matches);
		return $matches[0];
		}*/


	/**
	 * Transforms an associative array of attributes to string of attribute="value" pairs.
	 *
	 * @param Array $attributes the attributes to be converted
	 * @return string the string of attribute="value" pairs.
	 * @access public
	 *
	 */
	/*function attibutesToString($attributes){
		$result = '';
		foreach($attributes as $attribute => $value){
		$result .= $attribute . '="' . $value . '" ';
		}
		return $result;
		}*/


	/**
	 * This is a filter hooked into the the_content WordPress hook. Replaces image tags with a link
	 * to corresponding image, the link text being the thumbnail version. This enables scripts like
	 * Lightbox and Shutter Reloaded.
	 *
	 * @param string $content     The content of the post as it is stored in the WordPress Database.
	 *
	 * @returns string            The content with images replaced with image links.
	 * @access public
	 */
	/*function replaceImagesWithImageLink($content)
	 {
		global $post;
		if($this->isPhotoPost($post)){

		$thumbs = $this->getHtmlTags($post->post_excerpt, "img");

		foreach($thumbs as $thumb){
		$thumbAttributes = $this->getAttributesFromHtmlTag($thumb);
		$expectedImgName = basename($this->getImgPathFromThumbPath($thumbAttributes['src'],$post));

		if($img = $this->getImgTagByName($post->post_content, $expectedImgName)){
		if(!$this->isPartOfLink($post->post_content,$img)){
		//build image link
		$imgAttributes = $this->getAttributesFromHtmlTag($img);
		$imgLink = '<a '. stripslashes(html_entity_decode($this->_oc->getValue('imgLinksAttributes'))) . ' href="'.$imgAttributes['src'].'"><img ';
		$imgLink .= $this->attibutesToString($thumbAttributes);
		$imgLink .= '/></a>';
		//replace image tag with img link
		$content = preg_replace('#'.preg_quote($img, '#').'#',$imgLink,$content);
		}
		}
		}
		}
		return $content;
		}

		function listAllPhotosWithoutParent($currentName, $currentParent){
		global $wpdb;
		//check whether this one has children
		if($children = $this->getPhotoChildren($currentName)){
		echo "";
		}else{
		//get possible parent photos
		$results = $wpdb->get_results("
		SELECT
		q_imgname, q_title
		FROM
		$this->QUEUE_TABLE
		WHERE
		q_parent = ''
		");
		if($results){
		echo 'Parent Photo: <select name="img_parent[]">';
		echo '<option value="">None</option>';

		foreach($results as $parent)
		if($parent->q_imgname != $currentName){
		echo '<option';
		if($parent->q_imgname == $currentParent)
		echo ' selected="selected"';
		echo ' value="'.$parent->q_imgname.'">'. $parent->q_title.'</option>';
		}
		echo '</select>';
		}
		}
		}

		function getPhotoChildren($currentName){
		global $wpdb;
		$children = $wpdb->get_results("
		SELECT
		*
		FROM
		$this->QUEUE_TABLE
		WHERE
		q_parent = '$currentName'
		");

		return $children;
		}*/

	/**
	 * This is a filter hooked into the the_content WordPress hook. Allows to modify the_content on
	 * the fly, e.g., replace image tags with a link to corresponding image, the link text being the
	 * thumbnail version. This enables scripts like Lightbox and Shutter Reloaded.
	 *
	 * @param string $content     The content of the post as it is stored in the WordPress Database.
	 *
	 * @returns string            The content with images replaced with image links.
	 * @access public
	 */
	/*function modifyContentOnTheFly($content)
	 {
		return $this->_modifyOnTheFly($content);
		}*/


	/**
	 * This is a filter hooked into the the_excerpt WordPress hook. Allows to modify the_excerpt on
	 * the fly, e.g., replace image tags with a link to corresponding image, the link text being the
	 * thumbnail version. This enables scripts like Lightbox and Shutter Reloaded.
	 *
	 * @param string $excerpt     The excerpt of the post as it is stored in the WordPress Database.
	 *
	 * @returns string            The modified excerpt e.g. with images replaced with image links.
	 * @access public
	 */
	/*function modifyExcerptOnTheFly($excerpt)
	 {
		return $this->_modifyOnTheFly($excerpt, 'excerpt');
		}*/


	/**
	 * Both of the above filter functions call this one to do the work.
	 *
	 * @param string $data		data to be modified, either from excerpt or content
	 * @param string $viewName	indicates whether we are dealing with excerpt or content
	 * @return string $data the modified data
	 * @access private
	 */
	/*function _modifyOnTheFly($data, $viewName = 'content')
	 {
		global $post;

		if($this->isPhotoPost($post)){
		$photo =& PhotoQPhoto::createInstance('PhotoQPublishedPhoto', $post->ID, $post->title);
		$data = $photo->generateContent($viewName);
		}
		return $data;
		}*/


}

/**
 * My own category walker visitor object that will output categories in array syntax such that we can
 * have multiple category dropdown lists on the same page.
 */
class Walker_PhotoQ_Category_Checklist extends Walker {
	var $tree_type = 'category';
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this
	var $q_id;
	/**
	 * PHP4 type constructor
	 */
	function Walker_PhotoQ_Category_Checklist($q_id)
	{
		$this->__construct($q_id);
	}

	/**
	 * PHP5 type constructor
	 */
	function __construct($q_id)
	{
		$this->q_id = $q_id;
	}

	function start_lvl(&$output, $depth, $args) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='wimpq_subcats'>\n";
	}

	function end_lvl(&$output, $depth, $args) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	function start_el(&$output, $category, $depth, $args) {
		extract($args);

		$class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
		$output .= "\n<li id='category-$category->term_id-".$this->q_id."'$class>" . '<label for="in-category-' . $category->term_id . '-'.$this->q_id.'" class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="post_category['.$this->q_id.'][]" id="in-category-' . $category->term_id . '-'.$this->q_id.'"' . (in_array( $category->term_id, $selected_cats ) ? ' checked="checked"' : "" ) . '/> ' . wp_specialchars( apply_filters('the_category', $category->name )) . '</label>';
	}

	function end_el(&$output, $category, $depth, $args) {
		$output .= "</li>\n";
	}


}


/**
 * Shamelessly copied from Drupal. This gives us a set of timers that we for now use
 * in the batch processing stuff to prevent script timeouts.
 * Usage:
 *
 * $timer =& PhotoQSingleton::getInstance('PhotoQTimers');
 * $timer->start('batchProcessing');
 * if($timer->read('batchProcessing') < 1000) ...
 *
 * @author manu
 *
 */
class PhotoQTimers extends PhotoQSingleton
{
	/**
	 * @var Array of registered timers
	 */
	var $_timers;
	function __construct(){
		$_timers = array();
	}

	/**
	 * Start the timer with the specified name. If you start and stop
	 * the same timer multiple times, the measured intervals will be
	 * accumulated.
	 *
	 * @param name
	 *   The name of the timer.
	 */
	function start($name) {

		list($usec, $sec) = explode(' ', microtime());
		$this->_timers[$name]['start'] = (float)$usec + (float)$sec;
		$this->_timers[$name]['count'] = isset($this->_timers[$name]['count']) ? ++$this->_timers[$name]['count'] : 1;
	}

	/**
	 * Read the current timer value without stopping the timer.
	 *
	 * @param name
	 *   The name of the timer.
	 * @return
	 *   The current timer value in ms.
	 */
	function read($name) {

		if (isset($this->_timers[$name]['start'])) {
			list($usec, $sec) = explode(' ', microtime());
			$stop = (float)$usec + (float)$sec;
			$diff = round(($stop - $this->_timers[$name]['start']) * 1000, 2);

			if (isset($this->_timers[$name]['time'])) {
				$diff += $this->_timers[$name]['time'];
			}
			return $diff;
		}
	}

	/**
	 * Stop the timer with the specified name.
	 *
	 * @param name
	 *   The name of the timer.
	 * @return
	 *   A timer array. The array contains the number of times the
	 *   timer has been started and stopped (count) and the accumulated
	 *   timer value in ms (time).
	 */
	function stop($name) {
		$this->_timers[$name]['time'] = $this->read($name);
		unset($this->_timers[$name]['start']);

		return $this->_timers[$name];
	}


}






?>
