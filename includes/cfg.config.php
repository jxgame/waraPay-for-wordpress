<?php


require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php');
if (!defined('WP_ROOT')) {
    //define('WP_ROOT' , $_SERVER['DOCUMENT_ROOT']);
    define('WP_ROOT', ABSPATH);
}
if (!defined('DR')) {
    define('DR', DIRECTORY_SEPARATOR);
}
//include_once( WP_ROOT . DR . 'wp-load.php' );
global $wpdb;
////////////////////////////////constants section//////////////////////////////////////
if (!defined('WARAPAY_NAME_EN')) {
    define('WARAPAY_NAME_EN', 'waraPay');
}
if (!defined('WARAPAY_SETTINGS_PAGE')) {
    define('WARAPAY_SETTINGS_PAGE', WARAPAY_NAME_EN . "/includes/tpl.settings.php");
}
if (!defined('WARAPAY_MENU_SLUG')) {
    define('WARAPAY_MENU_SLUG', 'waraPay');
}
if (!defined('WARAPAY_SETTINGS_LINK')) {
    define('WARAPAY_SETTINGS_LINK', admin_url() . 'options-general.php?page=' . WARAPAY_MENU_SLUG);
}
if (!defined('WARAPAY_NAME')) {
    define('WARAPAY_NAME', 'waraPay Payment');
}
if (!defined('WARAPAY_AUTH')) {
    define('WARAPAY_AUTH', 'administrator');
}
if (!defined('WARAPAY_SETTINGS_TITLE')) {
    define('WARAPAY_SETTINGS_TITLE', WARAPAY_NAME . 'ControlPanel');
}
if (!defined('WARAPAY_BASENAME')) {
    define('WARAPAY_BASENAME', WARAPAY_NAME_EN . "/waraPay.php");
}
if (!defined('WARAPAY_DB_PREFIX')) {
    define('WARAPAY_DB_PREFIX', $wpdb->prefix . 'waraPay_');
}
if (!defined('WARAPAY_CHARSET')) {
    define('WARAPAY_CHARSET', get_bloginfo('charset'));
}
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('WARAPAY_ROOT')) {
    define('WARAPAY_ROOT', dirname(dirname(__FILE__)) . DS);
}
if (!defined('WARAPAY_INC')) {
    define('WARAPAY_INC', WARAPAY_ROOT . 'includes' . DS);
}
if (!defined('WARAPAY_URL')) {
    define('WARAPAY_URL', WP_PLUGIN_URL . "/" . WARAPAY_NAME_EN);
}
if (!defined('WARAPAY_IMG_URL')) {
    define('WARAPAY_IMG_URL', WARAPAY_URL . '/styles/images');
}
include_once('fnc.core.php');
include_once('fnc.api_core.php');
//////////////////////////////db section//////////////////////////////////////
$wpdb->prefix1            = $wpdb->prefix . 'waraPay_';
$wpdb->productsname      = 'products';
$wpdb->products          = $wpdb->prefix1 . 'products';
$wpdb->productsmeta      = $wpdb->prefix1 . 'products' . 'meta';
$wpdb->productsmetatype  = 'products';
$wpdb->ordersname        = 'orders';
$wpdb->orders            = $wpdb->prefix1 . 'orders';
$wpdb->ordersmeta        = $wpdb->prefix1 . 'orders' . 'meta';
$wpdb->ordersmetatype    = 'orders';
$wpdb->templatesname     = 'templates';
$wpdb->templates         = $wpdb->prefix1 . 'templates';
$wpdb->templatesmeta     = $wpdb->prefix1 . 'templates' . 'meta';
$wpdb->templatesmetatype = 'templatets';
$waraPay_tables             = array(
    $wpdb->productsname,
    $wpdb->ordersname,
    $wpdb->templatesname
);
waraPay_db_create();
date_default_timezone_set('UTC');
foreach ($waraPay_tables as $table) {
    $temp    = array();
    $tbl     = $wpdb->prefix1 . $table;
    $tmpData = $wpdb->get_results("SHOW FIELDS FROM $tbl;", ARRAY_A);
    foreach ($tmpData as $k => $arr) {
        $temp[] = $arr['Field'];
    }
    ${'waraPay_table_' . $table} = $temp;
    //$waraPay_table_pruducts
    //$waraPay_table_orders
    //$waraPay_table_templates
}

//############################################################################
 
