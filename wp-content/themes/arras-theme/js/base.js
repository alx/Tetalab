var j = jQuery.noConflict();

j(document).ready(function() {
	j('#show_projects').click(function(){
		if(j("#more_projects").is(':visible')){
			j("#more_projects").hide("slide", { direction: "down" }, 1000);
		} else {
			j("#more_projects").show("slide", { direction: "down" }, 1000);
		}
		
	});
});