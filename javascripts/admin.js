// JavaScript Document
jQuery(function($) {
	$('#waraPay_add_product').bind('click', function() {
		location.href = $(this).attr('action-href');
	});


	$('.waraPay_prolink').bind('click', function() {
		//location.href = $(this).val();
		window.open($(this).val(), '_blank')
	});


});