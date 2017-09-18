<?php
require_once('cfg.config.php');
header('Content-Type:text/html; charset=' . WARAPAY_CHARSET);
//预处理未定义的索引
isset($_REQUEST['info']) || $_REQUEST['info'] = '';
isset($_REQUEST['trano']) || $_REQUEST['trano'] = '';
isset($_REQUEST['time']) || $_REQUEST['time'] = '';
isset($_REQUEST['sign']) || $_REQUEST['sign'] = '';
$_REQUEST['time'] !== '' || $_REQUEST['time'] = time();

$_REQUEST['info'] = wp_kses(
    $_REQUEST['info'],
    array('a' => array('href', 'target', 'title'), 'b' => array(), 'div' => array(), 'p' => array())
);
//使用密钥验证公钥的合法性
//$nonce = wp_create_nonce('waraPay_tip_sign');
$key = AUTH_KEY;
$sign = md5($_REQUEST['info'] . $_REQUEST['trano'] . $_REQUEST['time'] . $key);

if (!(isset($_REQUEST['pms']) && $_REQUEST['pms'] == 'sudo')) {
    if ($sign !== $_REQUEST['sign']) {
        $_REQUEST['info'] = 'SIGN_INVALID';
    }
}

$mainTitle = __('支付提示:','waraPayi18N');
$datetime = date('Y-m-d H:i:s', $_REQUEST['time']);
$siteName = get_option('blogname');
$siteUrl = get_option('siteurl');
$year = date('Y', time());
$year2 = $year + 1;
$footer = "
$datetime<br />
Copyright &copy; {$year}-{$year2} <a href=$siteUrl>$siteName</a> All Rights Reserved
";

$success_img = WARAPAY_IMG_URL.'success.png';
$attention_img =WARAPAY_IMG_URL.'warn.png';

//PAY_SUCCESS:支付成功
//SIGN_INVALID:签名不合法
//PRO_EMPTY:商品余量不足
//NONCE_EMPTY:校验码为空
//NONCE_INVALID:校验码过期
//VERIFY_FAILED:权限验证失败
//TIMEOUT:连接超时
//UNSUPPORTED_GATE:不支持的支付方式
//其他:未知的结果


switch (strtoupper($_REQUEST['info'])) {
    case 'PAY_SUCCESS':
        $info = array(
            'h1'     => __('支付成功','waraPayi18N'),
            'img'    => $success_img,
            'msg'    =>sprintf(__( '感谢您的购买!<br />请记下您的订单号: %s<br />您随时可以关闭此页面!<br />', 'waraPayi18N' ),$_REQUEST['trano']),
        );
        break;
    case 'PAY_FAILED':
        $info = array(
            'h1'     => __('订单未支付！','waraPayi18N'),
            'img'    => $attention_img,
            'msg'    => sprintf(__( '抱歉!此订单未支付。<br />请记下您的订单号: %s<br />您可以返回后刷新重新支付!<br />', 'waraPayi18N' ),$_REQUEST['trano']),
        );
    break;
    case 'SIGN_INVALID':
        $info = array(
            'h1'     => __('签名不合法','waraPayi18N'),
            'img'    => $attention_img,
            'msg'    => __( '签名无效！<br />您可能来自未知渠道或者修改过该页的URL。<br />如感到困惑,请联系管理员!<br />', 'waraPayi18N' ),
        );
        break;
    case 'PRO_EMPTY':
        $info = array(
            'h1'     => __('商品剩余数量不足','waraPayi18N'),
            'img'    => $attention_img,
            'msg'    => __( '商品剩余数量不足!<br />交易无法继续进行!您的资金尚未扣除!请提醒管理员添加商品!<br />如感到困惑,请联系管理员!<br />', 'waraPayi18N' ),
        );
        break;
    case 'NONCE_EMPTY':
        $info = array(
            'h1'     => __('校验码为空','waraPayi18N'),
            'img'    => $attention_img,
            'msg'    => __( '校验码为空!<br />交易无法继续进行!您的资金尚未扣除!请提醒管理员处理!<br />如感到困惑,请联系管理员!<br />', 'waraPayi18N' ),
        );
        break;
    case 'NONCE_INVALID':
        $info = array(
            'h1'     => __('校验码已过期','waraPayi18N'),
            'img'    => $attention_img,
            'msg'    => __( '校验码已过期!<br />交易无法继续进行!您的资金尚未扣除!请提醒管理员更新缓存!<br />如感到困惑,请联系管理员!<br />', 'waraPayi18N' ),
        );
        break;
    case 'VERIFY_FAILED':
        $info = array(
            'h1'     => __('权限验证失败','waraPayi18N'),
            'img'    => $attention_img,
            'msg'    =>  __( '权限验证失败!<br />您没有访问该页面的权限!<br />如感到困惑,请联系管理员!<br />', 'waraPayi18N' ),
        );
        break;
    case 'TIMEOUT':
        $info = array(
            'h1'     => __('连接服务器超时','waraPayi18N'),
            'img'    => $attention_img,
            'msg'    => __( '连接服务器超时!<br />您暂时无法完成支付!<br />这可能是网站服务器的问题!请联系管理员!<br />', 'waraPayi18N' ),
        );
        break;
    case 'UNSUPPORTED_GATE':
        $info = array(
            'h1'     => __('不支持该支付方式','waraPayi18N'),
            'img'    => $attention_img,
            'msg'    => __( "不支持该支付方式!<br />您暂时无法完成支付!<br />暂时不支持此支付方式,请选择<a href='javascript:history.go(-1)'>其他支付方式</a><br />", 'waraPayi18N' ),
        );
        break;
    default:
        $info = array(
            'h1'     => __('未知的结果','waraPayi18N'),
            'img'    => $attention_img,
            'msg'    => sprintf(__( '未知的结果:%s。<br />这可能是程序开发中产生的错误,请联系管理员!您的资金尚未扣除!<br />您随时可以关闭此页面!<br />', 'waraPayi18N' ),strtoupper($_REQUEST['info'])),
        );
        break;
}

?>

<!DOCTYPE HTML>
<html>
<head>
    <title><?php echo $mainTitle . $info['h1']; ?></title>
    <style>
		body,html{margin:0;padding:0;width:100%;height:100%;background:#eef;font-family:微软雅黑,Microsoft YaHei,simsun;}
		body *{margin:0;padding:0;}		#mainContariner{position:absolute;top:50%;left:50%;display:block;margin-top:-180px;margin-left:-320px;width:640px;height:320px;border:solid 1px #390;background:#fff;cursor:pointer;}
		h1{padding-top:5px;padding-bottom:5px;width:100%;background:url(../styles/images/h1_gray.png) repeat-x;color:#444;text-align:center;font-size:25px;}
		#content{margin:20px 200px 10px;font-size:14px;font-family:Microsoft YaHei,simsun;line-height:2.5em;}
		#content_en{margin:0 200px 10px;color:#888;font-size:12px;font-family:Microsoft YaHei,simsun;line-height:1.5em;}		#footer{position:absolute;right:20px;bottom:10px;text-align:right;font-style:italic;font-size:12px;line-height:1.5em;}
		.tip_img{position:absolute;top:50px;left:10px;width:160px;}
		.bl,.br,.tl,.tr{position:absolute;display:block;width:5px;height:5px;background-repeat:no-repeat;}
		.tl{top:-1px;left:-1px;background-image:url(../styles/images/cn_g_01.png);background-position:top left;}
		.tr{top:-1px;right:-1px;background-image:url(../styles/images/cn_g_02.png);background-position:top right;}
		.bl{bottom:-1px;left:-1px;background-image:url(../styles/images/cn_g_03.png);background-position:bottom left;}
		.br{right:-1px;bottom:-1px;background-image:url(../styles/images/cn_g_04.png);background-position:bottom right;}
		a{text-decoration:none;}
    </style>


</head>
<body>
<div id="mainContariner">
    <div class="tl"></div>
    <div class="tr"></div>
    <div class="bl"></div>
    <div class="br"></div>
    <img class="tip_img" src="<?php echo $info['img']; ?>">

    <h1><?php echo $info['h1']; ?></h1>

    <div id="content">

        <?php echo $info['msg']; ?>
    </div>

    <div id="footer">

        <?php echo $footer; ?>
    </div>

</div>

</body>
</html>