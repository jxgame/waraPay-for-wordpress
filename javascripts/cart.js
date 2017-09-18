jQuery(function($) {




	$info = [];

	initInfo();
	

	
	$("input").each(function() {
		$(this).bind('blur', function() {
			if (!in_array($(this).attr('name'), $arr_valiFields)) {
				$(this).next().next().css('display', 'none');
				$(this).removeClass('error');

				return;

			}
			if (!waraPay_valiFormat($(this).val(), $(this).attr('name'))) {

				$(this).next().next().css('display', 'block');
				$(this).addClass('error');
			} else {
				$(this).next().next().css('display', 'none');
				$(this).removeClass('error');
			}

		});
	})


	$('input[name=paygate]').bind('click', function() {

		if ($(this).val().toUpperCase() !== 'UNION') {
			$('#bankList').hide();
		} else {
			$('#bankList').show();
		}
	});

	$('#proNumDecre').bind('click', function() {
//	var num = parseInt($('#cartProNum').text());
//	var price = parseFloat($('#cartProPrice').text())
//	var logFee = parseFloat($('#cartLogFee').text());
//	
//	if( num > 1){
//		
//		$('#cartProNum').text(num - 1);
//		$('#ordInfo_num').val(num - 1);	
//		
//		var cnum = parseInt($('#cartProNum').text());
//		$('#cartProFee').text( (price * cnum).toFixed(2) );
//		$('#cartTotFee').text( (price * cnum +logFee).toFixed(2));
//	}


		if ($info['num'] < 2) {
			return
		}
		;
		$info['num']--;
		updateInfo();

	});

	$('#proNumIncre').bind('click', function() {
//	var num 	= parseInt($('#cartProNum').text());
//	var price   = parseFloat($('#cartProPrice').text())
//	var logFee  = parseFloat($('#cartLogFee').text())
//	
//	$('#cartProNum').text(num + 1);
//	$('#ordInfo_num').val(num + 1);		
//	
//	var cnum = parseInt($('#cartProNum').text());
//	$('#cartProFee').text( (price * cnum).toFixed(2) );
//	$('#cartTotFee').text( (price * cnum + logFee).toFixed(2));
//if( $info['num'] < 2 ) {return};
		$info['num']++;
		updateInfo();

	});

	$('#cartPriceUnit').bind('change', function() {
//	var num 	= parseInt($('#cartProNum').text());
//	var price   = parseFloat($('#cartProPrice').text())
//	var logFee  = parseFloat($('#cartLogFee').text())
//	
//	$('#cartProNum').text(num + 1);
//	$('#ordInfo_num').val(num + 1);		
//	
//	var cnum = parseInt($('#cartProNum').text());
//	$('#cartProFee').text( (price * cnum).toFixed(2) );
//	$('#cartTotFee').text( (price * cnum + logFee).toFixed(2));
//if( $info['num'] < 2 ) {return};

		$uvp = $(this).val();

		$info['unit'] = $uvp.split('-')[0];
		$info['price'] = $uvp.split('-')[1];
		updateInfo();

	});


	$('#payInfo>ul li').bind({
		'click': function() {
			$radio = $(this).children('input');
			$radio.attr('checked', 'true');

			if ($radio.val().toUpperCase() !== 'UNION') {
				$('#bankList').hide();
			} else {
				$('#bankList').show();
			}
		},
		'mouseenter': function() {
			$(this).css('border', 'solid 1px #999');
		},
		'mouseleave': function() {
			$(this).css('border', 'solid 1px #FFF');
		}
	});

	$('#bankList>ul li').bind({
		'click': function() {
			$radio = $(this).children('input');
			$radio.attr('checked', 'true');
		},
		'mouseenter': function() {
			$(this).css('border', 'solid 1px #999');
		},
		'mouseleave': function() {
			$(this).css('border', 'solid 1px #FFF');
		}
	});


});//END OF JQUERY

function makeMformat(n) {
	return Math.round(parseFloat(n) * 100) / 100;
}
//在数组中查找元素值  
function in_array(v, a) {

	for (var i in a) {
		if (v === a[i]) {
			return true;
		}
	}
	return false;
} // 返回-1表示没找到，返回其他值表示找到的索  
function waraPay_valiFormat(val, strType) {

	if (val == '') return false;
	switch (strType.toUpperCase()) {
		case 'EMAIL':
			return isEmail(val);
			break;
		case 'TEL':
			return isTel(val);
			break;
		case 'POSTCODE':
			return isPostcode(val);
			break;
	}

	return true;

}

function isEmail(str) {
	var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/;
	return reg.test(str);
}

function isTel(str) {
	var reg = /^1(30|31|32|45|55|56|85|86|34|35|36|37|38|39|47|50|51|52|57|58|59|82|83|87|88|33|53|89|80)[0-9]{8}$/;
	return reg.test(str);
}

function isPostcode(str) {
	var reg = /^\d{6}$/;
	return reg.test(str);
}


function initInfo() {
	$info['num'] = parseInt($('#cartProNum').text());
	$info['price'] = parseFloat($('#cartProPrice').text());
	$info['unit'] = parseFloat($('#cartPriceUnit').val());
	$info['logFee'] = parseFloat($('#cartLogFee').text());


	if ($('#cartPriceUnit').length !== 0) {
		$info['price'] = $('#cartPriceUnit').val().split('-')[1];
	}

	updateInfo();
}


function updateInfo() {
	$('#cartProNum').text($info['num']);
	$('#ordInfo_num').val($info['num']);
	$('#cartProPrice').text(parseFloat($info['price']).toFixed(2));
	//$('#cartPriceUnit').val( $info['unit'] );
	$('#cartLogFee').text($info['logFee'].toFixed(2))

	$('#cartProFee').text(( $info['price'] * $info['num']).toFixed(2));
	$('#cartTotFee').text(( $info['price'] * $info['num'] + $info['logFee'] ).toFixed(2));
}