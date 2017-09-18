var $wsAliWidgetArr = [];


jQuery(function($) {

	$('.waraPay_widget_wrap').mouseenter(function() {
		$(this).children('.waraPay_widget_form').css('display', 'block');
	});

	$('.waraPay_widget_wrap').mouseleave(function() {
		$(this).children('.waraPay_widget_form').css('display', 'none');
	});

	$('.waraPay_widget_try').click(function() {
		alert('YOU GOT IT');
	});

});//EOJQ