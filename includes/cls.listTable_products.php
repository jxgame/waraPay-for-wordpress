<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Products_List_Table extends WP_List_Table{

    var $mkey = 'proid';
    var $mname = 'products';
    function __construct()
    {
        global $status, $page;
        //Set parent defaults
        parent::__construct(
            array(
                 'singular' => __('商品','waraPayi18N'), //singular name of the listed records
                 'plural'   => __('商品','waraPayi18N'), //plural name of the listed records
                 'ajax'     => false //does this table support ajax?
            )
        );
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'name':
            case 'price':
            case 'num':
            case 'protype':
            case 'shortcode':
            case 'description':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_name($item)
    {
        //Build row actions
        $actions = array(
            'edit'   => sprintf('<a href="?page=%s&action=%s&proid=%s">'.__('编辑','waraPayi18N').'</a>', $_REQUEST['page'], 'edit', $item['ID']),
            'delete' => '<a href="' . add_query_arg(
                  array('tab' => $this->mname, 'action' => 'delete', $this->mkey => $item['ID'])
              ) . '">'.__('删除','waraPayi18N').'</a>'
        );
        //Return the title contents
        return sprintf(
            '%1$s <span style="color:silver">(ID:%2$s)</span>%3$s',
            /*$1%s*/
            $item['name'],
            /*$2%s*/
            $item['ID'],
            /*$3%s*/
            $this->row_actions($actions)
        );
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/
            $this->_args['singular'], //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/
            $item['ID'] //The value of the checkbox should be the record's id
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb'          => '<input type="checkbox" />', //Render a checkbox instead of text
            'name'        => __('商品名称','waraPayi18N'),
            'price'       => __('单价','waraPayi18N'),
            'num'         => __('剩余数量','waraPayi18N'),
            'protype'     => __('类型','waraPayi18N'),
            'shortcode'   => __('短代码','waraPayi18N'),
            'description' => __('简要描述','waraPayi18N'),
        );
        return $columns;
    }


    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'      => array('name', true), //true means its already sorted
            'price'     => array('price', false),
            'num'       => array('num', false),
            'protype'   => array('protype', false),
            'shortcode' => array('shortcode', false),
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }


    function process_bulk_action()
    {
        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {
            include_once('tpl.edit_product.php');
        } elseif ('edit' === $this->current_action()) {
            include_once('tpl.edit_product.php');
        }
    }

    function prepare_items()
    {

        $per_page = 5;
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $current_page = $this->get_pagenum();
        $start        = ($current_page - 1) * $per_page;
        $end          = $start + $per_page;
        global $wpdb;
        $data = $wpdb->get_results(
            "SELECT *,`name`as`title`,`proid`as`ID`,CONCAT('[zfb id=',proid,']')as`shortcode` FROM `{$wpdb->products}`;",
            ARRAY_A
        );

        foreach ($data as $k => $item) {
            if (isset($item['proid'])) {
                $data[$k]['protype'] = get_metadata($wpdb->productsmetatype, $item['proid'], 'protype', 1);
            }
        }

        function usort_reorder($a, $b)
        {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'shortcode'; //If no sort, default to title
            $order   = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result  = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
        }

        usort($data, 'usort_reorder');
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);
        $this->items = $data;
        $this->set_pagination_args(
            array(
                 'total_items' => $total_items, //WE have to calculate the total number of items
                 'per_page'    => $per_page, //WE have to determine how many items to show on a page
                 'total_pages' => ceil($total_items / $per_page) //WE have to calculate the total number of pages
            )
        );
    }
}
