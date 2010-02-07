jQuery(document).ready(
	function () {
		if(ajaxQueueL10n.allowReorder){
			jQuery('#photoq').sortable(
				{
					axis: 			'y',
					containment:	'parent',
					stop :			listReordered,
					opacity: 0.5,
					tolerance: 'pointer',
					cursor: 'move'
				}
			);
		}
	}
);





function listReordered(e, ui){
	var reorderedEntries = jQuery('#photoq').sortable('serialize',{expression: "(.+?)[-](.+)"});
	//update the position labels
	jQuery('.qPosition').each(function(i) {
     	jQuery(this).text(i+1);
   	});
	
	//send the request to the server, no need to send cookie along: cookie="+encodeURIComponent(document.cookie)+"&
	jQuery.ajax( 
    { 
        type: "POST", 
        url: ajaxUrl, 
        data: "queueReorderNonce="+jQuery('#queueReorderNonce').val()+"&action=reorder&" + reorderedEntries, 
        success: 
            function(t) 
            { 
                //alert('success'); 
            }, 
        error: 
            function() 
            { 
                alert('error'); 
            } 
    }); 
	
}



function editQEntry(imgID){
	//disable reordering, editing, deleting
	jQuery('#photoq').sortable('disable');
	jQuery('.photoqEntry').css('cursor', 'default');
    jQuery('.qEdit').css('display','none');
    jQuery('.qDelete').css('display','none');
    jQuery('.tablenav .button-secondary').css('display','none');
    jQuery('#photoq-'+imgID).html('<div id="serverresponse"></div>');
    //get data from server
    jQuery("#serverresponse").load(ajaxUrl, {
        action: "edit",
        id: imgID
    },
    function(){
   		addPostBoxToggle(this);
 	});
    
    
    
    //stop normal click of link from happening
    return false;
}