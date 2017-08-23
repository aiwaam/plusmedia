$(window).load(function() {
	stretch_portal_content($("footer").outerHeight(true));
	$("footer").fadeIn();
});

function stretch_portal_content(footer) {
	if ($(window).height() > $('body').innerHeight()+footer){
		$('#content-area').css({
			"height":"auto",
			"min-height":$(window).height() - ($('body').innerHeight() - $('#content-area').outerHeight(true))-footer
		});
	}
}
function equalHeight(group) {
	var tallest = 0;
	group.each(function() {
		var thisHeight = $(this).height();
		if(thisHeight > tallest) {
			tallest = thisHeight;
		}
	});
	group.height(tallest);
}

