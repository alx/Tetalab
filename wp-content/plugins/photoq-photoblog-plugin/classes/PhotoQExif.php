<?php

/**
 * This class deals with EXIF meta data embedded in the photos.
 *
 */
class PhotoQExif extends PhotoQObject
{
	
	/**
	 * Get associative array with exif info from a photo
	 *
	 * @param string $path	Path to the photo.
	 * @return array		Exif info in associative array.
	 */
	function readExif($path)
	{
		//include and call the exifixer script
		require_once realpath(PHOTOQ_PATH.'lib/exif/exif.php');
		$fullexif = read_exif_data_raw($path, 0);
		//we now retain only the useful (whatever it means ;-) ) info
		$ifd0 = PhotoQExif::_filterUseless($fullexif['IFD0']);
		$subIfd = PhotoQExif::_filterUseless($fullexif['SubIFD']);
		$makerNote = $subIfd['MakerNote'];
		unset($subIfd['MakerNote']);
		$gps = PhotoQExif::_filterUseless($fullexif['GPS']);
		
		//bring all the arrays to single dimension
		$ifd0 = PhotoQHelper::flatten($ifd0);
		$subIfd = PhotoQHelper::flatten($subIfd);
		$makerNote = PhotoQHelper::flatten($makerNote);
		$gps = PhotoQHelper::flatten($gps);
		
		//and finally merge them into a single array
		$exif = array_merge($ifd0, $subIfd, $makerNote, $gps);
		
		
		//update discovered tags
		PhotoQExif::_discoverTags($exif);
		
		
		return $exif;
	}
	
	/**
	 * Creates the formatted exif list. Only tags selected in PhotoQ 
	 * and that are present in the current photo are displayed. 
	 * TagsFromExif are shown as links to the corresponding tag pages.
	 * @param $exif	the full exif data array of this post
	 * @param $tags the exif tags that are selected in photoq
	 * @param $tagsFromExif	the exif tags that were chosen as post_tags via tagFromExif
	 * @return string	formatted exif outpout in form of unordered html list
	 */
	function getFormattedExif($exif, $tags, $tagsFromExif, $displayNames, $displayOptions){
		if(!empty($tags) && !is_array($tags)){
			//is it a comma separated list?
			$tags = array_unique(explode(',',$tags));
		}
		if(!is_array($tags) || count($tags) < 1 ){
			//still nothing?
			$result = '';
		}else{
			$result = $displayOptions['before'];//'<ul class="photoQExifInfo">';
			$foundOne = false; //we don't want to print <ul> if there is no exif in the photo
			foreach($tags as $tag){
				if(array_key_exists($tag, $exif)){
					$foundOne = true;
					if(empty($displayOptions['elementFormatting']))//go with default
						$displayOptions['elementFormatting'] = '<li class="photoQExifInfoItem"><span class="photoQExifTag">[key]:</span> <span class="photoQExifValue">[value]</span></li>';
							
					$displayName = $tag;
					//do we need to display a special name
					if(!empty($displayNames[$tag]))
						$displayName = $displayNames[$tag];
					
					$value = $exif[$tag];
					
					//do we need a tag link?
					if(in_array($tag, $tagsFromExif)){
						//yes, so try to get an id and then the link
						$term = get_term_by('name', $value, 'post_tag');
						if($term)
							$value = '<a href="'.get_tag_link($term->term_id).'">'.$value.'</a>';
					}

					$result .= PhotoQHelper::formatShorttags($displayOptions['elementFormatting'], array('key' => $displayName, 'value' => $value));
					$result .= $displayOptions['elementBetween'];
				}
			}
			//remove last occurrence of elementBetween
			$result = preg_replace('/'.preg_quote($displayOptions['elementBetween']).'$/','',$result);
			$result .= $displayOptions['after'];//'</ul>';
			
			
			if(!$foundOne)
				$result = '';
		}
		return $result;
	}


	function _discoverTags($newTags){
		$oldTags = get_option( "wimpq_exif_tags" );
		if($oldTags !== false){
			$discovered = array_merge($oldTags, $newTags);
			ksort($discovered, SORT_STRING);
			update_option( "wimpq_exif_tags", $discovered);
		}else
			add_option("wimpq_exif_tags", $newTags);
			
	}
	
	/**
	 * Recursively removes entries containing ':unknown' in key from input array.
	 *
	 * @param array $in the input array
	 * @return array	the filtered array
	 */
	function _filterUseless($in){
		$out = array();
		if(is_array($in)){
			foreach ($in as $key => $value){
				if(strpos($key,'unknown:') === false && !in_array($key,PhotoQExif::_getUselessTagNames()))
					if(is_array($value))
						$out[$key] = PhotoQExif::_filterUseless($value);
					else
						$out[$key] = PhotoQExif::_sanitizeExifValue($value);
			}
		}
		return $out;
	}

	/**
	 * This return a list of tags that are either not implemented correctly in exifixer,
	 * that are added by exifixer and not needed or that contain no useful information (e.g. 
	 * only offsets inside the TIFF header or info i deem unlikely to be useful to my users).
	 *
	 * @return unknown
	 */
	function _getUselessTagNames()
	{
		return array(
		'Bytes',
		'CFAPattern',
		'ComponentsConfiguration',	
		'CustomerRender',			
		'ExifInteroperabilityOffset',
		'ExifOffset',
		'GPSInfo',
		'KnownMaker',
		'MakerNoteNumTags',
		'OwnerName',
		'RAWDATA',
		'Unknown',
		'UserCommentOld',
		'VerboseOutput',
		'YCbCrPositioning'
		);
	}
	
	function _sanitizeExifValue($value)
	{
		return preg_replace('#[^(a-zA-Z0-9_\s\.\:\/\,\;\-)]#','',$value);
	}

	
	
}
?>