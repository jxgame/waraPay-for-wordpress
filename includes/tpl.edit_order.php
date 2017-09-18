<?php

defined('ABSPATH') || die();

global $wpdb;
global $waraPay_table_orders;


//-----------------------------------------------------------------------
// upadte
//----------------------------------------------------------------------- 	
if (isset($_POST['submit'])) {
    //unset($_POST['_wpnonce']);
    //unset($_POST['_wp_http_referer']);
    foreach ($_POST as $k => $v) {
        if (!in_array($k, $waraPay_table_orders)) {
            unset($_POST[$k]);
        }
    }
    $wpdb->update($wpdb->orders, $_POST, array('ordid' => intval($_REQUEST['ordid'])));
	echo "<script>alert('SUCCESS');</script>";
}

//-----------------------------------------------------------------------
//insert
//----------------------------------------------------------------------- 
if (empty($_POST) && empty($_GET['ordid'])) {
    //$wpdb->insert($wpdb->orders,array('name'=>'未命名'));
    //echo $wpdb->insert_id;
    //$_GET['ordid'] = $wpdb->insert_id;
}

//-----------------------------------------------------------------------
//delete
//----------------------------------------------------------------------- 
if (isset($_GET['action']) && $_GET['action'] == 'delete' && $_GET['ordid']) {
    $user = wp_get_current_user();
    if (!$user->has_cap('activate_plugins')) {
        die();
    }
    ?>
    <div style="margin:30px;"><?php echo __('删除后数据不可恢复, 你确定要这样做吗?','waraPayi18N');?>
        <a class="button-primary" href="<?php echo admin_url(
            'options-general.php?page=waraPay&action=delete&tab=orders&ordid=' . $_GET['ordid'] . '&sure'
        ); ?>"><?php echo __('确定','waraPayi18N');?></a>
        <a class="button-secondary"
           href="<?php echo admin_url('options-general.php?page=waraPay&tab=orders'); ?>"><?php echo __('取消','waraPayi18N');?></a>

    </div>
    <?php
    return;
}


$_GET['ordid'] = esc_sql($_GET['ordid']);

//-----------------------------------------------------------------------
//get data
//----------------------------------------------------------------------- 
$data = $wpdb->get_results("SELECT * FROM {$wpdb->orders} WHERE `ordid`={$_GET['ordid']} LIMIT 1;", ARRAY_A);
//print_r($data);


if (isset($data[0])) {
    $data = $data[0];
}

$ordermeta = $wpdb->get_results(
    "SELECT * FROM {$wpdb->ordersmeta} WHERE `orders_id`={$_GET['ordid']};",
    ARRAY_A
);

foreach ($ordermeta as $meta) {
    $data[$meta['meta_key']] = $meta['meta_value'];
}


$htmls = array(
    array('proid', __('商品编号','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('buynum', __('购买数量','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('series', __('商户订单号','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('platTradeNo', __('平台订单号','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('paygate', __('支付网关','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('aliacc', __('支付账号','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('username', __('用户名','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('ordname', __('收件人姓名','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('email', __('收件人邮箱','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('phone', __('收件人电话','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('postcode', __('收件人邮编','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('address', __('收件人地址','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('remarks', __('备注信息','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('message', __('客户留言','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('otime', __('下单时间','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('stime', __('付款时间','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('referer', __('展示页面','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array(
        'status',
        '交易状态',
        'type'    => 'select',
        'options' => array(0 => __('待付款','waraPayi18N'), 1 => __('已付款','waraPayi18N')),
        'attrs'   => array('disabled' => 'disabled')
    ),
    array(
        'emailsend',
        __('发货状态','waraPayi18N'),
        'type'    => 'select',
        'options' => array(0 => __('未发货','waraPayi18N'), 1 => __('已发货','waraPayi18N')),
        'attrs'   => array('disabled' => 'disabled')
    ),
    array(
        'sendsrc',
        __('所发货源','waraPayi18N'),
        'type'  => 'textarea',
        'attrs' => array('class' => 'areatotext', 'disabled' => 'disabled')
    ),
);

if($data['status']=='1'){
	foreach($htmls as $k=>$v){
		if($v[0]=='emailsend'){
			$htmls[$k]['attrs']=array();
		}
		if($v[0]=='sendsrc'){
			$htmls[$k]['attrs']=array('class' => 'areatotext');
		}
	}
}

$translate = array();
foreach ($htmls as $item) {
    if (isset($item[0]) && isset($item[1])) {
        $translate[$item[0]] = $item[1];
    }
}

$translate['proid'] = __('商品编号','waraPayi18N');


foreach ($data as $k => $item) {
    break;
    ?>

    <div class="item"><label for="<?php echo $k; ?>" class="lbl"><?php if (isset($translate[$k])) {
                echo $translate[$k];
            } else {
                echo $k;
            } ?>：</label><input class="txt" type="text" name="<?php echo $k; ?>"
                                value="<?php echo $item; ?>"/></div>

<?php
}
?>

<div class="wrap waraPay_main_wrap">
    <?php include_once('tpl.tab.nav.php'); ?>

    <div id="icon-edit-pages" class="icon32"><br/></div>
    <h2><?php echo __('浏览订单','waraPayi18N');?></h2>


    <div id="waraPay_item_more" style="display:block">
        <div class="waraPay_item_more_main_wrap">

            <div class="waraPay_item_more_wrap">


                <form action="<?php echo admin_url(
                    'options-general.php?page=waraPay&tab=orders&action=edit&ordid=' . $_GET['ordid']
                ); ?>" method="post" id="waraPay_table_more_form" class="waraPay_table_form">
                    <?php


                    echo waraPay_label_input_html_with_data($htmls, 'waraPay_products_', $data);
					wp_nonce_field('waraPay_edit');
                    //wp_nonce_field('tpl.edit_product.php');
                    ?>

                     <input type="submit" name="submit" class="button-primary" value="<?php echo __('更新','waraPayi18N');?>"/>
                    <div class="clear"></div>
                </form>

            </div>
        </div>
    </div>
</div>