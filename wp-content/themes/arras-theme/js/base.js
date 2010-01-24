var j = jQuery.noConflict();

j(document).ready(function() {
	j('#show_projects').click(function(){
		
		var img = j('#show_projects img').attr('src');
		
		if(j("#more_projects").is(':visible')){
			j("#more_projects").hide("slide", { direction: "up" }, 1000);
			j('#show_projects img').attr('src', img.replace("up", "down"));
		} else {
			j("#more_projects").show("slide", { direction: "down" }, 1000);
			j('#show_projects img').attr('src', img.replace("down", "up"));
		}
		
	});
});