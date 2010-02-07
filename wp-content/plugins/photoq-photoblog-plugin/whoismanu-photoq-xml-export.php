<?php

/**
 * This file generates the XML options that the user can download.
 * Based on WordPress Export Administration API
 *
 * @package PhotoQ
 */

//if we don't define this switch, PhotoQ doesn't run
define('EXPORTING_PHOTOQ_XML', true);

//Load WordPress Bootstrap
require_once( 'whoismanu-photoq-wploader.php' );

//next are some access and nonce checks
if ( !is_user_logged_in() )
	die('-1');
	
check_admin_referer('photoqExportXML-nonce', 'photoqExportXML-nonce');

//Do we come from the form
if ( isset( $_GET['download'] ) ) {
	
	// setting up options
	$oc =& PhotoQSingleton::getInstance('PhotoQOptionController');
	// get the database access object
	$db =& PhotoQSingleton::getInstance('PhotoQDB');
	
	//create the filename
	if(!empty($_GET['xml-filename']))
		$filename = attribute_escape($_GET['xml-filename']).'.xml';
	else
		$filename = 'my-theme-preset.' . date('Y-m-d') . '.xml';

	//send the proper headers
	header('Content-Description: File Transfer');
	header("Content-Disposition: attachment; filename=$filename");
	header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
	
	
	echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?' . ">\n";

?>

<!-- generator="PhotoQ/<?php echo $photoq->getVersion() ?>" created="<?php echo date('Y-m-d H:i') ?>"-->

<photoQSave version="1.0">
<photoQSaveMeta>
	<generator>http://www.whoismanu.com/photoq-wordpress-photoblog-plugin/?v=<?php echo $photoq->getVersion() ?></generator>
<?php if(!empty($_GET['xml-themename']) || !empty($_GET['xml-themeversion']) || !empty($_GET['xml-themecategory']) || !empty($_GET['xml-themeurl']) || !empty($_GET['xml-themeauthorname']) || !empty($_GET['xml-themeauthorcontact']) ):?>
	<theme>
<?php if(!empty($_GET['xml-themename'])):?>
		<name><?php echo attribute_escape($_GET['xml-themename']) ?></name>
<?php endif; ?>
<?php if(!empty($_GET['xml-themeversion'])):?>
		<version><?php echo attribute_escape($_GET['xml-themeversion']) ?></version>
<?php endif; ?>
<?php if(!empty($_GET['xml-themecategory'])):?>
		<category><?php echo attribute_escape($_GET['xml-themecategory']) ?></category>
<?php endif; ?>
<?php if(!empty($_GET['xml-themeurl'])):?>
		<url><?php echo attribute_escape($_GET['xml-themeurl']) ?></url>
<?php endif; ?>
<?php if( !empty($_GET['xml-themeauthorname']) || !empty($_GET['xml-themeauthorcontact']) ):?>
		<author>
<?php if( !empty($_GET['xml-themeauthorname']) ):?>
			<name><?php echo attribute_escape($_GET['xml-themeauthorname']) ?></name>
<?php endif; ?>
<?php if( !empty($_GET['xml-themeauthorcontact']) ):?>
			<contact><?php echo attribute_escape($_GET['xml-themeauthorcontact']) ?></contact>
<?php endif; ?>
		</author>
<?php endif; ?>
	</theme>
<?php endif; ?>
<?php if( !empty($_GET['xml-creatorname']) || !empty($_GET['xml-creatorcontact']) ):?>
	<creator>
<?php if( !empty($_GET['xml-creatorname']) ):?>
		<name><?php echo attribute_escape($_GET['xml-creatorname']) ?></name>
<?php endif; ?>
<?php if( !empty($_GET['xml-creatorcontact']) ):?>
		<contact><?php echo attribute_escape($_GET['xml-creatorcontact']) ?></contact>
<?php endif; ?>
	</creator>
<?php endif; ?>
</photoQSaveMeta>
<photoQSettings>
	<?php
		//add an entry for every meta field 
		$fieldNames = $db->getFieldNames();
		if(count($fieldNames)){
			print '<photoQFields>'. PHP_EOL;
			foreach($fieldNames as $fieldName)
				print '<field><name>'.$fieldName.'</name></field>'. PHP_EOL;
			print '</photoQFields>'. PHP_EOL;
		}
		if(isset($_GET['xml-defaultCats'])){
			print '<photoQDefaultCategories>'. PHP_EOL;
			print '<category><name>'.get_the_category_by_ID($photoq->_oc->getValue('qPostDefaultCat')).'</name></category>'. PHP_EOL;
			print '</photoQDefaultCategories>'. PHP_EOL;
		}
		
		
		//what options do we include into this preset
		$includedOptions = array('imageSizes', 'views', 'exifDisplay');
		if(isset($_GET['xml-defaultTags']))
			array_push($includedOptions, 'qPostDefaultTags');
			
		//add an entry for every included option
		$oc->serizalize2xml($includedOptions); 
	?>
</photoQSettings>
</photoQSave>
<?php
}// end: if ( isset( $_GET['download'] ) ) {
?>