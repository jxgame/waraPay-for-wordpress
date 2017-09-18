<?php

############################################################################
define('SITE_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('DR', DIRECTORY_SEPARATOR);
############################################################################
require_once('../../../../wp-load.php');
error_reporting(0);
require_once('cfg.config.php');
header("Content-Type: text/plain; charset=" . WARAPAY_CHARSET);
############################################################################
//Die if load thfe page directly
if (empty($_REQUEST)) {
    die();
}
//Necessary security check
waraPay_security_check();
//stripslashe
$_REQUEST = stripslashes_deep($_REQUEST);
//FOR GET
//NEED: TABLE, FIELDS, ( PRIMARYKEY = INT )
$_dbLoader = array();
//Command fields
$_dbLoader['cmdfdsA'] = array(
    'action',
    'limit',
    'security_check',
    'fields',
    'asc_fields',
    'table',
    'where',
    'single',
    'fields_refer'
);
$_dbLoader['affdsA']  = array_diff_key($_REQUEST, array_flip($_dbLoader['cmdfdsA']));
if (isset($_REQUEST['table'])) {
    $_dbLoader['table'] = $_REQUEST['table'];
    $_dbLoader['wptbl'] = $wpdb->prefix . $_REQUEST['table'];
    //Current table fields , an Array.
    $_dbLoader['ctnfdsA']  = ${'waraPay_table_' . $_REQUEST['table']};
    $_dbLoader['products'] = $waraPay_table_products;
    //$_dbLoader['ords']		 = $waraPay_table_orders;
    //$_dbLoader['tpls']		 = $waraPay_table_templates;
    $_dbLoader['ctnfdsS'] = '';
    //To be affected db fields
    $_dbLoader['afdbfdsA'] = array_intersect_key($_REQUEST, array_flip($_dbLoader['ctnfdsA']));
    $_dbLoader['metafdsA'] = array_diff_key(
        $_REQUEST,
        $_dbLoader['afdbfdsA'],
        array_flip($_dbLoader['cmdfdsA'])
    );
    //$waraPay_refer_field
    $_dbLoader['referfd'] = '';
    //refer return array
    $_dbLoader['referfdA'] = '';
    //array to merge temp
    $_dbLoader['mergeA'] = '';
    //
}
if (isset($_REQUEST['where'])) {
    $_where              = preg_split('/=/', $_REQUEST['where']);
    $_dbLoader['wherek'] = $_where[0];
    if (isset($_where[1])) {
        $_dbLoader['wherev'] = $_where[1];
    }
}
//############################################################################
if (isset($_REQUEST['table']) && $_REQUEST['table'] !== '') {
    foreach ($_dbLoader['ctnfdsA'] as $key => $value) {
        $_dbLoader['ctnfdsS'] .= "$value,";
    }
    $_dbLoader['ctnfdsS'] = substr($_dbLoader['ctnfdsS'], 0, -1);
}
//allowed fileds first
if (isset($_REQUEST['fields']) && $_REQUEST['fields'] !== '') {
    if ($_REQUEST['fields'] !== '*') {
        $waraPay_asc_fields      = explode(',', $_REQUEST['fields']);
        $_dbLoader['ctnfdsA'] = $waraPay_asc_fields;
    }
}
//then the disallowed
if (isset($_REQUEST['asc_fields']) && $_REQUEST['asc_fields'] !== '') {
    $waraPay_asc_fields      = explode(',', $_REQUEST['asc_fields']);
    $_dbLoader['ctnfdsA'] = array_values(array_diff($_dbLoader['ctnfdsA'], $waraPay_asc_fields));
}
//refer parse
if (isset($_REQUEST['fields_refer']) && $_REQUEST['fields_refer'] !== '') {
    $_dbLoader['referfd'] = $_REQUEST['fields_refer'];
}
//var section
$arr_ret = array();
//global $wpdb;
$wpdb->query("SET time_zone = '" . waraPay_num2time(get_option('')) . "';");
############################################################################
//action list
if (isset($_REQUEST['action']) && $_REQUEST['action'] !== '') {
    switch ($_POST['action']) {
        case '78009':
            waraPay_get_data();
            break;
        case '78010':
            waraPay_add_data();
            break;
        case '78011':
            waraPay_add_data();
            break;
        case '78012':
            //waraPay_get_data_plus();
            //waraPay_get_refer_data();
            //waraPay_merge();
            break;
        case '78013':
            waraPay_api_update();
            break;
        case '78014':
            waraPay_get_data();
            break;
        case '78015':
            waraPay_update_data();
            break;
        case '78016':
            waraPay_insert_data();
            waraPay_get_data();
            break;
        case '78017':
            waraPay_delete_data();
            waraPay_get_data();
            break;
        case '78018':
            waraPay_copy_data();
            waraPay_get_data();
            break;
    }
}
//out put
$arr_ret = json_encode($arr_ret);
echo $arr_ret;
############################################################################
//functions section
function waraPay_merge()
{
    global $_dbLoader;
    global $arr_ret;
    foreach ($arr_ret['data'] as $key => $value) {
        foreach ($_dbLoader['mergeA'] as $key1 => $value1) {
            if ($value['proid'] == $value1->proid) {
                //ATTENTION:$value IS AN ARRAY,BUT $value1 IS AN OBJECT!!!
                //THAT'S B/C HERE SHOULD BE WRITTEN IN $value['proid'] == $value1->proid
                //OR WE CAN ADD A STATEMENT BEFORE THE IF STATEMENT LIKE $value1=(array)$value1
                $arr_ret['data'][$key] = array_merge($value, (array)$value1);
            }
        }
        if (!isset($arr_ret['data'][$key]['name'])) {
            $arr_ret['data'][$key]['name']  = __('该商品已不存在','waraPayi18N');
            $arr_ret['data'][$key]['price'] = __('未知','waraPayi18N');
        }
    }
}

function waraPay_get_refer_data($table = 1, $key = 1)
{
    global $wpdb, $_dbLoader;
    $sql= "	SELECT name,proid,price
			FROM $wpdb->products
			WHERE proid
			IN (SELECT proid FROM $wpdb->orders)
			;";
    $_dbLoader['mergeA'] = $wpdb->get_results($sql);
}

function waraPay_get_data()
{
    global $wpdb, $_dbLoader;
    global $arr_ret;
    if (isset($_REQUEST['fields_refer']) && $_REQUEST['fields_refer'] !== '') {
        waraPay_get_data_plus();
        return;
    }
    if (isset($_REQUEST['single'])) { //只读1条记录,即查看详情或编辑
        //$id = preg_split( '@\=@', $_REQUEST['where'] );
        $sql = "
		SELECT {$_dbLoader['ctnfdsS']} 
		FROM {$_dbLoader['wptbl']} 
		WHERE $_REQUEST[where]
		;";
    } else { //读N条记录
        $sql = "
		SELECT " . $_dbLoader['ctnfdsS'] . "
		FROM   " . $_dbLoader['wptbl'] . " 
		LIMIT  " . $_REQUEST['limit'] . ";";
    }
    $roret = $wpdb->get_results($sql);
    foreach ($roret as $value) { //$key=0,1,2,3...
        $arr_temp = array();
        foreach ($_dbLoader['ctnfdsA'] as $value1) { //$key1=0,1,2,3...
            $arr_temp[$value1] = $value->$value1;
        }
        $arr_ret['data'][] = $arr_temp;
    }
    if (isset($_REQUEST['single'])) { //只读1条记录,即查看详情或编
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //GET the add-on fields
        $metas = get_metadata(
            $wpdb->{$_dbLoader['table'] . 'metatype'},
            $_dbLoader['wherev'],
            '',
            true
        );
        //print_r($metas);
        //die();
        //Filter the JSON fields which is for ajax add-on
        if (!empty($metas)) {
            $metaCOMN = array();
            $metaJSON = array();
            foreach ($metas as $k => $v) {
                if (preg_match('@^\S+JSON$@', $k)) {
                    $tempA = json_decode($v[0], true);
                    if (!isset($tempA['transport']) || $tempA['transport'] == true) {
                        $metaJSON[] = $tempA;
                    }
                } else {
                    $metaCOMN[$k] = $v[0];
                }
            }
            $arr_ret['data'][0] = array_merge($arr_ret['data'][0], $metaCOMN);
            //uasort( $metaJSON, 'waraPay_metaJSON_sort', 'priority' );
            $metaJSON         = waraPay_sortByOneKey($metaJSON, 'priority', 10, true);
            $arr_ret['extra'] = $metaJSON;
        }
    }
    $row_count        = $wpdb->get_results("SELECT COUNT(*) FROM {$_dbLoader['wptbl']};");
    $arr_ret['count'] = $row_count;
}

function waraPay_get_data_plus()
{
    global $wpdb, $_dbLoader;
    global $arr_ret;
    $sql      = waraPay_get_refer_sql($_dbLoader['referfd']);
    $roret = $wpdb->get_results($sql);
    foreach ($roret as $value) { //$key=0,1,2,3...
        $arr_temp = array();
        foreach ($_dbLoader['ctnfdsA'] as $value1) { //$key1=0,1,2,3...
            $arr_temp[$value1] = stripslashes($value->$value1); //$arr_temp[price] = 3.00...
        }
        foreach ($_dbLoader['referfdA'] as $value1) { //$key1=0,1,2,3...
            $arr_temp[$value1] = stripslashes($value->$value1); //$arr_temp[price] = 3.00...
        }
        $arr_ret['data'][] = $arr_temp;
    }
    $row_count        = $wpdb->get_results("SELECT COUNT(*) FROM {$_dbLoader['wptbl']};");
    $arr_ret['count'] = $row_count;
}

function waraPay_add_data()
{
    global $wpdb, $_dbLoader;
    $in = array();
    foreach ($_dbLoader['afdbfdsA'] as $key => $value) {
        $in[$key] = $value;
    }
    $wpdb->insert($_dbLoader['wptbl'], $in);
    die();
}

function waraPay_update_data()
{
    global $wpdb, $_dbLoader;
    //HERE IS NECESSARY FOR THAT $wpdb->update WILL REGARG THE / AS THE ENTITIES
    //IF USE THE $waraPay_db_fields IN SQL DIRECTELY, WE'LL NOT STRIOSLASHES!!
    //$a = preg_split('/=/',$_REQUEST['where']);
    $wh = array($_dbLoader['wherek'] => $_dbLoader['wherev']);
    $wt = array('%d');
    $wpdb->update($_dbLoader['wptbl'], $_dbLoader['afdbfdsA'], $wh, null, $wt);
    //Update the metas
    foreach ($_dbLoader['metafdsA'] as $k => $v) {
        update_metadata($wpdb->{$_dbLoader['table'] . 'metatype'}, $_dbLoader['wherev'], $k, $v);
    }
    die();
}

function waraPay_api_update()
{
    global $_dbLoader;
    foreach ($_dbLoader['affdsA'] as $key => $value) {
        //$waraPay_db_fields[$key] = esc_html($value);
    }
    $JSON = json_encode($_dbLoader['affdsA']);
    update_option('waraPay_settings_api', $JSON);
}

//function waraPay_insert_data(){
//	global $wpdb, $_dbLoader;
//	
//	$in = array( $_REQUEST['where']=>'' );
//
//	
//	$wt = array( '%d' );
//	
//	$wpdb->insert( $_dbLoader['wptbl'], $in, $wt);
//}
function waraPay_insert_data()
{
    global $wpdb, $_dbLoader;
    $in  = array($_REQUEST['where'] => '');
    $wt  = array('%d');
    $sql = "INSERT INTO {name} ({$_REQUEST['where']}) VALUES('aaa');";
    $wpdb->query($sql);
    //echo $wpdb->insert( $_dbLoader['wptbl'], $in, $wt);
}

function waraPay_copy_data()
{
    global $wpdb, $_dbLoader;
    $the_copy     = $wpdb->get_results("SELECT * FROM {$_dbLoader['wptbl']} WHERE $_REQUEST[where]");
    $the_copy     = (array)$the_copy[0];
    $the_copy     = array_diff_key($the_copy, array('tplid' => '', 'proid' => '', 'ordid' => ''));
    $the_copy_key = array_keys($the_copy);
    foreach ($the_copy as &$value) {
        $value = "'" . addslashes($value) . "'";
    }
    $the_copy_key = waraPay_array_link($the_copy_key);
    $the_copy     = waraPay_array_link($the_copy);
    $wpdb->query("INSERT INTO {$_dbLoader['wptbl']} ($the_copy_key) VALUES($the_copy)");
}

function waraPay_delete_data()
{
    global $wpdb, $_dbLoader;
    $sql = "DELETE FROM {$_dbLoader['wptbl']} WHERE $_REQUEST[where]";
    $wpdb->query($sql);
    //delete_metadata( $wpdb->{''.$_dbLoader['table'].'metatype'}, $_dbLoader['wherev']);
    $tbl_meta = $wpdb->{$_dbLoader['table'] . 'meta'};
    $objk     = $_dbLoader['table'] . '_id';
    $sql      = "DELETE FROM $tbl_meta WHERE $objk = {$_dbLoader['wherev']}";
    $wpdb->query($sql);
}

//############################################################################
function waraPay_array_link($arr)
{
    return substr(array_reduce($arr, 'waraPay_array_link_callback'), 1);
}

function waraPay_array_link_callback($v1, $v2)
{
    return $v1 . ',' . $v2;
}

function waraPay_get_refer_sql($arr_refer)
{
    return waraPay_sql_maker(waraPay_refer_parser($arr_refer));
}

function waraPay_refer_parser($arr_refer)
{
    foreach ($arr_refer as $key => $value) {
        $arr_temp;
        $val_temp            = preg_split('/\|/', $value);
        $arr_temp['table']   = $val_temp[0];
        $arr_temp['refer']   = $val_temp[1];
        $arr_temp['fields']  = preg_split('/,/', $val_temp[2]);
        $arr_refer_ret[$key] = $arr_temp;
    }
    return $arr_refer_ret;
}

function waraPay_sql_maker($arr_mix)
{
    global $wpdb, $_dbLoader;
    $select = '';
    $from   = '';
    $join   = '';
    $on     = '';
    $prfix  = $wpdb->prefix . 'waraPay_';
    foreach ($arr_mix as $key => $items) {
        foreach ($items['fields'] as $field) {
            $select .= $prfix . $items['table'] . '.' . $field . ',';
        }
    }
    $select                     = substr($select, 0, -1);
    $from                       = $prfix . $arr_mix[0]['table'];
    $join                       = $prfix . $arr_mix[1]['table'];
    $on                         = $from . '.' . $arr_mix[0]['refer'] . '=' . $join . '.' . $arr_mix[1]['refer'];
    $_dbLoader['referfdA'] = $arr_mix[1]['fields'];
    $ret                        = "
	SELECT $select
	FROM $from
	LEFT OUTER JOIN $join
	ON $on
	;";
    return $ret;
}

