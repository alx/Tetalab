<?php

class PhotoQImageSize extends PhotoQObject
{
	
	var $_name;
	var $_imgName;
	var $_yearMonthDir;
	var $_originalWidth;
	var $_originalHeight;
	var $_ratio;
	var $_oc;
	var $_dirPath;
	var $_yearMonthDirPath;
	var $_path;
	var $_quality;
	var $_watermark;
	var $_crop;

	
	
	/**
	 * PHP4 type constructor
	 */
	/*function PhotoQImageSize($name, $imgName, $yearMonthDir, $originalWidth, $originalHeight)
	{
		$this->__construct($name, $imgName, $yearMonthDir, $originalWidth, $originalHeight);
	}*/


	/**
	 * PHP5 type constructor
	 */
	function __construct($name, $imgName, $yearMonthDir, $originalWidth, $originalHeight)
	{
		$this->_name = $name;
		$this->_imgName = $imgName;
		$this->_yearMonthDir = $yearMonthDir;
		$this->_originalWidth = $originalWidth;
		$this->_originalHeight = $originalHeight;
		//PhotoQHelper::debug('name: ' . $imgName);
		//PhotoQHelper::debug('height: ' . $originalHeight);
		$this->_ratio = $this->_originalWidth/$this->_originalHeight;
		$this->_oc =& PhotoQSingleton::getInstance('PhotoQOptionController');
		$this->_dirPath = $this->_oc->getImgDir() . $this->_name . '/';
		$this->_yearMonthDirPath = $this->_dirPath . $this->_yearMonthDir;
		$this->_path = $this->_yearMonthDirPath . $this->_imgName;
		
		if(!is_a($this, 'PhotoQOriginalImageSize')){ 
			$this->_quality = $this->_oc->getValue($this->_name . '-imgQuality');
			$this->_watermark = $this->_oc->getValue($this->_name.'-watermark');
		}
		$this->_crop = false;
		
	}
	
	
	/**
	 * Use this one (factory pattern) to create instances of this class. 
	 *
	 * @param unknown_type $name
	 */
	function &createInstance($name, $imgName, $yearMonthDir, $width, $height)
	{
		if($name == $this->_oc->ORIGINAL_IDENTIFIER)
			$inst =& new PhotoQOriginalImageSize($name, $imgName, $yearMonthDir, $width, $height);
		else
			switch($this->_oc->getValue($name.'-imgConstraint')){
				case 'rect':
					$inst =& new PhotoQRectImageSize($name, $imgName, $yearMonthDir, $width, $height);
					break;
				case 'side':
					$inst =& new PhotoQSideImageSize($name, $imgName, $yearMonthDir, $width, $height);
					break;
				case 'fixed':
					$inst =& new PhotoQFixedImageSize($name, $imgName, $yearMonthDir, $width, $height);
					break;
				case 'noResize':
					$inst =& new PhotoQImageSize($name, $imgName, $yearMonthDir, $width, $height);
					break;
			}

		return $inst;

	}
	
	
	
	function getThisPathFromOriginalPath($originalPath){
		return preg_replace('#'.$this->_oc->getImgDir() . $this->_oc->ORIGINAL_IDENTIFIER. '#', $this->_dirPath, $originalPath);
	}
	
	function getDirPath()
	{
		return $this->_dirPath;
	}
	
	function getYearMonthDirPath()
	{
		return $this->_yearMonthDirPath;
	}
	
	function getPath()
	{
		return str_replace('\\', '/', $this->_path);
		//return $this->_path;
	}
	
	function getUrl()
	{
		return PhotoQHelper::getRelUrlFromPath($this->_path);
	}
	
	function getName()
	{
		return $this->_name;
	}
	
	
	
	function getScaledWidth()
	{
		return $this->_originalWidth;
	}
	
	function getScaledHeight()
	{
		return $this->_originalHeight;
	}
	
	function _widthCounts()
	{
		return true;
	}
	
	
	function createPhoto($oldOriginalPath, $moveOriginal = true){
		PhotoQHelper::debug('enter createPhoto()');
		//create the needed year-month-directory
		if(!PhotoQHelper::createDir($this->_yearMonthDirPath))
			return new PhotoQErrorMessage(sprintf(__('Error when creating directory: %s.', 'PhotoQ'),$this->_yearMonthDirPath));
		if($this->_crop)//constr. width and height decide
			$status = $this->createThumb($oldOriginalPath, $this->_path, $this->getScaledWidth(), $this->getScaledHeight());
		else
			if($this->_widthCounts()){ //it is the width that counts
				$status = $this->createThumb($oldOriginalPath, $this->_path, $this->getScaledWidth());
			}else{ //it is height
				$status = $this->createThumb($oldOriginalPath, $this->_path, 0, $this->getScaledHeight());
			}
		PhotoQHelper::debug('leave createPhoto()');
		return $status;
	}
	
	function deleteResizedPhoto()
	{
		if(file_exists($this->_path))
			unlink($this->_path);
	}
	
	function createThumb($inFile, $outFile, $width = 0, $height = 0)
	{
		PhotoQHelper::debug('enter createThumb() ' . $this->getName());
		require_once(PHOTOQ_PATH.'lib/phpThumb_1.7.9/phpthumb.class.php');
		// create phpThumb object
		$phpThumb = new phpThumb();
		//set imagemagick path here
		$phpThumb->config_imagemagick_path = 
			( $this->_oc->getValue('imagemagickPath') ? $this->_oc->getValue('imagemagickPath') : null );
		
		// set data source -- do this first, any settings must be made AFTER this call
		$phpThumb->setSourceFilename($inFile);
		
		// PLEASE NOTE:
		// You must set any relevant config settings here. The phpThumb
		// object mode does NOT pull any settings from phpThumb.config.php
		//$phpThumb->setParameter('config_document_root', '/home/groups/p/ph/phpthumb/htdocs/');
		$phpThumb->setParameter('config_temp_directory', $this->_oc->getCacheDir());
		
		// set parameters (see "URL Parameters" in phpthumb.readme.txt)
		if($height)
			$phpThumb->setParameter('h', $height);
		if($width)
			$phpThumb->setParameter('w', $width);
			
		$phpThumb->setParameter('q', $this->_quality);
		
		//rect images may be cropped to the exact size
		if($this->_crop)
			$phpThumb->setParameter('zc', 'C');
		
		//$phpThumb->setParameter('fltr', 'gam|1.2');
		if($this->_watermark && $wmPath = get_option('wimpq_watermark')){
			$phpThumb->setParameter('fltr', 
			'wmi|'.$wmPath.'|'.
			$this->_oc->getValue('watermarkPosition').'|'.
			$this->_oc->getValue('watermarkOpacity').'|'.
			$this->_oc->getValue('watermarkXMargin').'|'.
			$this->_oc->getValue('watermarkYMargin'));
		}
		PhotoQHelper::debug('generating thumb...');
		// generate & output thumbnail
		//$output_filename = './thumbnails/'.basename($name.'_'.$largestSide.'.'.$phpThumb->config_output_format;
		if ($phpThumb->GenerateThumbnail()) { // this line is VERY important, do not remove it!
			PhotoQHelper::debug('generation ok');
			if ($phpThumb->RenderToFile($outFile)) {
				PhotoQHelper::debug('rendering ok');
				// do something on success
				return new PhotoQStatusMessage(__('Thumb created successfully', 'PhotoQ'));
			} else {
				PhotoQHelper::debug('rendering failed');
				// do something with debug/error messages
				return new PhotoQErrorMessage(__('Failed:','PhotoQ') . '<pre>' .implode("\n\n", $phpThumb->debugmessages).'</pre>');
			}
		} else {
			// do something with debug/error messages
			return new PhotoQErrorMessage(__('Failed:','PhotoQ') . '<pre>' .$phpThumb->fatalerror."\n\n".implode("\n\n", $phpThumb->debugmessages).'</pre>' );
		}
		
	}
	
}



class PhotoQRectImageSize extends PhotoQImageSize
{
	
	var $_constrWidth;
	var $_constrHeight;
	var $_rectRatio;
	
	/**
	 * PHP4 type constructor
	 */
	/*function PhotoQRectConstraint($name, $imgName, $yearMonthDir, $width, $height)
	{
		$this->__construct($name, $imgName, $yearMonthDir, $width, $height);
	}*/


	/**
	 * PHP5 type constructor
	 */
	function __construct($name, $imgName, $yearMonthDir, $width, $height)
	{
		parent::__construct($name, $imgName, $yearMonthDir, $width, $height);
		$this->_constrWidth = $this->_oc->getValue($name.'-imgWidth');
		$this->_constrHeight = $this->_oc->getValue($name.'-imgHeight');
		$this->_rectRatio = $this->_constrWidth/$this->_constrHeight;
		//only crop rect images
		$this->_crop = $this->_oc->getValue($this->_name.'-zoomCrop');
		
	}
	
	function _widthCounts()
	{
		return $this->_ratio >= $this->_rectRatio;
	}
	
	
	function getScaledWidth()
	{
		if($this->_crop)
			return $this->_constrWidth;
		else
			if($this->_ratio >= $this->_rectRatio)
				return min($this->_originalWidth, $this->_constrWidth);	
			else
				return min($this->_originalWidth, round($this->_constrHeight*$this->_ratio));
	}
	
	function getScaledHeight()
	{
		if($this->_crop)
			return min($this->_originalHeight, $this->_constrHeight);
		else
			if($this->_ratio >= $this->_rectRatio)
				return min($this->_originalHeight, round($this->_constrWidth/$this->_ratio));
			else
				return min($this->_originalHeight, $this->_constrHeight);
	}
	
}

class PhotoQSideImageSize extends PhotoQImageSize
{
	
	var $_constrSide;
	
	/**
	 * PHP4 type constructor
	 */
	/*function PhotoQSideConstraint($name, $imgName, $yearMonthDir, $width, $height)
	{
		$this->__construct($name, $imgName, $yearMonthDir, $width, $height);
	}*/


	/**
	 * PHP5 type constructor
	 */
	function __construct($name, $imgName, $yearMonthDir, $width, $height)
	{
		parent::__construct($name, $imgName, $yearMonthDir, $width, $height);
		$this->_constrSide = $this->_oc->getValue($name.'-imgSide');
	}
	
	function _widthCounts()
	{
		return $this->_ratio < 1;
	}
	
	function getScaledWidth()
	{
		if($this->_ratio >= 1)
			return min($this->_originalWidth, round($this->_constrSide*$this->_ratio));
		else
			return min($this->_originalWidth, $this->_constrSide);
	}
	
	function getScaledHeight()
	{
		if($this->_ratio >= 1)
			return min($this->_originalHeight, $this->_constrSide);
		else
			return min($this->_originalHeight, round($this->_constrSide/$this->_ratio));
	}
		
	
}


class PhotoQFixedImageSize extends PhotoQImageSize
{
	
	var $_constrFixed;
	
	/**
	 * PHP4 type constructor
	 */
	/*function PhotoQFixedImageSize($name, $imgName, $yearMonthDir, $width, $height)
	{
		$this->__construct($name, $imgName, $yearMonthDir, $width, $height);
	}*/


	/**
	 * PHP5 type constructor
	 */
	function __construct($name, $imgName, $yearMonthDir, $width, $height)
	{
		parent::__construct($name, $imgName, $yearMonthDir, $width, $height);
		$this->_constrFixed = $this->_oc->getValue($name.'-imgFixed');
	}
	
	function _widthCounts()
	{
		return $this->_ratio >= 1;
	}
	
	function getScaledWidth()
	{
		if($this->_ratio >= 1)
			return min($this->_originalWidth, $this->_constrFixed);
		else
			return min($this->_originalWidth, round($this->_constrFixed*$this->_ratio*$this->_ratio));
	}
	
	function getScaledHeight()
	{
		if($this->_ratio >= 1)
			return min($this->_originalHeight, round($this->_constrFixed/$this->_ratio));
		else
			return min($this->_originalHeight, round($this->_constrFixed*$this->_ratio));
	}
		
	
}


class PhotoQOriginalImageSize extends PhotoQImageSize
{
	
	/**
	 * Overwrites default behavior. No call to phpThumb needed for original photo. 
	 * Just move it to the imgdir.
	 *
	 * @param unknown_type $oldOriginalPath
	 * @return unknown
	 */
	function createPhoto($oldOriginalPath, $moveOriginal = true){
	
		//create directory
		if(!PhotoQHelper::createDir($this->_yearMonthDirPath))
			return new PhotoQErrorMessage(sprintf(_c('Error when creating directory: %s| dirname', 'PhotoQ'), $this->_yearMonthDirPath));
		//move the image file
		if (!file_exists($this->_path)) {
			if($moveOriginal){
				if(!PhotoQHelper::moveFile($oldOriginalPath, $this->_path))
					return new PhotoQErrorMessage(sprintf(_c('Unable to move %s, posting aborted.| imgname', 'PhotoQ'), $this->_imgName));
			}else{ //we don't move we only copy
				if (!copy($oldOriginalPath, $this->_path)){
					return new PhotoQErrorMessage(sprintf(_c('Unable to copy %s, posting aborted.| imgname', 'PhotoQ'), $this->_imgName));
				}
			}
		}else{
			return new PhotoQErrorMessage(sprintf(_c('Image %s already exists, posting aborted.| imgname', 'PhotoQ'), $this->_imgName));
		}
	
		return new PhotoQStatusMessage(__('Original photo moved successfully'));
	
	}
	
	/**
	 * Never delete orginal file like a scaled one. Use special function destroyForever()
	 *
	 * @param unknown_type $imgName
	 * @param unknown_type $this->_yearMonthDir
	 */
	function deleteResizedPhoto()
	{
		return false;
	}
	
	
	/**
	 * We never create a thumb for the original image size.
	 */
	function createThumb($inFile, $outFile, $width = 0, $height = 0)
	{
		return new PhotoQErrorMessage(__('Failed:','PhotoQ') . '<pre> ' . __("Don't call createThumb() on original image.", 'PhotoQ') .'</pre>' );	
	}
			
}



?>