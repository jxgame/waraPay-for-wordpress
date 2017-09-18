<?php

defined('ABSPATH') || die();

global $wpdb;
global $waraPay_table_templates;


//-----------------------------------------------------------------------
// upadte
//----------------------------------------------------------------------- 	
if (isset($_POST['submit'])) {
    //unset($_POST['_wpnonce']);
    //unset($_POST['_wp_http_referer']);
    foreach ($_POST as $k => $v) {
        if (!in_array($k, $waraPay_table_templates)) {
            unset($_POST[$k]);
        }
    }
    $wpdb->update($wpdb->templates, $_POST, array('tplid' => intval($_REQUEST['tplid'])));
	echo "<script>alert('SUCCESS');</script>";
}

//-----------------------------------------------------------------------
//insert
//----------------------------------------------------------------------- 
if (empty($_POST) && empty($_GET['tplid'])) {
    $wpdb->insert($wpdb->templates, array('tplname' => __('未命名模版','waraPayi18N')));
    //echo $wpdb->insert_id;
    $_GET['tplid'] = $wpdb->insert_id;
}

//-----------------------------------------------------------------------
//delete
//----------------------------------------------------------------------- 
if (isset($_GET['action']) && $_GET['action'] == 'delete' && $_GET['tplid']) {
    $_GET['tplid'] = esc_sql($_GET['tplid']);
    if (isset($_GET['sure'])) {
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->templates} WHERE `tplid`=%d;", $_GET['tplid']));
        return;
    }

    ?>
    <div style="margin:30px;"><?php echo __('删除后数据不可恢复, 你确定要这样做吗?','waraPayi18N');?>
        <a class="button-primary" href="<?php echo admin_url(
            'options-general.php?page=waraPay&action=delete&tab=templates&tplid=' . $_GET['tplid'] . '&sure'
        ); ?>"><?php echo __('确定','waraPayi18N');?></a>
        <a class="button-secondary" href="<?php echo admin_url('options-general.php?page=waraPay'); ?>"><?php echo __('取消','waraPayi18N');?><</a>

    </div>
    <?php
    return;
}


$_GET['tplid'] = esc_sql($_GET['tplid']);

//-----------------------------------------------------------------------
//get data
//----------------------------------------------------------------------- 
$data = $wpdb->get_results("SELECT * FROM {$wpdb->templates} WHERE `tplid`={$_GET['tplid']} LIMIT 1;", ARRAY_A);
//print_r($data);

if (isset($data[0])) {
    $data = $data[0];
}


$htmls = array(
    array(
        'tplid',
        __('模版编号','waraPayi18N'),
        'attrs' => array('style' => 'width:30%;margin:auto auto 10px 20px', 'readonly' => 'readonly')
    ),
    array('tplname', __('模版名称','waraPayi18N'), 'attrs' => array('style' => 'width:30%;margin:auto auto 10px 20px')),
    array('tpldescription', __('模版描述','waraPayi18N'), 'attrs' => array('style' => 'width:70%;margin:auto auto 10px 20px')),
    array(
        'tplcss',__('模版CSS代码:(请自行在代码中添加&lt;style&gt;标签,可以使用链接关系)','waraPayi18N'),
        'type'  => 'textarea',
        'attrs' => array('class' => 'waraPay_tpl_css')
    ),
    array(
        'tplhtml',__('模版HTML代码:(请直接在&lt;div&gt;标签下写代码)','waraPayi18N'),
        'type'  => 'textarea',
        'attrs' => array('class' => 'waraPay_tpl_html')
    ),
    array(
        'tpljs',
		__('模版javascript代码:(请自行在代码中添加&lt;script&gt;标签,可以使用脚本路径)','waraPayi18N'),
        'type'  => 'textarea',
        'attrs' => array('class' => 'waraPay_tpl_js')
    ),
);
$htmls = apply_filters('waraPay_templates_htmls', $htmls);


?>

<div class="wrap waraPay_main_wrap">
    <?php include_once('tpl.tab.nav.php'); ?>
    <div id="icon-edit-pages" class="icon32"><br/></div>
    <h2><?php echo __('编辑模版','waraPayi18N');?></h2>


    <div id="waraPay_item_more" style="display:block">
        <div class="waraPay_item_more_main_wrap">
            <div class="waraPay_item_more_wrap">

                <form action="<?php echo admin_url(
                    'options-general.php?page=waraPay&tab=templates&action=edit&tplid=' . $_GET['tplid']
                ); ?>" method="post" id="waraPay_table_more_form" class="waraPay_table_form_templates">
                    <?php
                    echo waraPay_label_input_html_with_data($htmls, 'waraPay_templates_', $data);

                    wp_nonce_field('waraPay_edit');
                    ?>
                    <div class="clear"></div>


                    <input type="submit" name="submit" class="button-primary" value="<?php echo __('更新','waraPayi18N');?>"/>

                    <div class="clear"></div>

                </form>


            </div>
        </div>
    </div>
</div>