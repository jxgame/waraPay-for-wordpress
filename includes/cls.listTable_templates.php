<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
class Templates_List_Table extends WP_List_Table
{

    function __construct()
    {
        global $status, $page;
        //Set parent defaults
        parent::__construct(
            array(
                 'singular' => __('模版','waraPayi18N'), //singular name of the listed records
                 'plural'   => __('模版','waraPayi18N'), //plural name of the listed records
                 'ajax'     => false //does this table support ajax?
            )
        );
    }
    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'tplname':
            case 'tplid':
            case 'tpldescription':
            case 'tpljs':
            case 'tplcss':
            case 'tplhtml':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }
    function column_tplname($item)
    {
        //Build row actions
        $actions = array(
            'edit'   => sprintf(
                '<a href="?page=%s&tab=templates&action=%s&tplid=%s">'.__('编辑','waraPayi18N').'</a>',
                $_REQUEST['page'],
                'edit',
                $item['ID']
            ),
            'delete' => sprintf(
                '<a href="?page=%s&tab=templates&action=%s&tplid=%s">'.__('删除','waraPayi18N').'</a>',
                $_REQUEST['page'],
                'delete',
                $item['ID']
            ),
        );
        //Return the title contents
        return sprintf(
            '%1$s <span style="color:silver">(ID:%2$s)</span>%3$s',
            /*$1%s*/
            $item['tplname'],
            /*$2%s*/
            $item['ID'],
            /*$3%s*/
            $this->row_actions($actions)
        );
    }

    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     *
     * @see WP_List_Table::::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     *
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
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

    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     *
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     *
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns()
    {
        $columns = array(
            'cb'             => '<input type="checkbox" />', //Render a checkbox instead of text
            'tplname'        => __('模版名称','waraPayi18N'),
            'tplid'          => __('编号','waraPayi18N'),
            'tpldescription' => __('描述','waraPayi18N'),
            //'tpljs'  => '模版客户端脚本',
            //'tplcss'  => '模版样式表',
            //'tplhtml'  => '模版HTML',
        );
        return $columns;
    }


    function get_sortable_columns()
    {
        $sortable_columns = array(
            'tplname'        => array('tplname', false), //true means its already sorted
            'tplid'          => array('tplid', true),
            'tpldescription' => array('tpldescription', false),
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => __('删除','waraPayi18N')
        );
        return $actions;
    }

    function process_bulk_action()
    {
        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {
            include_once('tpl.edit_template.php');
        } elseif ('edit' === $this->current_action()) {
            include_once('tpl.edit_template.php');
            //exit;
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
        $data = $wpdb->get_results("SELECT *,`tplid`as`ID` FROM `{$wpdb->templates}`;", ARRAY_A);
        //$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->templates}`;");
        //die($count );
        foreach ($data as $k => $item) {
            //if(isset($item['tplid']))
            //	$data[$k]['protype']=get_metadata($wpdb->templatesmetatype,$item['proid'],'protype',1);
        }

        function usort_reorder($a, $b)
        {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'tplid'; //If no sort, default to title
            $order   = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result  = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
        }

        usort($data, 'usort_reorder');
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         *
         * In a real-world situation, this is where you would place your query.
         *
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = count($data);
        //$total_items = $count;
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args(
            array(
                 'total_items' => $total_items, //WE have to calculate the total number of items
                 'per_page'    => $per_page, //WE have to determine how many items to show on a page
                 'total_pages' => ceil($total_items / $per_page) //WE have to calculate the total number of pages
            )
        );
    }
}
