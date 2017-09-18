<?php
header('Content-Type:text/html; charset=utf-8');
require_once('cls.warapay_service.php');
//计算得出通知验证结果
$waraPayService = new warapayService($warapay_config);
$result = $waraPayService->unPackage(trim($_GET['data']));
if($result['trade_no'] && is_numeric($result['trade_no'])) { //解包成功
	$result = $waraPayService->queryTrade(array('trade_no'=>trim($result['trade_no'])));
    ////////////////////////////////////////////////////////////////////////////////////
	$req=json_decode($result,320);
	if($req['code']===0 && $req['data']['status']=='succ'){
        //规范传入参数
        $para_ret = array();
        //支付平台别名
        $para_ret['plat_name'] = strtoupper($req['data']['channel']);
        //交易状态
        $para_ret['status'] = 1;
        //商家内部订单号
        $para_ret['out_ordno'] = $req['data']['out_trade_no'];
        //支付平台订单号
        $para_ret['plat_ordno'] = $req['data']['trade_no'];
        //交易总额
        $para_ret['total_fee'] = $req['data']['money'];
        //客户邮箱账号
        $para_ret['buyer_email'] = $req['data']['buyer_email'];
        //客户数字账号
        $para_ret['buyer_id'] = $req['data']['buyer_id'];
        //支付时间
        $para_ret['pay_time'] = $req['data']['paytime'];
        //处理返回参数
        require_once(WARAPAY_INC . 'cls.return.php');
        $ins_ret = new waraPayReturn($para_ret);
        $INFO    = $ins_ret->returnProcess();
	}else{
        //支付失败
		$para_ret['out_ordno']=$req['data']['out_trade_no'];
        $INFO = 'PAY_FAILED';	
	}
    ////////////////////////////////////////////////////////////////////////////////////
} else {
    //失败
    $INFO = 'VERIFY_FAILED';
}
isset($para_ret['out_ordno']) || $para_ret['out_ordno'] = '';
echo warapay_show_tip($INFO, $para_ret['out_ordno']);

