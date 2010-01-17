jQuery(document).ready(function() {
	if (jQuery('#iconator')) jQuery('#sexy-networks').sortable({ 
		delay:        250,
		cursor:      'move',
		scroll:       true,
		revert:       true, 
		opacity:      0.7
	});
	if (jQuery('.sexy-bookmarks')) { jQuery('#sexy-sortables').sortable({ 
		handle:      '.box-mid-head',
		delay:        250,
		cursor:      'move',
		scroll:       true,
		revert:       true, 
		opacity:      0.7
	});

	//Select all icons upon clicking
	jQuery('#sel-all').click(function() {
		jQuery('#sexy-networks').each(function() {
			jQuery('#sexy-networks input').attr('checked', 'checked');
		});
	});

	//Deselect all icons upon clicking
	jQuery('#sel-none').click(function() {
		jQuery('#sexy-networks').each(function() {
			jQuery('#sexy-networks input').removeAttr('checked');
		});
	});

	//Select most popular icons upon clicking
	jQuery('#sel-pop').click(function() {
		jQuery('#sexy-networks').each(function() {
			jQuery('#sexy-digg').attr('checked', 'checked');
			jQuery('#sexy-reddit').attr('checked', 'checked');
			jQuery('#sexy-delicious').attr('checked', 'checked');
			jQuery('#sexy-stumbleupon').attr('checked', 'checked');
			jQuery('#sexy-mixx').attr('checked', 'checked');
			jQuery('#sexy-comfeed').attr('checked', 'checked');
			jQuery('#sexy-twitter').attr('checked', 'checked');
			jQuery('#sexy-technorati').attr('checked', 'checked');
			jQuery('#sexy-misterwong').attr('checked', 'checked');
			jQuery('#sexy-diigo').attr('checked', 'checked');
		});
	});

	/* Select recommended icons upon clicking
	jQuery('#sel-pop').click(function() {
		jQuery('#sexy-networks').each(function() {
			jQuery('#sexy-digg').attr('checked', 'checked');
			jQuery('#sexy-reddit').attr('checked', 'checked');
			jQuery('#sexy-delicious').attr('checked', 'checked');
			jQuery('#sexy-stumbleupon').attr('checked', 'checked');
			jQuery('#sexy-mixx').attr('checked', 'checked');
			jQuery('#sexy-comfeed').attr('checked', 'checked');
			jQuery('#sexy-twitter').attr('checked', 'checked');
			jQuery('#sexy-technorati').attr('checked', 'checked');
			jQuery('#sexy-misterwong').attr('checked', 'checked');
			jQuery('#sexy-???').attr('checked', 'checked');
			jQuery('#sexy-???').attr('checked', 'checked');
			jQuery('#sexy-???').attr('checked', 'checked');
			jQuery('#sexy-???').attr('checked', 'checked');
			jQuery('#sexy-???').attr('checked', 'checked');			
		});
	}); */

	//Swap enabled/disabled between donation options onclick
	jQuery('#preset-amounts').parent('label').click(function() {
		jQuery('#custom-amounts').attr('disabled', 'disabled').css({'cursor':'none'});
		jQuery('#preset-amounts').removeAttr('disabled');
	});

	//Swap enabled/disabled between donation options onclick
	jQuery('#custom-amounts').parent('label').click(function() {
		jQuery('#preset-amounts').attr('disabled', 'disabled').css({'cursor':'none'});
		jQuery('#custom-amounts').removeAttr('disabled');
	});

	// Handle tiny form submission upon selecting option to hide sponsor messages
	jQuery('#hide-sponsors').click(function() {
		jQuery('#no-sponsors').submit();
	});

	// Create a universal click function to close status messages...
	jQuery('.del-x').click(function() {
		jQuery(this).parent('div').parent('div').fadeOut();
	});

	// if checkbox isn't already checked, open warning message...
	jQuery("#custom-mods").click(function() {
		if(jQuery(this).is(":not(:checked)")) {
			jQuery("#custom-mods-notice").css("display", "none");
		}
		else {
			jQuery("#custom-mods-notice").fadeIn("fast");
			jQuery("#custom-mods-notice").css("display", "table");
		}
	});

	// close custom mods warning when they click the X
	jQuery(".custom-mods-notice-close").click(function() {
		jQuery("#custom-mods-notice").fadeOut('fast');
	});

	// Apply "smart options" to BG image
	jQuery('#bgimg-yes').click(function() {
		if(jQuery(this).is(':checked')) {
			jQuery('#bgimgs').fadeIn('slow');
		}
		else {
			jQuery('#bgimgs').css('display', 'none');
		}
	});

	// Apply "smart options" to Yahoo! Buzz
	jQuery('#sexy-yahoobuzz').click(function() {
		if (jQuery(this).attr('checked')) {
			jQuery('#ybuzz-defaults').fadeIn('fast');
		}
		else {
			jQuery('#ybuzz-defaults').fadeOut();
		}
	});

	// Apply "smart options" to Twittley
	jQuery('#sexy-twittley').click(function() {
		if (jQuery(this).attr('checked')) {
			jQuery('#twittley-defaults').fadeIn('fast');
		}
		else {
			jQuery('#twittley-defaults').fadeOut();
		}
	});

	// Apply "smart options" to Twitter
	jQuery('#sexy-twitter').click(function() {
		if (jQuery(this).attr('checked')) {
			jQuery('#twitter-defaults').fadeIn('fast');
		}
		else {
			jQuery('#twitter-defaults').fadeOut();
		}
	});

	jQuery('#shorty').change(function() {
		jQuery('#shortyapimdiv-bitly').fadeOut('fast');
		jQuery('#shortyapimdiv-trim').fadeOut('fast');
		jQuery('#shortyapimdiv-snip').fadeOut('fast');
		jQuery('#shortyapimdiv-tinyarrow').fadeOut('fast');
		jQuery('#shortyapimdiv-cligs').fadeOut('fast');
		jQuery('#shortyapimdiv-supr').fadeOut('fast');
		if(this.value=='trim'){
			jQuery('#shortyapimdiv-trim').fadeIn('fast');
		}
		else if(this.value=='bitly'){
			jQuery('#shortyapimdiv-bitly').fadeIn('fast');
		}
		else if(this.value=='snip'){
			jQuery('#shortyapimdiv-snip').fadeIn('fast');
		}
		else if(this.value=='tinyarrow'){
			jQuery('#shortyapimdiv-tinyarrow').fadeIn('fast');
		}
		else if(this.value=='cligs'){
			jQuery('#shortyapimdiv-cligs').fadeIn('fast');
		}
		else if(this.value=='supr'){
			jQuery('#shortyapimdiv-supr').fadeIn('fast');
		}
	});

	jQuery('#shortyapichk-trim').click(function() {
		if (this.checked) {
			jQuery('#shortyapidiv-trim').fadeIn('fast');
		}
		else {
			jQuery('#shortyapidiv-trim').fadeOut('fast');
		}
	});

	jQuery('#shortyapichk-tinyarrow').click(function() {
		if (this.checked) {
			jQuery('#shortyapidiv-tinyarrow').fadeIn('fast');
		}
		else {
			jQuery('#shortyapidiv-tinyarrow').fadeOut('fast');
		}
	});

	jQuery('#shortyapichk-cligs').click(function() {
		if (this.checked) {
			jQuery('#shortyapidiv-cligs').fadeIn('fast');
		}
		else {
			jQuery('#shortyapidiv-cligs').fadeOut('fast');
		}
	});

	jQuery('#shortyapichk-supr').click(function() {
		if (this.checked) {
			jQuery('#shortyapidiv-supr').fadeIn('fast');
		}
		else {
			jQuery('#shortyapidiv-supr').fadeOut('fast');
		}
	});

	// Fade in/out mobile feature warning
	jQuery('#mobile-hide').click(function() {
		if (this.checked) {
			jQuery('#mobile-warn').fadeIn('fast');
		}
		else {
			jQuery('#mobile-warn').fadeOut();
		}
	});

	jQuery('#position-above').click(function() {
		if (jQuery('#info-manual').is(':visible')) {
			jQuery('#info-manual').fadeOut();
		}
	});

	jQuery('#position-below').click(function() {
		if (jQuery('#info-manual').is(':visible')) {
			jQuery('#info-manual').fadeOut();
		}
	});

	jQuery('#position-manual').click(function() {
		if (jQuery('#info-manual').is(':not(:visible)')) {
			jQuery('#info-manual').fadeIn('slow');
		}
	});

	jQuery('.dtags-info').click(function() {
		jQuery('#tag-info').fadeIn('fast');
	});

	jQuery('.dtags-close').click(function() {
		jQuery('#tag-info').fadeOut();
	});

	jQuery('.shebang-info').click(function() {
		jQuery('#info-manual').fadeIn('fast');
	});

	jQuery('.boxcloser').click(function() {
		jQuery('.sexy-donation-box').slideUp('slow');
	});

	jQuery('#clearShortUrls').click(function() {
		if (jQuery('#clearShortUrls').is(':checked')) {
			this.checked=jQuery('#clear-warning').fadeIn('fast');
		}else{
			this.checked=jQuery(this).is(':not(:checked)');
		}
		this.checked=jQuery(this).is(':not(:checked)');
	});

	jQuery('#warn-cancel').click(function() {
		this.checked=jQuery('#clear-warning').fadeOut();
		this.checked=jQuery(this).is(':not(:checked)');
	});

	jQuery('#warn-yes').click(function() {
		this.checked=jQuery('#clear-warning').fadeOut();
		this.checked=jQuery('#clearShortUrls').attr('checked', 'checked');
		this.checked=!this.checked;
	});
}});