<?php
session_start();
session_destroy();
require_once 'cfg.config.php';
require_once 'cls.info.php';

/////是否要求登录?
if (waraPay_get_setting('user_must_login') && !is_user_logged_in()) {
    if (is_ssl()) {
        $proto = 'https://';
    } else {
        $proto = 'http://';
    }
    $login_url = site_url(
        'wp-login.php?redirect_to=' . urlencode($proto . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])
    );
    wp_redirect($login_url);
    exit;
} 
if(!isset($_REQUEST['proid']) || !intval($_REQUEST['proid'])) die(waraPay_show_tip('SIGN_INVALID'));
$pro = new waraPay_product();
$proInfo = $pro->get_info($_REQUEST['proid']);
if(!$proInfo) die(waraPay_show_tip('PRO_EMPTY'));
//print_r($proInfo);	
//array('name'=>'','label'=>'', 'validate'=>'', 'priority'=>10, 'tip'=>'');
/*
VALIDATE:
DEFAULT: BOOLEAN TRUE or 'TRUE' - NOT EMPTY IS OK
CASE: EMPTY or 'FALSE' - ANYTHING IS OK
CASE: NUM - LEAST
CASE: ARRAY(LEAST,MOST) - LEAST LETTERS, MOST LETTERS
CASE: STRING - VALIDATE FUNCTION ( RETURN TRUE FOR OK, FALSE FOR BAD )
*/

$arr_buyerInfo['CUSTOM'] = array(
    array('name' => 'ordname', 'label' => __('收货人姓名','waraPayi18N'), 'validate' => array(2, 20)),
    array('name' => 'addr', 'label' => __('详细地址','waraPayi18N'), 'validate' => array(6, 60)),
    array('name' => 'postcode', 'label' => __('邮政编码','waraPayi18N'), 'validate' => 'waraPay_validateFormat', 'type' => 'POSTCODE'),
    array('name' => 'tel', 'label' => __('联系手机','waraPayi18N'), 'validate' => array(9, 12)),
    array('name' => 'email', 'label' => __('电子邮箱','waraPayi18N'), 'validate' => 'waraPay_validateFormat', 'type' => 'EMAIL'),
    array('name' => 'msg', 'label' => __('给卖家留言','waraPayi18N'), 'validate' => array(-1, 300)),
);

$arr_buyerInfo['CUSTOM'] = apply_filters('waraPay_carBbuyerInfo_CUSTOM', $arr_buyerInfo['CUSTOM']);

$arr_buyerInfo['VIRTUAL'] = array(
    array('name' => 'email', 'label' => __('电子邮箱','waraPayi18N'), 'validate' => 'waraPay_validateFormat', 'type' => 'EMAIL'),
    array('name' => 'msg', 'label' => __('给卖家留言','waraPayi18N'), 'validate' => array(-1, 300)),
);


//-----------------------------------------------------------------------
//Widget ad
//-----------------------------------------------------------------------
/////////////////////////////////////////////////////////////////////////////////////
$arr_buyerInfo['ADP'] = array(
    array('name' => 'imgSrc', 'label' => __('图片地址','waraPayi18N'), 'validate' => 'waraPay_validateFormat', 'type' => 'URL'),
    array('name' => 'imgLink', 'label' => __('跳转地址','waraPayi18N'), 'validate' => 'waraPay_validateFormat', 'type' => 'URL'),
    array('name' => 'email', 'label' => __('电子邮箱','waraPayi18N'), 'validate' => 'waraPay_validateFormat', 'type' => 'EMAIL'),
    array('name' => 'msg', 'label' => __('给卖家留言','waraPayi18N'), 'validate' => array(-1, 300)),
);

$arr_buyerInfo['LINK'] = array(
    array('name' => 'linkName', 'label' => __('链接名称','waraPayi18N'), 'validate' => array(1, 7)),
    array('name' => 'linkUrl', 'label' => __('链接地址','waraPayi18N'), 'validate' => 'waraPay_validateFormat', 'type' => 'URL'),
    array('name' => 'linkDesc', 'label' => __('链接描述','waraPayi18N'), 'validate' => array(-1, 30)),
    array('name' => 'email', 'label' => __('电子邮箱','waraPayi18N'), 'validate' => 'waraPay_validateFormat', 'type' => 'EMAIL'),
    array('name' => 'msg', 'label' => __('给卖家留言','waraPayi18N'), 'validate' => array(-1, 300)),
);


/////////////////////////////////////////////////////////////////////////////////////


foreach ($arr_buyerInfo as $k => $v) {
    $arr_buyerInfo[$k] = apply_filters("waraPay_carBbuyerInfo_$k", $arr_buyerInfo[$k]);
}

$arr_buyerInfo = apply_filters('waraPay_cartBbuyerInfo', $arr_buyerInfo);


add_filter('waraPay_cartProType', 'waraPay_cartProType_fn', 999, 2);

function waraPay_cartProType_fn($info, $type)
{
    if (!empty($type) && isset($info[$type])) {
        return $info[$type];
    } else {
        return 'CUSTOM';
    }
}


if (empty($proInfo['protype'])) {
    $type = 'CUSTOM';
} else {
    $type = $proInfo['protype'];
}


$type = apply_filters('waraPay_cartProTypeName', $type, $_REQUEST);

//$type = 'VIRTUAL';


$arr_buyerInfo = apply_filters('waraPay_cartProType', $arr_buyerInfo, $type);


if (in_array($type, array('ADP', 'LINK'))) {
    $showMultiPrice = true;
} else {
    $showMultiPrice = false;
}

if ($showMultiPrice):

    $unitHtml = '<select name="unit" class="unitsl" id="cartPriceUnit">';
    if (!empty($proInfo['pricePerDay']) && $proInfo['pricePerDay'] > 0) {
        $unitHtml .= '<option value="pricePerDay-' . $proInfo['pricePerDay'] . '">' . __('Day', 'waraPayi18N') . '</option>';
    }
    if (!empty($proInfo['pricePerWeek']) && $proInfo['pricePerWeek'] > 0) {
        $unitHtml .= '<option value="pricePerWeek-' . $proInfo['pricePerWeek'] . '">' . __(
              'Week',
              'waraPayi18N'
          ) . '</option>';
    }
    if (!empty($proInfo['pricePerMonth']) && $proInfo['pricePerMonth'] > 0) {
        $unitHtml .= '<option value="pricePerMonth-' . $proInfo['pricePerMonth'] . '">' . __(
              'Month',
              'waraPayi18N'
          ) . '</option>';
    }
    if (!empty($proInfo['pricePerQuarter']) && $proInfo['pricePerQuarter'] > 0) {
        $unitHtml .= '<option value="pricePerQuarter-' . $proInfo['pricePerQuarter'] . '">' . __(
              'Quarter',
              'waraPayi18N'
          ) . '</option>';
    }
    if (!empty($proInfo['pricePerYear']) && $proInfo['pricePerYear'] > 0) {
        $unitHtml .= '<option value="pricePerYear-' . $proInfo['pricePerYear'] . '">' . __(
              'Year',
              'waraPayi18N'
          ) . '</option>';
    }
    $unitHtml .= '</select>';

endif;


//验证字段的合法性
if (isset($_REQUEST['cartSubmit'])) {
    foreach ($arr_buyerInfo as $arr) { //T for error
        if (!isset($_REQUEST[$arr['name']]) || !isset($arr['validate'])) {
            continue;
        }
        $src = $_REQUEST[$arr['name']];
        $v   = $arr['validate'];
        if ($v === true || strtoupper((string)$v) == 'TRUE') {
            if (empty($src)) {
                $validerr[$arr['name']] = true;
            } else {
                $validerr[$arr['name']] = false;
            }
        } elseif (empty($v)) {
            $validerr[$arr['name']] = false;
        } elseif (is_numeric($v)) {
            preg_match_all("@.@us", $src, $match);
            $len = count($match[0]);
            if ($len !== $v) {
                $validerr[$arr['name']] = true;
            } else {
                $validerr[$arr['name']] = false;
            }
        } elseif (is_array($v) && count($v) == 2) { //长度限制
            preg_match_all("@.@us", $src, $match);
            $len = count($match[0]);
            if ($v[0] !== -1 && $v[1] !== -1 && ($len < $v[0] || $len > $v[1])) {
                $validerr[$arr['name']] = true;
            } elseif ($v[1] == -1 && $len < $v[0]) {
                $validerr[$arr['name']] = true;
            } elseif ($v[0] == -1 && $len > $v[1]) {
                $validerr[$arr['name']] = true;
                echo $arr['name'];
            } else {
                $validerr[$arr['name']] = false;
            }
        } elseif (is_string($v)) {
            $vlitype = (isset($arr['type'])) ? $arr['type'] : null;
            if (is_callable($v)) {
                if (call_user_func($v, $src, $vlitype)) {
                    $validerr[$arr['name']] = false;
                } else {
                    $validerr[$arr['name']] = true;
                }
            }
        }
        //END OF IF
    }
    //END OF FOR EACH
    $errExist = false;
    foreach ($validerr as $v) {
        if ($v) {
            $errExist = true;
            break;
        }
    }
    if (!$errExist) {
        $nonce = wp_create_nonce('fromcart');
        header("Location:inc.payto.php?{$_SERVER['QUERY_STRING']}&nonce=$nonce");
    }
}
//END OF IF


/////////////////////////////////////////////////////////////////////////////////////


if (isset($validerr)) {
    $err = $validerr;
}

//print_r($err);
//生成FOOTER
$siteName = get_option('blogname');
$siteUrl = get_option('siteurl');
$year = date('Y', time());
$year2 = $year + 1;
$footer_Copyright = "
Copyright &copy; {$year}-{$year2} <a href=$siteUrl>$siteName</a> All Rights Reserved
";


//获取请求参数
$arr_fields = array(
    'proid',
    'email',
    'num',
    'msg',
    'extra',
    'addr',
    'tel',
    'ordname',
    'postcode',
    'nonce',
    'paygate',
    'bankType',
    'unit',
    'imgSrc',
    'imgLink',
    'referer'
);

$arr_fields = apply_filters('waraPay_cart_queryVars', $arr_fields);

//把未声明的预使用变量定义为''
$ord = waraPay_noEmpty($arr_fields, $_REQUEST);
//先获取编码,如果不存在则默认为WP设置的编码
isset($_REQUEST['charset']) || $_REQUEST['charset'] = WARAPAY_CHARSET;
//对URL中的字符解码并转码, 转成此WP的编码, 后续的数据库写入就不在考虑编码的问题了
//不管外界是以何种编码传入,转码后从此页面提交给服务器的编码肯定是和服务器一致了
$ord = waraPay_urlDecodeDeep($ord, $_REQUEST['charset'], WARAPAY_CHARSET);
//对发送给用户的浏览器的该网页头
header('Content-Type:text/html; charset=' . $_REQUEST['charset']);


$proid = intval($_REQUEST['proid']);

//生成NONCE
$nonce = wp_create_nonce('waraPay_front_nonce_action', 'waraPay_front_nonce_name');


//对ord数组进行重写.
!empty($ord['num']) || $ord['num'] = 1;


$ord['proFee'] = $proInfo['price'] * $ord['num'];
if ($proInfo['spfre']) {
    $ord['logFee'] = $proInfo['freight'];
} else {
    $ord['logFee'] = '0.00';
}
$ord['totFee'] = $ord['proFee'] + $ord['logFee'];


//对商品图片重写
!empty($proInfo['images']) || $proInfo['images'] = WARAPAY_IMG_URL . '/cart_small.jpg';



$arr_buyerInfo = apply_filters('waraPay_buyerInfo', $arr_buyerInfo);

$arr_buyerInfo = waraPay_sortByOneKey($arr_buyerInfo, 'priority', 10);
function waraPay_buyerInfo_fn($info)
{
    //unset( $info['ordname']);
    //$info = array_merge($info,array('ordFOO'	 => array('收货人FOO','请填写收货人姓名')));
    return $info;
}


$arr_buyInfo = array(
    'WARAPAY' => array('waraPay', 1),
    'ALIPAY' => array(__('支付宝','waraPayi18N'), 0),
    'WECHAT' => array(__('微信','waraPayi18N'), 0),
    /*'PAYPAL' => array(__('PayPal','waraPayi18N'), 0),*/
);
 

$file = "api_warapay" . DIRECTORY_SEPARATOR . "cls.warapay_service.php";
if (file_exists($file)) {
    include_once $file;
	$waraPayService = new warapayService($warapay_config);
	$currencyFlag=$waraPayService->getCurrencyFlag();
}

?>

<!DOCTYPE HTML>
<html>
<head>
    <title><?php echo __('商品购买详情','waraPayi18N');?></title>
    <link href="<?php echo WARAPAY_URL; ?>/styles/cart.css" rel="stylesheet"/>
    <script src="//lib.sinaapp.com/js/jquery/3.1.0/jquery-3.1.0.min.js"></script>
    <script src="../javascripts/cart.js"></script>
    <?php echo apply_filters('waraPay_cartHeader', null); ?>
</head>

<body>

<div id="header">
    <div id="headCenter">
        <div id="logo"><h1><?php echo __('waraPay快捷支付!','waraPayi18N');?></h1></div>
        <div id="headInfo"><?php echo __('欢迎您!','waraPayi18N');?> | <a href="javascript:void(0);"><?php echo __('订单中心','waraPayi18N');?></a> 
        </div>
    </div>
</div>
<div id="main">
    <div id="mainPatch">
        <form action="" method="get" id="frmOrdInfo">
            <h1><?php echo __('购物车','waraPayi18N');?></h1>
            <hr/>
            <div id="cartInfo">
                <h2><?php echo __('第一步：确认商品信息','waraPayi18N');?></h2>
                <hr class="dot"/>
                <!--#########################################################################-->
                <table id="cartTable">
                    <thead>
                    <tr>
                        <th class="proName"><?php echo __('商品名称','waraPayi18N');?></th>
                        <th class="proPrice"><?php echo __('单价(元)','waraPayi18N');?></th>
                        <th class="option"><?php echo __('X','waraPayi18N');?></th>
                        <th class="buyNum"><?php echo __('数量','waraPayi18N');?></th>
                        <th class="equal"><?php echo __('=','waraPayi18N');?></th>
                        <th class="proFee"><?php echo __('小计(元)','waraPayi18N');?></th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <td colspan="6" class="totalFee"><?php echo sprintf(__('应付总额(含运费：<em id="cartLogFee">%s</em>元)','waraPayi18N'),$ord['logFee']);?>
                            <?php echo $currencyFlag;?><em id="cartTotFee"><?php echo $ord['totFee']; ?></em><?php echo __('元','waraPayi18N');?>
                        </td>
                    </tr>
                    </tfoot>

                    <tbody>
                    <tr>
                        <td>
                            <img id="proImg" src="<?php echo $proInfo['images']; ?>"
                                 title="<?php echo '(ID:' . $proInfo['proid'] . ')&nbsp;'; ?>">

                            <div id="proName"><?php echo $proInfo['name']; ?></div>
                        </td>
                        <td id="cartProPrice"><?php echo $proInfo['price']; ?></td>
                        <td>X</td>
                        <td>
                            <span><input type="button" value="－" id="proNumDecre"/></span>
                            <span id="cartProNum"><?php echo $ord['num']; ?></span>
                            <span><input type="button" value="+" id="proNumIncre"/></span>

                            <?php
                            if ($showMultiPrice) {
                                echo '<p>' . $unitHtml . '</p>';
                            }
                            ?>

                        </td>
                        <input type="hidden" name="num" id="ordInfo_num" value="<?php echo $ord['num']; ?>"/>
                        <td>=</td>
                        <td id="cartProFee"><?php echo $ord['proFee']; ?></td>
                    </tr>
                    </tbody>

                </table>
                <!--#########################################################################-->
            </div>
            <div id="buyerInfo">
                <h2><?php echo __('第二步：填写收货信息','waraPayi18N');?></h2>
                <hr class="dot"/>
                <?php
                foreach ($arr_buyerInfo as $key => $item) {
                    if (isset($arr_buyerInfo['html'])) {
                        echo $arr_buyerInfo['html'];
                        break;
                    }
                    $error  = false;
                    $_class = '';
                    $k      = $item['name'];
                    if (isset($err[$k]) && $err[$k] == true) {
                        $error = true;
                    }
                    if ($error) {
                        $_class = 'class="error"';
                    }
                    if ($k !== 'msg') {
                        echo '<p>';
                    } else {
                        echo '<p class="wideText">';
                    }
                    echo '<label>' . $item['label'] . '</label>';
                    if ($k !== 'msg') {
                        isset($ord[$k]) || $ord[$k] = '';
                        echo '<input type="text" name="' . $k . '" ' . $_class . ' value="' . $ord[$k] . '"/>';
                        if (isset($item['validate']) && ($item['validate'] !== false && (isset($item['validate']['0']) && $item['validate']['0'] !== -1))) {
                            echo '<span>*</span>';
                        } else {
                            echo '<span>&nbsp;</span>';
                        }
                    } else {
                        echo '<textarea name="msg">' . $ord['msg'] . '</textarea>';
                    }
                    $spanStyle = ($error) ? 'block' : 'none';
                    echo '<span class="tip" style="display:' . $spanStyle . '">';
                    if (!empty($item['tip'])) {
                        $tip = $item['tip'];
                    } else {
                        $tip = __('请填写正确的','waraPayi18N') . $item['label'];
                    }
                    echo $tip;
                    echo '</span></p><div class="clear"></div>';
                }
                ?>


            </div>
            <div class="clear"></div>

            <div id="payInfo">
                <h2><?php echo __('第三步：选择付款方式','waraPayi18N');?></h2>
                <hr class="dot"/>
                <ul>
                    <?php


                    foreach ($arr_buyInfo as $k => $item) {
                        $innerHtml = waraPay_wRCheck('paygate', $k, $ord['paygate'], $item[1]);
                        echo '<li>
			<input id="'.$k.'input" type="radio" ' . $innerHtml . '/>
			<label for="'.$k.'input"><img src="../styles/images/' . strtolower($k) . '_logo.gif" class="'.strtolower($k) . 'Logo" title="' . $item[0] . '"/></label>
          </li>';
                    }

                    ?>

                    <div class="clear"></div>

                </ul>
                <div class="clear"></div>  

            </div>
            <!--END OF PAYINFO-->

            <div id="paySubmit">
                <h2><?php echo __('第四步：去收银台结账','waraPayi18N');?></h2>
                <hr class="dot"/>
                <input type="submit" value="<?php echo __('确定订单并付款','waraPayi18N');?>"/>
            </div>
            <input type="hidden" name="referer" value="<?php echo $ord['referer']; ?>"/>
            <input type="hidden" name="nonce" value="<?php echo $nonce; ?>"/>
            <input type="hidden" name="proid" value="<?php echo $proid; ?>"/>
            <input type="hidden" name="cartSubmit" value="1"/>
        </form>
    </div>
    <!--END OF MAIN PATCH-->
</div>
<!--END OF MAIN-->

<div id="footer">
    <?php echo apply_filters('waraPay_cartFooter', null); ?>
    <div id="copyright">
        <p>
            <?php echo $footer_Copyright; ?>
        </p>
    </div>
    <p class="bankpreload"></p>
</div>
<!--EN OF FOOTER-->
<script>
	var $arr_valiFields = ['tel', 'addr', 'postcode', 'ordname', 'email'];
	var $arr_valiTips = [];
	$arr_valiTips['tel'] = '<?echo __('请填写正确的联系手机号码','waraPayi18N');?>';
	$arr_valiTips['addr'] = '<?echo __('请填写收货详细地址','waraPayi18N');?>'';
	$arr_valiTips['postcode'] = '<?echo __('请填写正确的邮政编码','waraPayi18N');?>'';
	$arr_valiTips['ordname'] = '<?echo __('请填写收货人姓名','waraPayi18N');?>'';
	$arr_valiTips['email'] = '<?echo __('请填写正确的电子邮箱地址','waraPayi18N');?>'';
</script>
</body>
</html>