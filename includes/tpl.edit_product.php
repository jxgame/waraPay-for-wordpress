<?php

defined('ABSPATH') || die();

global $wpdb;
global $waraPay_table_products;


//-----------------------------------------------------------------------
// upadte
//----------------------------------------------------------------------- 	
if (isset($_POST['submit'])) {
    unset($_POST['_wpnonce']);
    unset($_POST['submit']);
    unset($_POST['_wp_http_referer']);
    //$metas = array();
    foreach ($_POST as $k => $v) {
        if (!in_array($k, $waraPay_table_products)) {
            unset($_POST[$k]);
            //$metas[$k] = $v;
            update_metadata($wpdb->productsmetatype, intval($_REQUEST['proid']), $k, $v);
        }
    }
    $wpdb->update($wpdb->products, $_POST, array('proid' => intval($_REQUEST['proid'])));
	echo "<script>alert('SUCCESS');</script>";
}

//-----------------------------------------------------------------------
//insert
//----------------------------------------------------------------------- 
if (empty($_POST) && empty($_GET['proid'])) {
    $wpdb->insert($wpdb->products, array('name' =>  __('未命名商品','waraPayi18N')));
    //echo $wpdb->insert_id;
    $_GET['proid'] = $wpdb->insert_id;
    $wpdb->show_errors();
}

//-----------------------------------------------------------------------
//delete
//----------------------------------------------------------------------- 
if (isset($_GET['action']) && $_GET['action'] == 'delete' && $_GET['proid']) {
    $_GET['proid'] = esc_sql($_GET['proid']);
    if (isset($_GET['sure'])) {
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->products} WHERE `proid`=%d;", $_GET['proid']));
        return;
    }

    ?>
    <div style="margin:30px;"><?php echo __('删除后数据不可恢复, 你确定要这样做吗?','waraPayi18N');?>
        <a class="button-primary" href="<?php echo add_query_arg(array('sure' => '')); ?>"><?php echo __('确定','waraPayi18N');?></a>
        <a class="button-secondary" href="<?php echo admin_url('options-general.php?page=waraPay'); ?>"><?php echo __('取消','waraPayi18N');?></a>

    </div>
    <?php
    return;
}


$_GET['proid'] = esc_sql($_GET['proid']);

//-----------------------------------------------------------------------
//get data
//----------------------------------------------------------------------- 
$data = $wpdb->get_results("SELECT * FROM {$wpdb->products} WHERE `proid`={$_GET['proid']} LIMIT 1;", ARRAY_A);
$meta = $wpdb->get_results(
    "SELECT `meta_key`,`meta_value` FROM {$wpdb->productsmeta} WHERE `products_id`={$_GET['proid']};"
);

foreach ($meta as $k => $item) {
    $data[0][$item->meta_key] = $item->meta_value;
}


//$data = array_merge($data,$meta);

//print_r($data);

if (isset($data[0])) {
    $data = $data[0];
}
$data['buylink'] = get_bloginfo('url') . '/wp-content/plugins/waraPay/includes/tpl.cart.php?proid=' . $data['proid'];

if (!isset($data['autosrc'])) {
    $data['autosrc'] = '';
}

$htmls = array(
    array('proid',  __('商品编号','waraPayi18N'), 'type' => 'hidden'),
    array('name',  __('商品名称','waraPayi18N')),
    array(
        'protype',
         __('商品类型','waraPayi18N'),
        'type'    => 'select',
        'options' => array(
            'CUSTOM'  =>  __('普通实物','waraPayi18N'),
            'VIRTUAL' =>  __('普通虚拟','waraPayi18N'),
            'ADP'     =>  __('广告位','waraPayi18N'),
            'LINK'    =>  __('友情链接','waraPayi18N')
        ),
        'attrs'   => array('class' => 'waraPay_select_protype')
    ),
    array('price',  __('商品价格','waraPayi18N')),
    array('pricePerDay',  __('每日单价','waraPayi18N'), 'type' => 'hidden', 'attrs' => array('class' => 'waraPay_multiPrice')),
    array('pricePerWeek',  __('每周单价','waraPayi18N'), 'type' => 'hidden', 'attrs' => array('class' => 'waraPay_multiPrice')),
    array('pricePerMonth',  __('每月单价','waraPayi18N'), 'type' => 'hidden', 'attrs' => array('class' => 'waraPay_multiPrice')),
    array('pricePerQuarter',  __('每季单价','waraPayi18N'), 'type' => 'hidden', 'attrs' => array('class' => 'waraPay_multiPrice')),
    array('pricePerYear',  __('每年单价','waraPayi18N'), 'type' => 'hidden', 'attrs' => array('class' => 'waraPay_multiPrice')),
    array('description',  __('商品描述','waraPayi18N')),
    array('weight',  __('商品净重(kg)','waraPayi18N')),
    array('snum',  __('已售数量','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('num',  __('剩余数量','waraPayi18N')),
    array('images',  __('商品图片地址','waraPayi18N')),
    array('download',  __('下载链接','waraPayi18N')),
    array('zipcode',  __('解压密码','waraPayi18N')),
    array('tags',  __('商品标签(,)','waraPayi18N')),
    array(
        'spfre',
         __('买家承担运费','waraPayi18N'),
        'type'    => 'select',
        'options' => array(0 => __('否','waraPayi18N'), 1 => __('是','waraPayi18N')),
        'attrs'   => array('class' => 'waraPay_select_spfre')
    ),
    array('freight',  __('运费价格','waraPayi18N'), 'attrs' => array('class' => 'waraPay_select_spfre_rel')),
    array('location',  __('商品所在地','waraPayi18N')),
    array('atime',  __('商品添加日期','waraPayi18N'), 'attrs' => array('readonly' => 'readonly')),
    array('btime',  __('商品上架时间','waraPayi18N')),
    array('etime',  __('商品下架时间','waraPayi18N')),
    array(
        'promote',
         __('开启促销','waraPayi18N'),
        'type'    => 'select',
        'options' => array(0 => __('关闭','waraPayi18N'), 1 => __('开启','waraPayi18N')),
        'attrs'   => array('class' => 'waraPay_select_promote')
    ),
    array(
        'protime',
         __('开启每日促销','waraPayi18N'),
        'type'    => 'select',
        'options' => array(0 => __('关闭','waraPayi18N'), 1 => __('开启','waraPayi18N')),
        'attrs'   => array('class' => 'waraPay_select_protime 				waraPay_select_promote_rel')
    ),
    array(
        'probdate',
         __('促销开始日期','waraPayi18N'),
        'attrs' => array('class' => 'waraPay_select_promote_rel waraPay_select_promote_rel')
    ),
    array(
        'probtime',
         __('促销开始时间','waraPayi18N'),
        'attrs' => array('class' => 'waraPay_select_protime_rel waraPay_select_promote_rel')
    ),
    array(
        'proedate',
         __('促销结束日期','waraPayi18N'),
        'attrs' => array('class' => 'waraPay_select_promote_rel waraPay_select_promote_rel')
    ),
    array(
        'proetime',
         __('促销结束时间','waraPayi18N'),
        'attrs' => array('class' => 'waraPay_select_protime_rel waraPay_select_promote_rel')
    ),
    array(
        'discountb',
        __('促销折扣','waraPayi18N'),
        'type'    => 'select',
        'options' => array(0 => __('关闭','waraPayi18N'), 1 => __('开启','waraPayi18N')),
        'attrs'   => array('class' => 'waraPay_select_discountb waraPay_select_promote_rel')
    ),
    array(
        'discount',
        __('折扣比率','waraPayi18N'),
        'attrs' => array('class' => 'waraPay_select_discountb_rel waraPay_select_promote_rel')
    ),
    array('tplid',  __('模版选择','waraPayi18N'),),
    array(
        'autosend',
        __('启用自动货源列表','waraPayi18N'),
        'type'    => 'select',
        'options' => array(0 => __('关闭','waraPayi18N'), 1 => __('开启','waraPayi18N')),
        'attrs'   => array('class' => 'waraPay_select_autosend')
    ),
    array('autosep', __('货源分隔符','waraPayi18N'), 'attrs' => array('class' => 'waraPay_select_autosend_rel')),
    array(
        'autosrc',
        'html' => '<div style="float:none;clear:both;width:100%;">
<label for="autosrc" style="float:left;padding-left:2.5%;width:100%">'.__("虚拟物品货源&nbsp;&nbsp;&nbsp;&nbsp;(如果货源文本是每行一个条目,请将\'货源分隔符\'留空。一旦设置了分隔符，下面的货源文件就应该用该分隔符分隔)",'waraPayi18N').'</label>
<textarea name="autosrc" style="float:right;display:block;width:97.5%;min-width:97.5%;max-width:97.5%;min-height:70px;margin-left:2.5%" class="waraPay_select_autosend_rel">' . $data['autosrc'] . '</textarea>
</div>'
    ),
    array('buylink', __('商品快捷链接','waraPayi18N'), 'attrs' => array('class' => 'waraPay_prolink', 'title' => __('双击打开','waraPayi18N'))),
);

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
    <h2><?php echo __('编辑商品','waraPayi18N');?></h2>
    <?php include_once('tpl.tab.nav.php'); ?>


    <div id="waraPay_item_more" style="display:block">
        <div class="waraPay_item_more_main_wrap">

            <div class="waraPay_item_more_wrap">

                <?php

                ?>
                <form action="<?php echo admin_url(
                    'options-general.php?page=waraPay&action=edit&proid=' . $_GET['proid']
                ); ?>" method="post" id="waraPay_table_more_form" class="waraPay_table_form">
                    <?php


                    echo waraPay_label_input_html_with_data($htmls, 'waraPay_products_', $data);

                    wp_nonce_field('waraPay_edit');
                    ?>


                    <input type="submit" name="submit" class="button-primary" value="<?php echo __('更新','waraPayi18N');?>"/>

                    <div class="clear"></div>
                </form>

            </div>
        </div>
    </div>
</div>