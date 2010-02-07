/* thanks to Roger Theriault, http://wordpress.org/support/topic/185897 */
jQuery(document).ready( function() {
    addPostBoxToggle();
});


function addPostBoxToggle(context) {
	
	jQuery('.postbox h3', context).click( function() {
        jQuery(jQuery(this).parent().get(0)).toggleClass('closed');
    });
	
};