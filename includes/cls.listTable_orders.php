<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Orders_List_Table extends WP_List_Table
{

    var $totals = array();
    var $mkey = 'ordid';
    var $mname = 'orders';
    function __construct()
    {
        global $status, $page;
        //Set parent defaults
        parent::__construct(
            array(
                 'singular' => __('订单','waraPayi18N'), //singular name of the listed records
                 'plural'   => __('订单','waraPayi18N'), //plural name of the listed records
                 'ajax'     => false //does this table support ajax?
            )
        );
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'name':
            case 'price':
            case 'buynum':
            case 'series':
            case 'otime':
            case 'status':
            case 'emailsend':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_name($item)
    {
        //Build row actions
        $actions = array(
            'edit'   => '<a href="' . add_query_arg(
                  array('tab' => $this->mname, 'action' => 'edit', $this->mkey => $item['ID'])
              ) . '">'.__('查看详情','waraPayi18N').'</a>',
            'delete' => '<a href="' . add_query_arg(
                  array('tab' => $this->mname, 'action' => 'delete', $this->mkey => $item['ID'])
              ) . '">'.__('删除','waraPayi18N').'</a>',
        );
        if (!current_user_can('level_10')) {
            unset($actions['delete']);
        }
        //Return the title contents
        return sprintf(
            '%1$s <span style="color:silver">(ID:%2$s,NO:%3$s)</span>%4$s',
            /*$1%s*/
            $item['name'],
            /*$2%s*/
            $item['proid'],
            /*$2%s*/
            $item['ordid'],
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
            'cb'     => '<input type="checkbox" />', //Render a checkbox instead of text
            'name'   => __('商品名称','waraPayi18N'),
            'price'  => __('单价','waraPayi18N'),
            'buynum' => __('购买数量','waraPayi18N'),
            'series' => __('内部订单号','waraPayi18N'),
            'otime'  => __('下单时间','waraPayi18N'),
            'status' => __('交易状态','waraPayi18N'),
            'emailsend' => __('发货状态','waraPayi18N'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'   => array('name', false), //true means its already sorted
            'price'  => array('price', false),
            'buynum' => array('buynum', false),
            'series' => array('series', false),
            'otime'  => array('otime', true),
            'status' => array('status', false),
            'emailsend' => array('emailsend', false),
        );
        return $sortable_columns;
    }

    function get_views()
    {
        $status       = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        $status_links = array();
        foreach ($this->totals as $type => $count) {
            if (!$count) {
                continue;
            }
            switch ($type) {
                case 'all':
                    $text = __('全部','waraPayi18N').' <span class="count">(' . $count . ')</span>';
                    break;
                case 'nopayment':
                    $text = __('未付款','waraPayi18N').' <span class="count">(' . $count . ')</span>';
                    break;
                case 'payed':
                    $text = __('付款成功','waraPayi18N').' <span class="count">(' . $count . ')</span>';
                    break;
            }
            $query = remove_query_arg('paged');
            if ('search' != $type) {
                $status_links[$type] = sprintf(
                    "<a href='%s' %s>%s</a>",
                    add_query_arg(array('filter' => $type), $query),
                    ($type == $status) ? ' class="current"' : '',
                    sprintf($text, number_format_i18n($count))
                );
            }
        }
        return $status_links;
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
            include_once('tpl.edit_order.php');
        } elseif ('edit' === $this->current_action()) {
            include_once('tpl.edit_order.php');
        }
    }

    function prepare_items()
    {

        $per_page = 10;
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
            "SELECT o.*,p.*,o.`ordid`as`ID` FROM `{$wpdb->orders}` as o INNER JOIN `{$wpdb->products}` as p ON o.`proid`=p.`proid`;",
            ARRAY_A
        );
        if (!waraPay_is_admin()) {
            global $user_ID;
            $meta = $wpdb->get_results(
                "SELECT `orders_id` FROM `{$wpdb->ordersmeta}` WHERE `meta_key`='order_user_id' AND `meta_value`=$user_ID;",
                ARRAY_A
            );
            if (isset($meta[0])) {
                foreach ($meta as $item) {
                    $ids[] = $item['orders_id'];
                }
                $ids  = implode(',', $ids);
                $data = $wpdb->get_results(
                    "SELECT o.*,p.*,o.`ordid`as`ID` FROM `{$wpdb->orders}` as o INNER JOIN `{$wpdb->products}` as p ON o.`proid`=p.`proid` WHERE o.`ordid` IN ($ids);",
                    ARRAY_A
                );
            } else {
                $data = array();
            }
            foreach ($data as $k => $item) {
                $data[$k]['order_user_id'] = $user_ID;
            }
        }
       
        foreach ($data as $k => $item) {
            if (isset($item['proid'])) {
                $data[$k]['protype'] = get_metadata($wpdb->productsmetatype, $item['proid'], 'protype', 1);
            }
        }

        function usort_reorder($a, $b)
        {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'otime'; //If no sort, default to title
            $order   = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result  = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
        }

        usort($data, 'usort_reorder');
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        global $user_ID;
        // print_r($data);
        $userOrder = array();
        if (!waraPay_is_admin()) {
            foreach ($data as $k => $item) {
                if (isset($item['order_user_id']) && $item['order_user_id'] == $user_ID) {
                    $userOrder[$k] = $item;
                }
            }
            $data        = $userOrder;
            $total_items = count($data);
        }
        $this->totals['all']       = $total_items;
        $this->totals['nopayment'] = 0;
        $this->totals['payed']     = 0;
        foreach ($data as $k => $item) {
            if ($item['status'] == '0') {
                $data[$k]['status'] = __('未付款','waraPayi18N');
                $this->totals['nopayment']++;
            }
            if ($item['status'] == '1') {
                $data[$k]['status'] = __('付款成功','waraPayi18N');
                $this->totals['payed']++;
            }
            if ($item['emailsend'] == '0') {
                $data[$k]['emailsend'] = __('未发货','waraPayi18N');                
            }
            if ($item['emailsend'] == '1') {
                $data[$k]['emailsend'] = __('已发货','waraPayi18N');                
            }			
			
        }
        $tmpData = array();
        if (isset($_GET['filter'])) {
            if ('all' == $_GET['filter']) {
                $tmpData = $data;
            }
            if ('nopayment' == $_GET['filter']) {
                foreach ($data as $k => $item) {
                    if (isset($item['status']) && __('未付款','waraPayi18N') == $item['status']) {
                        $tmpData[$k] = $item;
                    }
                }
            } elseif ('payed' == $_GET['filter']) {
                foreach ($data as $k => $item) {
                    if (isset($item['status']) && __('付款成功','waraPayi18N') == $item['status']) {
                        $tmpData[$k] = $item;
                    }
                }
            }
            $data = $tmpData;
        }
        $total_items = count($data);
        $data        = array_slice($data, (($current_page - 1) * $per_page), $per_page);
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
