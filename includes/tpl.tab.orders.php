<?php
require_once('cls.listTable_orders.php');
$testListTable = new Orders_List_Table();
$testListTable->prepare_items();

if (isset($_GET['ordid'])) {
    return;
}
?>

<div class="wrap waraPay_main_wrap">
    <?php include_once('tpl.tab.nav.php'); ?>
    <div id="icon-users" class="icon32"><br/></div>
    <h2><?php echo __('订单管理','waraPayi18N');?></h2>
    <div class="waraPay_viewwrap"><?php $testListTable->views(); ?></div>
    <style>
        .column-name{width:30%;}
        .column-series{width:15%;}
        .column-num{text-align:left !important}
    </style>
    <form id="movies-filter" method="get" action="options-general.php?page=waraPay&tab=orders">
        <?php $testListTable->search_box(__('搜索','waraPayi18N'), 'pro_input_id');?><?php

		?>
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo intval($_REQUEST['page']) ?>"/>
        <!-- Now we can render the completed list table -->
        <?php $testListTable->display() ?>
    </form>

</div>
