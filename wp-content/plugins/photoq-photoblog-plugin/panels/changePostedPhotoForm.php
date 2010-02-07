<div id="photoQPhotoBox" class="postbox">
	<h3 class='hndle'><span><?php _e('PhotoQ Photo', 'PhotoQ'); ?></span></h3>
	<div class="inside"><p>
		<img src="<?php echo 
					$photo->getAdminThumbURL(
						$this->_oc->getValue('editPostThumbs-Width'),
						$this->_oc->getValue('editPostThumbs-Height')
					); 
				?>" alt="<?php echo $photo->getTitle(); ?>" /></p>
		<?php 
			// Use nonce for verification
			if ( function_exists('wp_nonce_field') )
				wp_nonce_field('photoqEditPost'.$post->ID, 'photoqEditPostFormNonce');
			
			//this hidden field is needed as a flag to tell the filter hook that reformats
			//editor content that we actually return from the editor and didn't do e.g. a
			//quick update of a batch update where the_content would not contain the description
			//only.
		?>
		<input type="hidden" name="saveAfterEdit" value="1">
		<p><?php _e('To change the current photo of this post, select a new one here and update the post.', 'PhotoQ'); ?></p>
		<p><input type="file" class="button-secondary action" name="Filedata" id="Filedata" size="55"/></p>
	</div>
</div>


<script type="text/javascript">
/* <![CDATA[ */

		function enhanceForm() {

			// Mutate the form to a fileupload form
			// As usual: Special code for IE
			if (jQuery.browser.msie) jQuery('#post').attr('encoding', 'multipart/form-data');
			else jQuery('#post').attr('enctype', 'multipart/form-data');

			// Ensure proper encoding
			jQuery('#post').attr('acceptCharset', 'UTF-8');

			// Insert the fileupload field
			jQuery('#photoQPhotoBox').insertAfter('#titlediv');

		}

		/* 
			
			We call the function right now, because wordpress already 
			generated all we need for this. We could also plug this in 
			as onLoad method via jQuery:
			
			$(document).ready(
				function() { 
					enhanceForm(); 
				}
			);

			But that's a little bit slow since the form addition
			shows after the completion of page loading

		*/

		enhanceForm();

	/* ]]> */
</script>