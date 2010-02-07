<div class="wrap">

<h2><?php _e('PhotoQ Options', 'PhotoQ'); ?></h2>
<div id="poststuff">

<div  id="autoconf" class="postbox closed">
	<h3 class="postbox-handle"><span><?php _e('Auto Configuration', 'PhotoQ') ?></span></h3>
	<div class="inside">
		<form action="options-general.php?page=whoismanu-photoq.php" method="post">
		<?php wp_nonce_field('photoqImportXML-nonce', 'photoqImportXML-nonce'); ?>
		<h4><?php echo __('Users - Load Theme Preset: ', 'PhotoQ'); ?></h4>
		<p><?php $this->_oc->renderListOfPresets(); ?>
		<span class="submit">
		<input type="submit" name="submit" class="button-primary"
			value="<?php esc_attr_e('Load Theme Preset', 'PhotoQ'); ?>" 
			onclick="return confirm('<?php 
				_e('Are you sure? This will override some of your current settings. Further, all of your published posts will be rebuilt, which might take a while.', 'PhotoQ'); 
			?>');"
		/></span>
					
		<input type="hidden" name="importXML" value="true" />
		</p>
	</form>
	<?php if(!PhotoQHelper::isWPMU() || is_site_admin()): ?>
	<br/>
	<form action="../wp-content/plugins/photoq-photoblog-plugin/whoismanu-photoq-xml-export.php" method="get">
	<?php wp_nonce_field('photoqExportXML-nonce', 'photoqExportXML-nonce'); ?>
		
		<h4><?php _e('Theme Authors - Create Theme Preset: ', 'PhotoQ'); ?></h4>
		<p>
			<?php _e("Want your theme included in above list? All you need to do is to create a preset using the form below and make it available to the users of your theme. If you want me to include your preset as one of PhotoQ's defaults, please additionally send me a pointer to the preset you created.",'PhotoQ');
				echo '</p><p>'; 
				_e('All of the following information is optional. However, the more complete the info is, the better. Also, more accurate info increases the chances that I will include your preset as a default.', 'PhotoQ'); 
			?>
		</p>
		<table class="form-table noborder save-preset">
			<tr valign="top">
				<td><?php _e('Filename','PhotoQ') ?>:</td>
				<td><input type="text" name="xml-filename" id="xml-filename" size="40"
					maxlength="100" value="" />.xml</td>
			</tr>
			<tr valign="top">
				<td><?php _e('Theme Name','PhotoQ') ?>:</td>
				<td><input type="text" name="xml-themename" id="xml-themename" size="40"
					maxlength="100" value="" />, <?php _e('Version','PhotoQ') ?>: <input type="text" name="xml-themeversion" id="xml-themeversion" size="10"
					maxlength="20" value="" /></td>
			</tr>
			<tr valign="top">
				<td><?php _e('Theme Category','PhotoQ') ?>:</td>
				<td>
					<select name="xml-themecategory" id="xml-themecategory">
						<?php foreach ($this->_oc->getPresetCategories() as $key => $val){
							echo '<option value="'.$key.'">'.$val.'</option>';	
						}?>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<td><?php _e('Theme URL','PhotoQ') ?>:</td>
				<td><input type="text" name="xml-themeurl" id="xml-themeurl" size="61"
					maxlength="100" value="" /></td>
			</tr>
			<tr valign="top">
				<td><?php _e('Theme Author Name','PhotoQ') ?>:</td>
				<td><input type="text" name="xml-themeauthorname" id="xml-themeauthorname" size="61"
					maxlength="100" value="" /></td>
			</tr>
			<tr valign="top">
				<td><?php _e('Theme Author Contact Info (URL/Email)','PhotoQ') ?>:</td>
				<td><input type="text" name="xml-themeauthorcontact" id="xml-themeauthorcontact" size="61"
					maxlength="100" value="" /></td>
			</tr>
			<tr valign="top">
				<td><?php _e('Your Name','PhotoQ') ?>:</td>
				<td><input type="text" name="xml-creatorname" id="xml-creatorname" size="61"
					maxlength="100" value="" /></td>
			</tr>
			<tr valign="top">
				<td><?php _e('Your Contact Info (URL/Email)','PhotoQ') ?>:</td>
				<td><input type="text" name="xml-creatorcontact" id="xml-creatorcontact" size="61"
					maxlength="100" value="" /></td>
			</tr>
			<tr valign="top">
				<td><label for="xml-defaultTags"> <input
					id="xml-defaultTags" type="checkbox" name="xml-defaultTags" /><?php _e('Include default Tags','PhotoQ') ?>. </label></td>
				<td><label for="xml-defaultCats"> <input
					id="xml-defaultCats" type="checkbox" name="xml-defaultCats" /><?php _e('Include default Category','PhotoQ') ?>. </label></td>
			</tr>
			
		</table>
		<p class="submit">
			<input type="submit" name="submit" class="button" value="<?php esc_attr_e('Create Theme Preset', 'PhotoQ'); ?>" /> 
			<input type="hidden" name="download" value="true" />
		</p>
	</form>
	<?php endif; //!PhotoQHelper::isWPMU()?>
	</div>
</div>

<form method="post" action="options-general.php?page=whoismanu-photoq.php">
		
		
		
		<p class="submit top-savebtn">
			<?php $this->_oc->renderSaveButton(__('Save Changes', 'PhotoQ')); ?>
		</p>
			
			
			
			<div  class="postbox ">
			<h3 class="postbox-handle"><span><?php _e('Image sizes', 'PhotoQ') ?></span></h3>
			<div class="inside">
			
			
			<?php $this->_oc->render('imageSizes');?>
			
			<!--<table width="100%" cellspacing="2" cellpadding="5" class="form-table noborder">
			<tr valign="top">
					<th scope="row">
						<label for="newImageSizeName"><?php _e('Name of new image size', 'PhotoQ'); ?>:</label>
					</th>
					<td>
			<input type="text" name="newImageSizeName" id="newImageSizeName"
					size="20" maxlength="20" value="" />
			<input type="submit" class="button-secondary"
					name="addImageSize"
					value="<?php _e('Add Image Size', 'PhotoQ') ?> &raquo;" />
			
					</td>
			</tr>
			</table> -->
			<table width="100%" cellspacing="2" cellpadding="5" class="form-table noborder">
			<tr valign="top">
				<th scope="row"><?php _e('Hide \'original\' folder', 'PhotoQ'); ?>:</th>
				<td>
				<?php 
					$this->_oc->render('originalFolder');
					
					$folderName = get_option('wimpq_originalFolder');
					$folderName = $folderName ? $folderName : 'original';
					echo '<br/>('.__('Current name', 'PhotoQ').': '.$folderName.')';
				?></td>
			</tr>
			</table>
			</div>
			</div>
			
			<div  class="postbox ">
			<h3 class="postbox-handle"><span><?php _e('Views', 'PhotoQ') ?></span></h3>
			<div class="inside">
				<?php $this->_oc->render('views'); ?>
			
			<?php if(false):?>
			<table width="100%" cellspacing="2" cellpadding="5" class="form-table noborder">
				<tr valign="top">
					<th>the_content</th>
					<td></td>
				</tr>
				<?php $this->_oc->render('contentView');?>
			</table>	
			<table width="100%" cellspacing="2" cellpadding="5" class="form-table noborder">
				<tr valign="top">
					<th>the_excerpt</th>
					<td></td>
				</tr>
				<?php $this->_oc->render('excerptView');?>
			</table>
			<?php endif; ?>
			</div>
			</div>
			
			
			<div  class="postbox closed">
			<h3 class="postbox-handle"><span><?php _e('Exif', 'PhotoQ') ?></span></h3>
			<div class="inside">
			<table width="100%" cellspacing="2" cellpadding="5" class="form-table">
				
				<?php 
				
				$exifDisplayOptions = array(
					'exifDisplay' => __('Exif Formatting Options:', 'PhotoQ')
				); 
				$this->_oc->showOptionArray($exifDisplayOptions);
				
				?>
				
				<tr valign="top">
					<th scope="row"><?php _e('Choose Exif Tags', 'PhotoQ'); ?>:
						<br/><br/><span class="setting-description"><?php _e('You can select/deselect EXIF tags via drag-and-drop between the two lists.<br/>Within the list of selected tags you can also change the order via drag-and-drop.', 'PhotoQ') ?></span>
					</th>
					<td>
						<?php 
							if(!get_option( "wimpq_exif_tags" )) 
								_e('No tags yet. PhotoQ will learn exif tags from uploaded photos. Upload a photo first, then come back and choose your exif tags here.', 'PhotoQ');
							else
								$this->_oc->render('exifTags');
						?>
					</td>
				</tr>
			</table>
			</div>
			</div>
			
			
			<div  class="postbox closed">
			<h3 class="postbox-handle"><span><?php _e('Watermarking', 'PhotoQ') ?></span></h3>
			<div class="inside">
			<table width="100%" cellspacing="2" cellpadding="5" class="form-table noborder">
			<tr valign="top">
					<th scope="row"><?php _e('Watermark Image', 'PhotoQ') ?>:</th>
					<td>
					<?php $this->showCurrentWatermark(); ?>
			
			<input style="vertical-align: top;" type="submit" class="button-secondary"
					name="showWatermarkUploadPanel"
					value="<?php _e('Change Watermark', 'PhotoQ') ?> &raquo;" />
					</td>
			</tr>
			
			<?php $this->_oc->render('watermarkOptions');?>
			
			</table>
			</div>
			</div>
			
			
			
			<div  class="postbox closed">
			<h3 class="postbox-handle"><span><?php _e('Meta Fields', 'PhotoQ') ?></span></h3>
			<div class="inside">
			<table width="100%" cellspacing="2" cellpadding="5" class="form-table">
				<?php 
				
				$metaFieldOptions = array(
					'fieldAddPosted' => __('Upon Add:', 'PhotoQ'),
					'fieldDeletePosted' => __('Upon Delete:', 'PhotoQ'),
					'fieldRenamePosted' => __('Upon Rename:', 'PhotoQ')
				); 
				
				
				$this->_oc->showOptionArray($metaFieldOptions);
				
				
				?>
				
				<tr valign="top">
					<th><?php _e('Defined Fields:', 'PhotoQ'); ?></th>
					<td>
						<table width="200" cellspacing="2" cellpadding="5"
							class="meta_fields noborder">

							<?php
								$this->showMetaFields();				
							?>
				
						</table>
					</td>
				</tr>	
			</table>
			<table width="100%" cellspacing="2" cellpadding="5" class="form-table noborder">
				<tr valign="top">
					<th scope="row">
						<label for="newFieldName"><?php _e('Name of new field', 'PhotoQ'); ?>:</label>
					</th>
					<td>
						<input type="text" name="newFieldName" id="newFieldName"
								size="20" maxlength="20" value="" />
						<input type="submit" class="button-secondary"
								name="addField"
								value="<?php _e('Add Meta Field', 'PhotoQ') ?> &raquo;" />
					</td>
				</tr>
			</table>
			</div>
			</div>
		
			<div  class="postbox closed">
			<h3 class="postbox-handle"><span><?php _e('Further Options', 'PhotoQ') ?></span></h3>
			<div class="inside">
			<table width="100%" cellspacing="2" cellpadding="5" class="form-table">
				
				<?php 
				
				
				$furtherOptions = array(
					'postMulti' => __('Second Post Button:', 'PhotoQ'),
					'cronJobs' => __('Automatic Posting:', 'PhotoQ'),
					'qPostStatus' => __('PhotoQ Post Status:', 'PhotoQ'),
					'qPostDefaultCat' => __('PhotoQ Default Category:', 'PhotoQ'),
					'qPostAuthor' => __('PhotoQ Default Author:', 'PhotoQ'),
					'qPostDefaultTags' => __('PhotoQ Default Tags:', 'PhotoQ'),
					'autoTitles' => __('Auto Titles:', 'PhotoQ'),
					'descrFromExif' => __('Auto Description:', 'PhotoQ'),
					'specialCaps' => __('Roles/Capabilities:', 'PhotoQ'),
					'showThumbs' => __('Admin Thumbs:', 'PhotoQ'),
					'foldCats' => __('Fold Categories:', 'PhotoQ'),
					'deleteImgs' => __('Deleting Posts:', 'PhotoQ'),
					'enableBatchUploads' => __('Batch Uploads:', 'PhotoQ')
				); 
				
				
				if(!PhotoQHelper::isWPMU()){//WPMU version has no imgdir and ftp setting
					$furtherOptions = array_merge(array(
							'imgdir' => __('Image Directory:', 'PhotoQ'),
							'enableFtpUploads' => __('FTP Upload:', 'PhotoQ'),
							'imagemagickPath' => __('ImageMagick Path:', 'PhotoQ')
						), $furtherOptions
					);					
				}elseif(is_site_admin()){//in case of WPMU IMPath is a sitewide setting only accessible to site_admins
					$furtherOptions = array_merge(array(
							'imagemagickPath' => __('ImageMagick Path:', 'PhotoQ') . '<br/><b>' . __('(Sitewide Setting)', 'PhotoQ') . '</b>'
						), $furtherOptions
					);
				}
				
				$this->_oc->showOptionArray($furtherOptions);
				
				
				?>
				
			</table>
			</div>
			</div>
			
			<div  class="postbox closed">
			<h3 class="postbox-handle"><span><?php _e('Maintenance', 'PhotoQ') ?></span></h3>
			<div class="inside">
			<table width="100%" cellspacing="2" cellpadding="5" class="form-table">
				
				<?php if(!PhotoQHelper::isWPMU()): ?>
				<tr valign="top">
					<th scope="row"><?php _e('Upgrade:','PhotoQ') ?></th>
					<td><input style="vertical-align: top;" type="submit" class="button-secondary"
					name="showUpgradePanel"
					value="<?php _e('Upgrade from PhotoQ < 1.5', 'PhotoQ') ?> &raquo;" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Fix Permissions:','PhotoQ') ?></th>
					<td><input style="vertical-align: top;" type="submit" class="button-secondary"
					name="fixPermissions"
					value="<?php _e('Fix File and Folder Permissions', 'PhotoQ') ?>" /></td>
				</tr>
				<?php endif; ?>
				
				<tr valign="top">
					<th scope="row"><?php _e('Rebuild Published:','PhotoQ') ?></th>
					<td><input style="vertical-align: top;" type="submit" class="button-secondary"
					name="rebuildAll"
					value="<?php _e('Rebuild All Published Photos', 'PhotoQ') ?>" 
					onclick="return confirm(
						'<?php _e('Are you sure? This will rebuild all published photos recreating all the thumbs. It might thus take a while.', 'PhotoQ'); ?>');"/>
					</td>
				</tr>
				
			</table>
			</div>
			</div>
		
		
		<p class="submit">
			<?php $this->_oc->renderSaveButton(__('Save Changes', 'PhotoQ'), true); ?>
		</p>
		
	</form>
	</div>
</div> 