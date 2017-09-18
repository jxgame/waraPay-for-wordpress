<?php
require_once('cls.listTable_templates.php');
$testListTable = new Templates_List_Table();
$testListTable->prepare_items();
if (isset($_GET['tplid'])) {
    return;
}
?>

<div class="wrap waraPay_main_wrap">
    <?php include_once('tpl.tab.nav.php'); ?>
    <div id="icon-edit-pages" class="icon32"><br/></div>
    <h2><?php echo __('模版管理','waraPayi18N');?></h2>

    <div class="waraPay_toobar01">
        <a href="?page=waraPay&action=edit&tab=templates&tplid" class="button-secondary"><?php echo __('添加模版','waraPayi18N');?></a>
        <a href="#" target="_blank" class="button-secondary"><?php echo __('下载模版','waraPayi18N');?></a>
    </div>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <style>
        .column-title{
            width:25%;
        }
        .column-num{
            text-align:left !important
        }

    </style>
    <form id="movies-filter" method="get">
        <?php $testListTable->search_box(__('搜索','waraPayi18N'), 'pro_input_id');?><?php




        ?>
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo intval($_REQUEST['page']) ?>"/>
        <!-- Now we can render the completed list table -->
        <?php $testListTable->display() ?>
    </form>

</div>
