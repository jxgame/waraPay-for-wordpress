<?php
/**
 *parse the $attr
 *
 *
 *
 *
 *
 */
//############################################################################
require_once('cfg.config.php');
require_once('cls.dbparser.php');
//global $wpdb;
//global $WARAPAY_DB_PREFIX;
//global $waraPay_tpl_show_g78009;
global $waraPay_in_class_proid;
//############################################################################
if (isset($atts)) {
    extract(shortcode_atts(array('id' => ''), $atts));
    if (!isset($id) || $id == '') {
        $waraPay_show_return = __('该商品不存在','waraPayi18N');
        return;
    }
    $waraPay_in_class_proid = $id;
} else {
    $id = $waraPay_in_class_proid;
}
$id = (isset($id) && $id !== '') ? $id : 0;
//CALL THE CLASS TO PARSE THE $ID(PROID)
$output                = new waraPay_db_parser($id);
$waraPay_show_return = $output->ret;



