var $wsAliFrontArr = [];
$wsAliFrontArr['payto_path'] = '/wp-content/plugins/waraPay/includes/tpl.cart.php?';
//$wsAliFrontArr['arr_query']  = ['proid','email','num','msg','extra','addr','tel','ordname','postcode','nonce'];
//$wsAliFrontArr['arr_query'] = '';
$wsAliFrontArr['prefix'] = '.waraPay_buy_';
//$wsAliFrontArr['p'] = '';

jQuery(function($) {

	$('.waraPay_buy_wrap .waraPay_buy_pay').click(function() {


		$wsAliFrontArr['p'] = $(this).parents('.waraPay_buy_wrap');

		$wsAliFrontArr['arr_query'] = $wsAliFrontArr['p'].find('.waraPay_buy_fields').val().split(',');

		$PARA = waraPay_http_build_query($wsAliFrontArr['arr_query']);

		var $PROTO = window.location.protocol + '//';
		var $HOST = window.location.host;
		var $PORT = window.location.port;
		var $PATH = $wsAliFrontArr['payto_path'];
		var $URI = $PROTO + $HOST + $PORT + $PATH + $PARA;

		$URI = encodeURI($URI);
		//window.location.href = $URI ;

		open($URI);
	});


});//EOJQ


function waraPay_http_build_query($query_fields) {
	var ret = '';
	var $p = $wsAliFrontArr['p'];
	var $prefix = $wsAliFrontArr['prefix'];

	for (var i in $query_fields) {
		fiels_val = $p.find($prefix + $query_fields[i]).val();
		fiels_val = waraPay__E28(fiels_val);
		ret += '&' + $query_fields[i] + '=' + fiels_val;
	}

	var referer = waraPay__E28(window.location.href);
	ret = 'referer=' + referer + ret;
	return ret;
}

function waraPay__E28(o) {
	if (typeof o == 'undefined') {
		o = ''
	}
	return  encodeURIComponent(o);
}
