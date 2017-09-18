<?php


/**
 * USAGE:
 *
 * require_once( 'cls.info.php' );
 *
 * $pro = new  waraPay_product();
 * $proInfo = $pro->get_info(1);
 * print_r($proInfo);

 */
require_once 'cfg.config.php';
//$pro = new  waraPay_product();
//
//$proInfo = $pro->get_info(26);
//$pro->set('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb','bb');
//$pro->del('aaaaaaaaaaaaaaaaaa');
//
//print_r($proInfo);
/////////////////////////////////////////////////////////////////////////////////////
class waraPay_info
{

    //var $prefix = '';
    //var $wpdb   = '';
    var $info = '';

    var $priid = '';

    var $curid = '';

    var $tbl = '';

    function waraPay_info($id = null)
    {
        global $wpdb;
        //$wpdb = $wpdb;
        //$wpdb->prefix = $wpdb->prefix;
        if (!empty($id)) {
            $this->curid = $id;
        }
    }

    function get_info($id = null, $bln_array = true)
    {
        global $wpdb;
        $id = (empty($id)) ? $this->curid : $id;
        if (intval($id) != $id) {
            die(waraPay_show_tip('SIGN_INVALID'));
        }
        $sql = "SELECT *
				FROM {$wpdb->prefix}{$this->fields}
				WHERE {$this->priid} = $id
				;";
        $ret = $wpdb->get_results($sql);
        if (empty($ret[0])) {
            return false;
        }
        if ($bln_array == true) {
            $ret[0] = (array)$ret[0];
        }
        $ret[0] = waraPay_stripslashes($ret[0]);
        /////////////////////////////////////////////////////////////////////////////////////
        $metas = get_metadata($wpdb->{$this->tbl . 'metatype'}, $id, '', true);
        //foreach($metas as &$meta)
        //$meta = $meta[0];
        //Filter the JSON fields which is for ajax add-on
        if (!empty($metas)) {
            $metaCOMN = array();
            foreach ($metas as $k => $v) {
                if (!preg_match('@^\S+JSON$@', $k)) {
                    $metaCOMN[$k] = $v[0];
                    //echo $metaCOMN[$k].'</br>';
                    //$ret[0][$k] = $v[0];
                }
            }
            $ret[0] = array_merge($ret[0], $metaCOMN);
        }
        /////////////////////////////////////////////////////////////////////////////////////
        if ($this->tbl == 'orders') {
            //print_r($ret[0] );
            //die();
        }
        $ret[0]      = array_map('maybe_unserialize', $ret[0]);
        $this->info  = $ret[0];
        $this->curid = $id;
        return $this->info;
    }

    function set($field = null, $val = null, $kvp = null)
    {
        global $wpdb;
        global ${'waraPay_table_' . $this->tbl};
        $args = func_get_args();
        (isset($args[2]) && $where = $args[2]) || $where = $this->priid;
        (isset($args[3]) && $where_val = $args[3]) || $where_val = $this->curid;
        $where     = $this->priid;
        $where_val = $this->curid;
        if (!empty($kvp)) {
            $fields = $this->splitFields($kvp);
            $dbs    = $fields['dbs'];
            $metas  = $fields['metas'];
            //print_r($metas);
            if (!empty($dbs)) {
                $wpdb->update(
                    $wpdb->{$this->tbl},
                    $dbs,
                    array($this->priid => $this->curid),
                    array(),
                    array('%d')
                );
            }
            if (!empty($metas)) {
                foreach ($metas as $k => $v) {
                    $this->sets($k, $v);
                }
            }
            //$wpdb->update($wpdb->{$this->tbl.'meta'}, $metas, array($this->tbl.'_id'=>$this->curid),array(),array('%d'));
            return; ///////////////////////////////////////////////////////
        }
        if (in_array($field, ${'waraPay_table_' . $this->tbl})) {
            $sql = "
				UPDATE {$wpdb->prefix}{$this->fields}
				SET $field = '$val'
				WHERE $where = '$where_val'
				;";
            $ret = $wpdb->query($sql);
        } elseif ($where == $this->priid && $where_val == $this->curid) {
            $ret = update_metadata(
                $wpdb->{$this->tbl . 'metatype'},
                $this->curid,
                $field,
                $val
            );
        }
        if ($ret) {
            $this->info[$field] = $val;
            return true;
        } else {
            return false;
        }
    }

    function get($field)
    { // $field, $whereK, $whereV . eg: price, proid, 1
        global $wpdb;
        global ${'waraPay_table_' . $this->tbl};
        $args = func_get_args();
        if (!empty($this->curid)) {
            $this->get_info($this->curid);
        }
        if (count($args) == 1) {
            return (isset($this->info[$field])) ? $this->info[$field] : null;
        }
        (isset($args[1]) && $where = $args[1]) || $where = $this->priid;
        (isset($args[2]) && $where_val = $args[2]) || $where_val = $this->curid;
        $sql = "
			SELECT $field
			FROM {$wpdb->prefix}{$this->fields}
			WHERE $where = '$where_val'
		;";
        if (in_array($field, ${'waraPay_table_' . $this->tbl})) {
            $row_ret = $wpdb->get_results($sql);
        } elseif ($where == $this->priid && $where_val == $this->curid) {
            $row_ret = get_metadata(
                $wpdb->{$this->tbl . 'metatype'},
                $this->curid,
                $field,
                ''
            );
        }
        if (empty($row_ret)) {
            return false;
        }
        return $row_ret[0]->$field;
    }

    function del($field, $val = '')
    {
        global $wpdb;
        return delete_metadata(
            $wpdb->{$this->tbl . 'metatype'},
            $this->curid,
            $field,
            $val
        );
    }

    function exist($field, $val)
    {
        global $wpdb;
        $sql     = "
			SELECT *
			FROM {$wpdb->prefix}{$this->fields}
			WHERE $field = '$val'
		;";
        $row_ret = $wpdb->get_results($sql);
        if (empty($row_ret)) {
            return false;
        }
        return true;
    }

    //Add or update a meta
    function sets($field, $default = null)
    {
        global $wpdb;
        add_metadata($wpdb->{$this->tbl . 'metatype'}, $this->curid, $field, $default, true);
        return update_metadata(
            $wpdb->{$this->tbl . 'metatype'},
            $this->curid,
            $field,
            $default
        );
    }

    //insert
    function insert($kvp)
    {
        global $wpdb;
        $fields = $this->splitFields($kvp);
        $dbs    = $fields['dbs'];
        $metas  = $fields['metas'];
        $wpdb->insert($wpdb->{$this->tbl}, $dbs);
        $this->curid = $wpdb->insert_id; 
        //$this->refresh();
        foreach ($metas as $key => $value) {
            update_metadata(
                $wpdb->{$this->tbl . 'metatype'},
                $this->curid,
                $key,
                $value
            );
        }
        return $this->curid;
    }

    //refresh
    function refresh()
    {
        $this->get_info($this->curid);
    }

    //split
    function splitFields($kvp)
    {
        global $wpdb;
        global ${'waraPay_table_' . $this->tbl};
        $dbfields = array();
        $metas    = array();
        foreach ($kvp as $k => $v) {
            if (in_array($k, ${'waraPay_table_' . $this->tbl})) {
                $dbfields[$k] = $v;
            } else {
                $metas[$k] = $v;
            }
        }
        return array('dbs' => $dbfields, 'metas' => $metas);
    }
}

/////////////////////////////////////////////////////////////////////////////////////
if (!class_exists('waraPay_product')):
    class waraPay_product extends waraPay_info{

        var $tbl = 'products';
        var $fields = 'waraPay_products';
        var $priid = 'proid';

        function waraPay_product($id = null){
            if (!empty($id)) {
                $this->curid = $id;
            }
        }
    }
endif;
if (!class_exists('waraPay_order')):
    class waraPay_order extends waraPay_info
    {

        var $tbl = 'orders';

        var $fields = 'waraPay_orders';

        var $priid = 'ordid';

        function waraPay_order($id = null)
        {
            if (!empty($id)) {
                $this->curid = $id;
            }
        }
    }
endif;
if (!class_exists('Alipay_Ads')):
    class Alipay_Ads extends waraPay_product
    {

        var $adfield = 'ArrInfo_ADP';

        function Alipay_Ads($id)
        {
            $this->curid = $id;
        }

        function update($opt)
        {
            $this->sets($this->adfield, '');
        }

        function add($ad)
        {
            $oldads = $this->get($this->adfield);
            if (!$this->isNewAd($ad, $oldads)) {
                return;
            }
            $oldads = (array)$oldads;
            array_push($oldads, $ad);
            $this->sets($this->adfield, $oldads);
        }

        function isNewAd($ad, $oldads)
        {
            if (empty($oldads)) {
                return true;
            }
            $newId = $ad['ordid'];
            foreach ($oldads as $v) {
                if (isset($v['ordid'])) {
                    $oldIds[] = $v['ordid'];
                }
            }
            $oldIds = (array)$oldIds;
            return (in_array($newId, $oldIds)) ? false : true;
        }
    }
endif;
 
