<?php
require_once('cls.listTable_products.php');
$testListTable = new Products_List_Table();

//Fetch, prepare, sort, and filter our data...
$testListTable->prepare_items();

if (isset($_GET['proid'])) {
    return;
}

?>

<div class="wrap waraPay_main_wrap">
    <?php include_once('tpl.tab.nav.php'); ?>
    <div id="icon-index" class="icon32"><br/></div>
    <h2><?php echo __('商品仓库','waraPayi18N');?></h2>

    <div class="waraPay_toobar01">

        <a href="?page=waraPay&tab=products&action=edit&proid" class="button-secondary"><?php echo __('添加商品','waraPayi18N');?></a>
    </div>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <style>

        .column-name{
            width:30%;
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
