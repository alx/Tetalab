/**
 * Defines the sortable list. Whenever it is sorted, the resulting ordering of the li element ids
 * is stored in a field with id equal to the ul plus suffix "Ordering".
 */
jQuery(document).ready(function()
{
	//Make all reorderable lists sortable
	jQuery("ul.reorderable").sortable({ 
		items: 'li', 
		//containment: 'parent', 
		//axis: 'y', 
		opacity: 0.5,
		tolerance: 'pointer',
		cursor: 'move',
		dropOnEmpty: true,
		stop: function(event, ui){
			//ordering changed -> update corresponding Ordering field
			var listID = ui.item.parents().attr('id');
			var senderListID = this.id;
			updateOrderField(listID);
		} 
	});
	
	//Now loop through all reorderable lists and add their status to the corresponding Ordering field
	jQuery("ul.reorderable").each(function(){
		var selected = jQuery(this).sortable('toArray');
		for(i=0; i<selected.length; i++)
			selected[i] = selected[i].replace('_', ' ');
		jQuery("#"+this.id+"Ordering").val(selected);
	});
	
	//Link the lists together
	jQuery("ul.selectedOptions").each(function(){
		jQuery("#"+this.id+"All").sortable('option', 'connectWith', "#"+this.id);
		jQuery("#"+this.id).sortable('option', 'connectWith', "#"+this.id+"All");
	});
	
	//add mouse enter/leave events
	jQuery("ul.reorderable li").hover(
			function () {
				jQuery(this).css({'background-color' : '#EAF2FA'});
			}, 
			function () {
				jQuery(this).css({'background-color' : 'white'});
			}
	);
	
	//add a switch list link
	jQuery("ul.reorderable li").prepend('<a class="switchLink" href="javascript:void(0)">' + reorderOptionL10n.switchLinkLabel +'</a>');
	jQuery(".switchLink").bind("click", function(){
		  var parentListId = jQuery(this).parents("ul.reorderable").attr('id');
		  var otherListId;
	      if(parentListId.indexOf('All') == parentListId.length-3)
	    	  otherListId = parentListId.substring(0,parentListId.length-3);
	      else
	    	  otherListId = parentListId + 'All';
	      jQuery(this).parent().appendTo(jQuery("#"+otherListId));
	      updateOrderField(parentListId);
	});
	
	
});


function updateOrderField(listID){
	//get the id w/o the All suffix
	if(listID.indexOf('All') == listID.length-3)
		listID = listID.substring(0,listID.length-3);
    //update the hidden field
	var selected = jQuery("#"+listID).sortable('toArray');
	for(i=0; i<selected.length; i++)
		selected[i] = selected[i].replace('_', ' ');
	jQuery("#"+listID+"Ordering").val(selected);
}