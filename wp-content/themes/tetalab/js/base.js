var j = jQuery.noConflict();

j(document).ready(function() {
	j('#more_projects_tab').click(function(){
		
		var img = j('#more_projects_tab img').attr('src');
		
		if(img.search(/up/) != -1){
			j("#more_projects").animate({top: "-=300px"}, 300);
			j('#more_projects_tab img').attr('src', img.replace("up", "down"));
		} else {
			j("#more_projects").animate({top: "+=300px"}, 300);
			j('#more_projects_tab img').attr('src', img.replace("down", "up"));
		}
		
	});
});