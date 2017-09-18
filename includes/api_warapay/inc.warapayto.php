<?php
header('Content-Type:text/html; charset=utf-8');
require_once('cls.warapay_service.php');

$p = $payto_para;
unset ($payto_para);
/////////////////////////////////////////////////////////////////////////////////////
$wara_currency = waraPay_get_setting('wara_currency');
if (empty($wara_currency)) {
    $wara_currency = 'CNY';
}
//CNY:人民币
//KER:韩元
//VND:越南盾
/////////////////////////////////////////////////////////////////////////////////////
//构造要请求的参数数组
$parameter = array(
    'channel'            => $channel,
    'currency'            => $wara_currency,  
	'money'              => $p['price'],
	'out_trade_no'       => $p['ordno'],
	'subject'            => $p['name'],
	'body'               => $p['price'].'x'.$p['num'], 
	'buyer_email'               => $email, 
	'uid'               => $userid, 
); 

$waraPayService = new warapayService($warapay_config);
$html_text     = $waraPayService->qrPay($parameter);
echo '<title>'.__('页面跳转中...','waraPayi18N').'</title>';
echo $html_text;


