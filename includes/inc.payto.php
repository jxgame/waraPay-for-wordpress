<?php
/*
本页面严格要求请求从tpl.cart.php中传过来.并不关心非重要字段的正确性.
对于字段的合法性检验由tpl.cart.php来完成.
*/
header('Content-Type:text/html; charset=utf-8');
require_once('cfg.config.php');
require_once('cls.mail.php');
//wp_mail_content_charset不用设置,WP邮件类自动修改为博客编码
//############################################################################
//CHECK NONCE
if (!isset($_REQUEST['nonce'])) {
    die(waraPay_show_tip('NONCE_EMPTY'));
}
//wp_verify_nonce( $_REQUEST['nonce'], 'waraPay_front_nonce_action')
if (!wp_verify_nonce($_REQUEST['nonce'], 'fromcart')) {
    die(waraPay_show_tip('NONCE_INVALID'));
}
//############################################################################
//DB SECTION
if (!isset($_REQUEST['proid'])) {
    die();
}
//需要的参数, 
$para = array(
    'proid',
    'email',
    'msg',
    'extra',
    'addr',
    'tel',
    'num',
    'aliacc',
    'referer',
    'postcode',
    'ordname'
);
//把未声明的预使用变量定义为''
$ord = waraPay_noEmpty($para, $_REQUEST);
//先获取编码,如果不存在则默认为WP设置的编码
isset($_REQUEST['charset']) || $_REQUEST['charset'] = WARAPAY_CHARSET;
//对URL中的字符解码并转码, 转成此WP的编码, 后续的数据库写入就不在考虑编码的问题了
//不管外界是以何种编码传入,转码后从此页面提交给服务器的编码肯定是和服务器一致了
$ord = waraPay_urlDecodeDeep($ord, $_REQUEST['charset'], WARAPAY_CHARSET);
extract($ord);
unset($ord);
//*************************
//下单时间
$time_l10n = current_time('timestamp');
$otime     = date('YmdHis', $time_l10n);
$odate     = date('Ymd', $time_l10n);
$otimeo    = date('His', $time_l10n);
//4位随机数
$randNum = rand(1000, 9999);
//10位序列号,可以自行调整。
$strReq = $otimeo . $randNum;
//订单号(唯一):由下单时间戳和订单编号组成
$out_trade_no = $otime . rand(100000, 999999);
//购买数量
$num = ((int)$num > 0) ? (int)$num : 1;
//展示页面
$show_url = $referer;
//获取商品信息
require_once 'cls.info.php';
//-----------------------------------------------------------------------
//实例化一个商品
//-----------------------------------------------------------------------
$pro     = new waraPay_product();
$proInfo = $pro->get_info($proid);
//############################################################################
//商品剩余数量重写
if ($proInfo['autosend']) {
    $autosepori = $autosep = trim($proInfo['autosep']);
    $autosep    = waraPay_preg_pre($autosep);
    if ($autosep == '') {
        $autosep = '\n';
    }
    $arr_autosrc    = preg_split("@$autosep@", $proInfo['autosrc']);
    $arr_autosrc    = waraPay_filter_empty($arr_autosrc);
    $proInfo['num'] = count($arr_autosrc);
    $pro->set('num', $proInfo['num']);
}
//############################################################################
//余量判断
$notify_email = waraPay_get_setting('notify_email');
$admin_url    = get_option('siteurl') . '/wp-admin/options-general.php?page=waraPay';
if ((int)$proInfo['num'] < 2) {
    if (waraPay_get_setting('pro_lack_notify')) {
        wp_mail(
            $notify_email,
            '商品余量不足,请及时补充',
            "商品ID:$proid \n商品名:$proInfo[name] \n管理地址:$admin_url"
        );
    }
}
$proInfo['num'] = intval($proInfo['num']);
if ($proInfo['num'] < $num) {
    $cnum = (int)$proInfo['num'];
    die(waraPay_show_tip('PRO_EMPTY'));
}
//促销价格重载
if ($proInfo['promote'] == 1 && $proInfo['probdate'] < date('Ymd') && date(
      'Ymd'
  ) < $proInfo['proedate'] && $proInfo['discountb'] == 1
) {
    if (($proInfo['protime'] == 1 && $proInfo['probtime'] < date('His') && date(
            'His'
        ) < $proInfo['proetime']) || $proInfo['protime'] == 0
    ) {
        $proInfo['price'] *= $proInfo['discount'];
        $proInfo['price'] = round($proInfo['price'], 2);
    }
}
//运费价格重载
$type = $proInfo['protype'];
if (in_array($type, array('ADP', 'LINK'))) {
    $showMultiPrice = true;
} else {
    $showMultiPrice = false;
}
if ($showMultiPrice) {
    $units            = preg_split('@-@', $_REQUEST['unit']);
    $unit             = $units[0];
    $proInfo['price'] = $proInfo[$unit];
}
//商品总费用,不含运费(商品的最终单价确定,数量确定,算出商品总费用)
$proInfo['profee'] = $proInfo['price'] * $num;
//加载运费
if ($proInfo['spfre'] == 0) {
    $proInfo['freight'] = 0.00;
}
//交易总费用(商品的总费用确定,运费确定)
$proInfo['ordfee'] = $proInfo['profee'] + $proInfo['freight'];
$subject           = waraPay_esc_quotes($proInfo['name']);
$body              = waraPay_esc_quotes($proInfo['description']);
//$price   = waraPay_esc_quotes($proInfo['price'])		    ;
//商品的最终单价为:$proInfo['price']
//商品的总费用为(不含运费,总数量):$proInfo['profee']
//商品的运费为:$proInfo['freight']
//交易的总费用为:$proInfo['ordfee']
//交易数量(需为1):$proInfo['ordnum']
$proInfo['ordnum'] = 1;
if (is_user_logged_in()) {
    global $current_user;
    get_currentuserinfo();
    $username = $current_user->user_login;
    $userid   = $current_user->ID;
} else {
    $username = '';
    $userid   = '';
}
$username   = //更新订单数据库
$arr_insert = array(
    'proid'       => $proid,
    'ordname'     => $ordname, //买家姓名
    'emailsend'   => '0',
    'postcode'    => $postcode, //买家邮编
    'aliacc'      => trim((string)$aliacc),
    'otime'       => $otime,
    'status'      => '0',
    'series'      => $out_trade_no,
    'buynum'      => $num, //购买的数量
    'address'     => $addr, //买家地址
    'email'       => $email, //买家邮件
    'phone'       => $tel, //买家电话
    'remarks'     => $extra,
    'message'     => $msg,
    'referer'     => $referer,
    'paygate'     => $_REQUEST['paygate'],
    'payprice'    => $proInfo['price'], //成交单价,打折会有影响
    'freight'     => $proInfo['freight'], //运费
    'profee'      => $proInfo['profee'], //商品除运费外的总费用
    'ordfee'      => $proInfo['ordfee'], //交易总金额
    'platTradeNo' => '',
    'username'    => $username,
    'userid'      => $userid
);
$arr_insert = apply_filters('waraPay_insertorder', $arr_insert);
$ord        = new waraPay_order();
$ordid      = $ord->insert($arr_insert);
//global $user_ID;
$ord->sets('order_user_id', $user_ID);
//特殊商品的额外处理
if ($proInfo['protype'] == 'ADP') {
    $timeLong  = waraPay_unitToDay($_REQUEST['num'], $units[0]) * 24 * 3600;
    $extraInfo = array(
        'ordtype'   => 'ADP',
        'imgSrc'    => $_REQUEST['imgSrc'],
        'imgLink'   => $_REQUEST['imgLink'],
        'imgMd5'    => md5(file_get_contents($_REQUEST['imgSrc'])),
        'startTime' => current_time('timestamp'),
        'endTime'   => current_time('timestamp') + $timeLong,
        'timeLong'  => $timeLong
    );
    $ord->set('', '', $extraInfo);
}
if ($proInfo['protype'] == 'LINK') {
    $timeLong  = waraPay_unitToDay($_REQUEST['num'], $units[0]) * 24 * 3600;
    $extraInfo = array(
        'ordtype'   => 'LINK',
        'linkName'  => $_REQUEST['linkName'],
        'linkUrl'   => $_REQUEST['linkUrl'],
        'linkDesc'  => $_REQUEST['linkDesc'],
        'startTime' => current_time('timestamp'),
        'endTime'   => current_time('timestamp') + $timeLong,
        'timeLong'  => $timeLong
    );
    $ord->set('', '', $extraInfo);
}
/////////////////////////////////////////////////////////////////////////////////////
//-----------------------------------------------------------------------
//实例化一个订单
//-----------------------------------------------------------------------
$order          = new waraPay_order();
$orderInfo      = $order->get_info($ordid);
$waraPay_mail = new waraPay_Mail($proInfo, $orderInfo);
/////////////////////////////////////////////////////////////////////////////////////
////订单邮件通知
$buyer_ord_notify  = waraPay_get_setting('buyer_ord_notify');
$seller_ord_notify = waraPay_get_setting('seller_ord_notify');
if ($buyer_ord_notify) {
    //wp_mail( $email , '交易状态已改变为：等待付款',
    //	"亲爱的顾客,感谢您的购买,请及时完成支付<br />订单号为:$out_trade_no");
    $waraPay_mail->send($email, 'ORDER');
}
if ($seller_ord_notify) {
    //wp_mail( $notify_email , '有一笔新的订单等待付款', "订单号为:$out_trade_no");
    $waraPay_mail->send($notify_email, 'ORDER', true);
}
///////////////////////////////参数预定义////////////////////////////////////////
//商户额外参数 extra_common_param
$extra_common_param = "";
/**************************请求参数**************************/
//参数body（商品描述）、subject（商品名称）、extra_common_param（公用回传参数）不能包含特殊字符（如：#、%、&、+）、敏感词汇，也不能使用外国文字（旺旺不支持的外文，如：韩文、泰语、藏文、蒙古文、阿拉伯语）
//必填参数//
//请与贵网站订单系统中的唯一订单号匹配
$out_trade_no = $out_trade_no;
//订单名称，显示在支付宝收银台里的"商品名称"里，显示在支付宝的交易管理的"商品名称"的列表里。
$subject = $subject; //$_POST['subject'];
//订单描述、订单详细、订单备注，显示在支付宝收银台里的"商品描述"里
$body = $body; //$_POST['body'];
//订单总金额，显示在支付宝收银台里的"应付总额"里
//$total_fee    = $total_fee;//$_POST['total_fee'];
//扩展功能参数——默认支付方式//
//默认支付方式，取值见"即时到帐接口"技术文档中的请求参数列表
$paymethod = '';
//默认网银代号，代号列表见"即时到帐接口"技术文档"附录"→"银行列表"
if (isset($_REQUEST['bankType']) && isset($_REQUEST['paygate']) && $_REQUEST['paygate'] == 'UNION') {
    $defaultbank = $_REQUEST['bankType'];
} else {
    $defaultbank = '';
}
//扩展功能参数——防钓鱼//
//防钓鱼时间戳
$anti_phishing_key = '';
//获取客户端的IP地址，建议：编写获取客户端IP地址的程序
$exter_invoke_ip = '';
//注意：
//1.请慎重选择是否开启防钓鱼功能
//2.exter_invoke_ip、anti_phishing_key一旦被使用过，那么它们就会成为必填参数
//3.开启防钓鱼功能后，服务器、本机电脑必须支持SSL，请配置好该环境。
//示例：
//$exter_invoke_ip = '202.1.1.1';
//$ali_service_timestamp = new AlipayService($aliapy_config);
//$anti_phishing_key = $ali_service_timestamp->query_timestamp();//获取防钓鱼时间戳函数
//扩展功能参数——其他//
//商品展示地址，要用 http://格式的完整路径，不允许加?id=123这类自定义参数
$show_url = $show_url;
//自定义参数，可存放任何内容（除=、&等特殊字符外），不会显示在页面上
//NOT ARRAY ,SRING ###MUST FILTER IT###
$extra_common_param = $extra_common_param;
//扩展功能参数——分润(若要使用，请按照注释要求的格式赋值)
$royalty_type       = ""; //提成类型，该值为固定值：10，不需要修改
$royalty_parameters = "";
//注意：
//提成信息集，与需要结合商户网站自身情况动态获取每笔交易的各分润收款账号、各分润金额、各分润说明。最多只能设置10条
//各分润金额的总和须小于等于total_fee
//提成信息集格式为：收款方Email_1^金额1^备注1|收款方Email_2^金额2^备注2
//示例：
//royalty_type 		= "10"
//royalty_parameters= "111@126.com^0.01^分润备注一|222@126.com^0.01^分润备注二"
/////////////////////////////参数接口规范化/////////////////////////////////////////
$payto_para = array(
    //
    ''                   => '',
    ##商品信息##//############################################################################
    //商品名称
    'name'               => $subject,
    //商品价格
    'price'              => $proInfo['ordfee'],
    //购买数量
    'num'                => 1,
    //商品描述
    'desc'               => $body,
    //展示页面
    'showurl'            => $show_url,
    ##订单信息##//############################################################################
    //订单号
    'ordno'              => $out_trade_no,
    //日期
    'date'               => $odate,
    //时间
    'time'               => $otimeo,
    ##系统参数//############################################################################
    //字符编码
    'charset'            => WARAPAY_CHARSET,
    //客户IP地址
    'ip'                 => $_SERVER['REMOTE_ADDR'],
    //其他
    'paymethod'          => $paymethod,
    'bank'               => $defaultbank,
    'extra'              => $extra_common_param,
    'royalty_type'       => $royalty_type,
    'royalty_parameters' => $royalty_parameters,
    'anti_phishing_key'  => $anti_phishing_key,
    'exter_invoke_ip'    => $exter_invoke_ip,
    //tenpay
    //序列号
    'strReq'             => $strReq,
);
include_once 'tpl.payto.php';
$gate = strtolower($_REQUEST['paygate']);
if (in_array($gate,array('warapay','alipay','wechat','palpay')) == 'union') {
    $channel=$gate;
	$gate = 'warapay';
}
$file = "api_$gate" . DIRECTORY_SEPARATOR . "inc.{$gate}to.php";

if (file_exists($file)) {
    include_once $file;
} else {
    echo waraPay_show_tip('UNSUPPORTED_GATE');
}
//die(dirname(__FILE__) . "\inc.{$gate}to.php");
//include_once( 'api_waraPay' . DIRECTORY_SEPARATOR . 'inc.waraPayto.php' );
//include_once( 'api_tenpay' . DIRECTORY_SEPARATOR . 'inc.tenpayto.php' );
//include_once( 'api_paypal' . DIRECTORY_SEPARATOR . 'inc.paypalto.php' );
echo '</body></html>';
