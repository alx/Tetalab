jQuery(document).ready(function($) {
	$('#multi-sidebar').tabs();
	
	$('#commentform').validate();
	$('.featured').hover( 
		function() {
			$('#featured-slideshow').cycle('pause');
			$('#controls').fadeIn();
		}, 
		function() {
			$('#featured-slideshow').cycle('resume');
			$('#controls').fadeOut();
		}
	);
	$('#featured-slideshow').cycle({
		fx: 'fade',
		speed: 250,
		next: '#controls .next',
		prev: '#controls .prev',
		timeout: 6000
	});
	
	$('#show_projects').click(function(){
		if($("#more_projects").is(':visible')){
			$("#more_projects").hide("slide", { direction: "down" }, 1000);
		} else {
			$("#more_projects").show("slide", { direction: "down" }, 1000);
		}
		
	});
});