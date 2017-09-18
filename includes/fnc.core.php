<?php


//activate
if (!function_exists('waraPay_activate')):
    function waraPay_activate()
    {
        update_option('waraPay_security_code', md5(time()));
        waraPay_db_create();
    }


    //create table
endif;
if (!function_exists('waraPay_db_create')):
    function waraPay_db_create()
    {
        include_once('cls.db.php');
    }

    //add a menu
endif;
if (!function_exists('waraPay_menu_constructor')):
    function waraPay_menu_constructor()
    {
        waraPay_db_create();
        if (waraPay_is_admin()) {
            $page = add_options_page(
                WARAPAY_SETTINGS_TITLE,
                WARAPAY_NAME,
                'read',
                WARAPAY_MENU_SLUG,
                'waraPay_show_settings_page'
            );
        } elseif (waraPay_get_setting('allow_user_see_order')) {
            $page = add_menu_page(
                '我的订单',
                '我的订单',
                'read',
                WARAPAY_MENU_SLUG,
                'waraPay_show_settings_page'
            );
        }
        add_filter('plugin_action_links_' . WARAPAY_BASENAME, 'waraPay_settings_link');
        add_action('admin_print_styles-' . $page, 'waraPay_admin_header');
    }
    //show menu settings page
endif;
if (!function_exists('waraPay_show_settings_page')):
    function waraPay_show_settings_page()
    {
        include('tpl.navHandler.php');
        //include('tpl.settings.php');
    }

    //admin init
endif;
if (!function_exists('waraPay_admin_init')):
    function waraPay_admin_init()
    {
        //wp_register_script('waraPay_settings_js', WARAPAY_URL . '/javascripts/settings.js',array('jquery') );
        wp_register_script('waraPay_admin_js', WARAPAY_URL . '/javascripts/admin.js', array('jquery'));
        wp_register_style('waraPay_settings_css', WARAPAY_URL . '/styles/settings.css?v=2');
    }

endif;
if (!function_exists('waraPay_init')):
    function waraPay_init()
    {
        if (!defined('WARAPAY_NAME_EN')) {
            define('WARAPAY_NAME_EN', 'waraPay');
        }
        if (!defined('WARAPAY_URL')) {
            define('WARAPAY_URL', WP_PLUGIN_URL . "/" . WARAPAY_NAME_EN);
        }
        wp_register_script(
            'waraPay_front_js',
          WARAPAY_URL . '/javascripts/front.js',
            array('jquery')
        );
        wp_enqueue_script('waraPay_front_js');
        wp_register_script(
            'waraPay_widget_js',
          WARAPAY_URL . '/javascripts/widget.js',
            array('jquery')
        );
        wp_enqueue_script('waraPay_widget_js');
        wp_register_style(
            'waraPay_front_css',
          WARAPAY_URL . '/styles/front.css'
        );
        wp_enqueue_style('waraPay_front_css');
    }
    //header of my settings page
endif;
if (!function_exists('waraPay_admin_header')):
    function waraPay_admin_header()
    {
        if (isset($_GET['page']) && $_GET['page'] == 'waraPay') {
            wp_enqueue_script('waraPay_admin_js');
            wp_enqueue_style('waraPay_settings_css');
        }
    }

    //add a settings link to the plugins list
endif;
if (!function_exists('waraPay_settings_link')):
    function waraPay_settings_link($links)
    {
        $links[] = '<a href="' . WARAPAY_SETTINGS_LINK . '">' . __('Settings') . '</a>';
        return $links;
    }

    //load the languages pack
endif;
if (!function_exists('waraPay_languages')):
    function waraPay_languages()
    {
        load_plugin_textdomain('waraPayi18N', false, WARAPAY_NAME_EN . '/languages');
    }

endif;
if (!function_exists('waraPay_register_taxonomy')):
    function waraPay_register_taxonomy()
    {
        global $wp_rewrite;
        $catname = '商品分类';
        register_taxonomy(
            'waraPay_product_cats',
            'post',
            array(
                 'labels'                => array(
                     'menu_name'         => __($catname),
                     'name'              => _x($catname, 'taxonomy general name'),
                     'singular_name'     => _x('productcats', 'taxonomy singular name'),
                     'search_items'      => __('搜索' . $catname),
                     'all_items'         => __('所有' . $catname),
                     'parent_item'       => __('父级' . $catname),
                     'parent_item_colon' => __('父级' . $catname . '类:'),
                     'edit_item'         => __('编辑' . $catname),
                     'update_item'       => __('更新' . $catname),
                     'add_new_item'      => __('添加' . $catname),
                     'new_item_name'     => __('新建' . $catname),
                 ),
                 'show_tagcloud'         => false,
                 'hierarchical'          => true,
                 'update_count_callback' => '_update_post_term_count',
                 'query_var'             => 'ali_products',
                 'rewrite'               => did_action('init') ? array(
                       'hierarchical' => true,
                       'slug'         => 'ali_products',
                       'with_front'   => (get_option('tag_base') && !$wp_rewrite->using_index_permalinks(
                           )) ? false : true
                   ) : false,
                 'public'                => true,
                 'show_ui'               => true,
                 '_builtin'              => false,
                 'show_in_nav_menus'     => true,
            )
        );
    }

    //create security code
endif;
if (!function_exists('waraPay_security_code')):
    function waraPay_security_code()
    {
        return wp_create_nonce(get_option('waraPay_security_code'));
    }

endif;
if (!function_exists('waraPay_security_check')):
    function waraPay_security_check()
    {
        //check_admin_referer(get_option('waraPay_security_code'),'security_check');
        if (!current_user_can('level_10')) {
            wp_die('Permission Deny.');
        }
    }

    //short code parse
endif;
if (!function_exists('waraPay_shortcode_parser')):
    function waraPay_shortcode_parser($atts)
    {
        $waraPay_show_return = '';
        if (isset($atts)) {
            extract(shortcode_atts(array('id' => ''), $atts));
            if (!isset($id) || (int)$id == 0) {
                $id = 0;
            }
        } else {
            $id = 0;
        }
        require_once('cls.dbparser.php');
        $output = new waraPay_db_parser($id);
        return $output->ret;
    }

endif;
if (!function_exists('waraPay_show')):
    function waraPay_show($proid = 0)
    {
        $waraPay_show_return = '';
        global $waraPay_in_class_proid;
        $waraPay_in_class_proid = $proid;
        if (!isset($proid) || (int)$proid == 0) {
            $proid = 0;
        }
        require_once('cls.dbparser.php');
        $output = new waraPay_db_parser($proid);
        echo $output->ret;
    }

    /**
     * @mix array or string
     *      =add stripslashes
     */
endif;
if (!function_exists('waraPay_stripslashes')):
    function waraPay_stripslashes(&$mix)
    {
        if (is_string($mix)) {
            $mix = stripslashes($mix);
        } elseif (is_array($mix)) {
            foreach ($mix as &$value) {
                $value = stripslashes($value);
            }
        }
        return $mix;
    }

    /**
     *对正则表达式的特殊符号进行转义,加转义反斜杠
     * @pattern original
     *          =pattern with /
     */
endif;
if (!function_exists('waraPay_preg_pre')):
    function waraPay_preg_pre($str)
    {
        //正则表达式的特殊符号
        $preg_arr = array('\\', '*', '$', '+', '+', '.', '(', ')', '{', '}', '[', ']', '^', '?', '|');
        //将字符串变成数组,数组的成员为各个字符
        $arr = preg_split('@@', $str);
        $ret = '';
        //对每个特殊字符进行转义
        foreach ($arr as $value) {
            if (in_array($value, $preg_arr)) {
                //加反斜杠,前一个反斜杠是对后一个反斜杠的转义.
                $value = '\\' . $value;
            }
            $ret .= $value;
        }
        return $ret;
    }

    /**
     * @mix array or string
     *      =strip the /r /n /r/n
     *      =the array without empty value
     */
endif;
if (!function_exists('waraPay_filter_empty')):
    function waraPay_filter_empty($mix)
    {
        if (!is_array($mix)) {
            return $mix;
        }
        $ret = array();
        foreach ($mix as $key => $value) {
            $v     = $value;
            $value = preg_replace('@\r@', '', $value);
            $value = preg_replace('@\n@', '', $value);
            $value = preg_replace('@\r\n@', '', $value);
            $value = trim($value);
            if ($value !== '') {
                $ret[$key] = $v;
            }
        }
        return $ret;
    }

    /**
     * @array
     * @seperater
     *=(string)array with sep
     *
     */
endif;
if (!function_exists('waraPay_array_reduce')):
    function waraPay_array_reduce($arr, $sep)
    {
        $last = array_pop($arr);
        $ret  = '';
        if ($sep == '') {
            $sep = "\r\n";
        }
        foreach ($arr as $key => $value) {
            $ret .= $value . $sep;
        }
        $ret .= $last;
        return $ret;
    }

    /**
     * @string with quotes
     *         =string without quotes
     */
endif;
if (!function_exists('waraPay_esc_quotes')):
    function waraPay_esc_quotes($s)
    {
        return preg_replace('@[\"\']@', '', $s);
    }

    /**
     * @param $arr_field :the array with empty key
     * @param $arr_src   :the array with key and value
     *                   =return      :the array with key by 1st array
     */
endif;
if (!function_exists('waraPay_no_empty')):
    function waraPay_no_empty($arr_field, $arr_src)
    {
        $arr_field = array_flip($arr_field);
        foreach ($arr_field as &$v) {
            $v = '';
        }
        return array_merge($arr_field, $arr_src);
    }

    /**
     * @param $val  :field's value
     * @param $name :field's name( the selectname)
     *              =return html
     */
endif;
if (!function_exists('waraPay_select_yes_no_html')):
    function waraPay_select_yes_no_html($val, $name, $option = null)
    {
        $html = '<select name="' . $name . '">';
        if (!empty($option)) {
            foreach ($option as $opt) {
                if ($val == $opt['value']) {
                    $selected = ' selected ';
                } else {
                    $selected = '';
                }
                $html .= '<option value="' . $opt['value'] . '" ' . $selected . '>' . $opt['label'] . '</option>';
            }
        } else {
            if ($val == 0) {
                $html .= '<option value="0" selected="selected">否&nbsp;&nbsp;&nbsp;&nbsp;╳</option>';
                $html .= '<option value="1">是&nbsp;&nbsp;&nbsp;&nbsp;√</option>';
            } else {
                $html .= '<option value="0">否&nbsp;&nbsp;&nbsp;&nbsp;╳</option>';
                $html .= '<option value="1" selected="selected">是&nbsp;&nbsp;&nbsp;&nbsp;√</option>';
            }
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * @param $show :the array with details of form input
     * @param $src  :almost the form input's value
     *              =return html
     */
endif;
if (!function_exists('waraPay_input_html')):
    function waraPay_input_html($show, $src)
    {
        $html = '';
        foreach ($show as $k => $arr_v) {
            isset($arr_v['type']) || $arr_v['type'] = 'text';
            isset($arr_v['default']) || $arr_v['default'] = '';
            isset($src[$k]) || $src[$k] = $arr_v['default'];
            switch ($arr_v['type']) {
                case 'text':
                    $html .= '<div>';
                    $html .= "<label for='$k'>{$arr_v['label']}:</label>";
                    $html .= "<input type='text' name='$k' value='{$src[$k]}' />";
                    $html .= '</div>';
                    break;
				case 'textarea':
                    $html .= '<div>';
                    $html .= "<label for='$k'>{$arr_v['label']}:</label>";
                    $html .= "<textarea name='$k'/>{$src[$k]}</textarea>";
                    $html .= '</div>';
                    break;	
                case 'select':
                    if (!isset($arr_v['option'])) {
                        $arr_v['option'] = array();
                    }
                    $html .= '<div>';
                    $html .= "<label for='$k'>{$arr_v['label']}:</label>";
                    $html .= waraPay_select_yes_no_html($src[$k], $k, $arr_v['option']);
                    $html .= '</div>';
                    break;
                case 'html':
                    $html .= $arr_v['html'];
                    break;
            }
        }
        return $html;
    }

    /**
     * @param $set_name :the field name in option nameed waraPay_settings_api
     *                  =return $set_value  or null
     */
endif;
if (!function_exists('waraPay_get_setting')):
    function waraPay_get_setting($set_name)
    {
        if (isset($GLOBALS['waraPay_settings_api_json'])) {
            $set_json = $GLOBALS['waraPay_settings_api_json'];
        } else {
            $set_json                               = get_option('waraPay_settings_api');
            $GLOBALS['waraPay_settings_api_json'] = $set_json;
        }
        $set_arr = json_decode($set_json, true);
        isset($set_arr[$set_name]) || $set_arr[$set_name] = null;
        return $set_arr[$set_name];
    }

endif;
if (!function_exists('waraPay_get_settings')):
    function waraPay_get_settings()
    {
        $set_json = get_option('waraPay_settings_api');
        $set_arr  = json_decode($set_json, true);
        return $set_arr;
    }

endif;
if (!function_exists('waraPay_show_tip')):
    function waraPay_show_tip($info_code, $trano = '')
    {
        include('tpl.payto.php');
        $ret = '';
        //$ret  = '<!DOCTYPE html><html><head><title>页面跳转中,请稍候......</title>';
        $ret .= '<script type="text/javascript">';
        $ret .= 'window.location.href="' . waraPay_show_url($info_code, $trano) . '"';
        $ret .= '</script>';
        return $ret;
    }

endif;
if (!function_exists('waraPay_show_url')):
    function waraPay_show_url($info_code, $trano = '')
    {
        $info_code = strtolower($info_code);
        //$nonce = wp_create_nonce('waraPay_tip_sign');
        $key  = AUTH_KEY;
        $time = time();
        $sign = md5($info_code . $trano . $time . $key);
        $ret  = WARAPAY_URL . "/includes/tpl.tip.php?info=$info_code&trano=$trano&time=$time&sign=$sign";
        return $ret;
    }

endif;
if (!function_exists('waraPay_urlDecodeDeep')):
    function waraPay_urlDecodeDeep($arr, $_input_charset = '', $_output_charset = '')
    {
        foreach ($arr as &$v) {
            $v = urldecode($v);
            if ($_input_charset !== '' & $_input_charset !== '') {
                $v = waraPay_charsetDecode($v, $_input_charset, $_output_charset);
            }
        }
        return $arr;
    }

    /**
     * 实现多种字符编码方式
     * @param $input           需要编码的字符串
     * @param $_output_charset 输出的编码格式
     * @param $_input_charset  输入的编码格式
     *                         return 编码后的字符串
     */
endif;
if (!function_exists('waraPay_charsetEncode')):
    function waraPay_charsetEncode($input, $_output_charset, $_input_charset)
    {
        $output = "";
        if (!isset($_output_charset)) {
            $_output_charset = $_input_charset;
        }
        if ($_input_charset == $_output_charset || $input == null) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding($input, $_output_charset, $_input_charset);
        } elseif (function_exists("iconv")) {
            $output = iconv($_input_charset, $_output_charset, $input);
        } else {
            die("sorry, you have no libs support for charset change.");
        }
        return $output;
    }


    /**
     * 实现多种字符解码方式
     * @param $input           需要解码的字符串
     * @param $_output_charset 输出的解码格式
     * @param $_input_charset  输入的解码格式
     *                         return 解码后的字符串
     */
endif;
if (!function_exists('waraPay_charsetDecode')):
    function waraPay_charsetDecode($input, $_input_charset, $_output_charset)
    {
        $output = "";
        if (!isset($_input_charset)) {
            $_input_charset = $_input_charset;
        }
        if ($_input_charset == $_output_charset || $input == null) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding($input, $_output_charset, $_input_charset);
        } elseif (function_exists("iconv")) {
            $output = iconv($_input_charset, $_output_charset, $input);
        } else {
            die("sorry, you have no libs support for charset changes.");
        }
        return $output;
    }

endif;
if (!function_exists('waraPay_noEmpty')):
    function waraPay_noEmpty($arr_fields, $arr_dateSrc)
    {
        $arr_fields = array_flip($arr_fields);
        foreach ($arr_fields as &$v) {
            $v = '';
        }
        return array_merge($arr_fields, $arr_dateSrc);
    }

    function waraPay_wRCheck($name, $valin, $valout, $default = false)
    {
        $checked = ($valin == $valout || ($valout == '' && $default == true)) ? 'checked ' : '';
        return 'name="' . $name . '" value="' . $valin . '" ' . $checked . '';
    }

endif;
if (!function_exists('waraPay_validateFormat')):
    function waraPay_validateFormat($value, $type)
    {
        if (empty($value) || empty($type)) {
            return false;
        }
        switch (strtoupper($type)) {
            case 'EMAIL';
                return filter_var($value, FILTER_VALIDATE_EMAIL);
                break;
            case 'URL';
                return filter_var($value, FILTER_VALIDATE_URL);
                break;
            case 'TEL':
                return preg_match(
                    "/^1(30|31|32|45|55|56|85|86|34|35|36|37|38|39|47|50|51|52|57|58|59|82|83|87|88|33|53|89|80
                    )[0-9]{8}$/",
                    $value
                );
                break;
            case 'POSTCODE':
                return preg_match("/^\d{6}$/", $value);
                break;
            case 'ADDR':
                if (is_numeric($value)) {
                    return false;
                }
                return (strlen($value) >= 20) ? true : false;
                break;
            default:
                return true;
                break;
        }
    }

endif;
if (!function_exists('waraPay_makeQueryArr')):
    function waraPay_makeQueryArr($url = '', $query = '')
    {
        if ($url == '' && $query == '') {
            return;
        }
        if ($url !== '' && $query = '') {
            $refererName = preg_split('@\?@', $url);
            $arr_rQuery  = preg_split('@\&@', $refererName[1]);
        } else {
            $arr_rQuery = preg_split('@\&@', $query);
        }
        $arr_rqPair = array();
        foreach ($arr_rQuery as $val) {
            $arr_temp = preg_split('@\=@', $val);
            $k        = $arr_temp[0];
            if (isset($arr_temp[1])) {
                $v = $arr_temp[1];
            } else {
                $v = '';
            }
            $arr_rqPair[$k] = $v;
        }
        return $arr_rqPair;
    }

    //JUST FOR SETTINGS TPLS
endif;
if (!function_exists('waraPay_label_input_html')):
    function waraPay_label_input_html($htmls, $filter_prefix = null)
    {
        $ret = '';
        foreach ($htmls as $k => $item) {
            if ($filter_prefix) {
                $item = apply_filters($filter_prefix . $item[0], $item);
                $item = apply_filters($filter_prefix . $k, $item);
            }
            if (isset($item['html'])) {
                $ret .= $item['html'];
                continue;
            }
            $attrstr = ' ';
            if (isset($item['attrs'])) {
                foreach ($item['attrs'] as $ak => $av) {
                    $attrstr .= $ak . '="' . $av . '" ';
                }
            }
            $type = (isset($item['type'])) ? $item['type'] : 'text';
            if ($type == 'hidden') {
                $html = '<div style="display:none"><label for="' . $item[0] . '">' . $item[1] . '</label>';
            } else {
                $html = '<div><label for="' . $item[0] . '">' . $item[1] . '</label>';
            }
            switch (strtolower($type)) {
                case 'text':
                    $html .= '<input name="' . $item[0] . '" type="text" value="" ' . $attrstr . '/>';
                    break;
                case 'hidden':
                    $html .= '<input name="' . $item[0] . '" type="text" value="" ' . $attrstr . '/>';
                    break;
                case 'select':
                    $html .= '<select name="' . $item[0] . '" ' . $attrstr . ' >';
                    foreach ($item['options'] as $ok => $ov) {
                        $html .= '<option value="' . $ok . '">' . $ov . '</option>';
                    }
                    $html .= '</select>';
                    break;
                case 'textarea':
                    $html .= '<textarea name="' . $item[0] . '" ' . $attrstr . '></textarea>';
                    break;
            }
            $html .= '</div>';
            if ($filter_prefix) {
                $html = apply_filters($filter_prefix . $k . '_f', $html);
            }
            $ret .= $html;
        }
        return $ret;
    }


    //function waraPay_arraySortByKey( $arr, $k = NULL ){
    //
    //	if( empty($k) ) return 0;
    //	isset($a[$k]) || $a[$k] = 10;
    //	isset($b[$k]) || $b[$k] = 10;
    //	if ($a[$k] == $b[$k]) return 0;
    //	return ($a[$k] > $b[$k] ) ? 1 : -1;
    //
    //	uasort( $arr , 'waraPay_arraySortByKey_core');
    //
    //}
endif;
add_filter('waraPay_products_data_username', 'waraPay_products_data_username_cbk', 10, 2);
function waraPay_products_data_username_cbk($item, $items)
{
    if ($item == '') {
        $item = '游客';
    }
    if (!empty($items['userid'])) {
        $item .= ' [' . $items['userid'] . ']';
    }
    return $item;
}

if (!function_exists('waraPay_label_input_html_with_data')):
    function waraPay_label_input_html_with_data($htmls, $filter_prefix = null, $data)
    {
        $ret = '';
        foreach ($htmls as $k => $item) {
            if ($filter_prefix) {
                $item = apply_filters($filter_prefix . $item[0], $item);
                $item = apply_filters($filter_prefix . $k, $item);
            }
            if (isset($item['html'])) {
                $ret .= $item['html'];
                continue;
            }
            $attrstr = ' ';
            if (isset($item['attrs'])) {
                foreach ($item['attrs'] as $ak => $av) {
                    $attrstr .= $ak . '="' . $av . '" ';
                }
            }
            $type = (isset($item['type'])) ? $item['type'] : 'text';
            if ($type == 'hidden') {
                $html = '<div style="display:none"><label for="' . $item[0] . '">' . $item[1] . '</label>';
            } else {
                $html = '<div><label for="' . $item[0] . '">' . $item[1] . '</label>';
            }
            isset($data[$item[0]]) || $data[$item[0]] = '';
            if ($filter_prefix) {
                $data[$item[0]] = apply_filters($filter_prefix . 'data_' . $item[0], $data[$item[0]], $data);
            }
            switch (strtolower($type)) {
                case 'text':
                    $html .= '<input name="' . $item[0] . '" type="text" value="' . $data[$item[0]] . '" ' . $attrstr . '/>';
                    break;
                case 'hidden':
                    $html .= '<input name="' . $item[0] . '" type="text" value="' . $data[$item[0]] . '" ' . $attrstr . '/>';
                    break;
                case 'select':
                    $html .= '<select name="' . $item[0] . '" ' . $attrstr . ' >';
                    foreach ($item['options'] as $ok => $ov) {
                        if ($ok == $data[$item[0]]) {
                            //echo "$ok====={$data[$item[0]]}";
                            $select = '  selected="selected"';
                        } else {
                            $select = '';
                        }
                        //echo $ok.'<br />';
                        //echo $data['protype'];
                        $html .= '<option value="' . $ok . '"' . $select . '>' . $ov . '</option>';
                    }
                    $html .= '</select>';
                    break;
                case 'textarea':
                    $html .= '<textarea name="' . $item[0] . '" ' . $attrstr . '>' . $data[$item[0]] . '</textarea>';
                    break;
            }
            $html .= '</div>';
            if ($filter_prefix) {
                $html = apply_filters($filter_prefix . $k . '_f', $html);
            }
            $ret .= $html;
        }
        return $ret;
    }


endif;
if (!function_exists('waraPay_sortByOneKey')):
    function waraPay_sortByOneKey(array $array, $key, $default = 0, $asc = true)
    {
        $result = array();
        $values = array();
        $i      = 0;
        foreach ($array as $id => $value) {
            $values[$id] = isset($value[$key]) ? $value[$key] : $default + 0.00001 * $i++;
        }
        if ($asc) {
            asort($values);
        } else {
            arsort($values);
        }
        foreach ($values as $key => $value) {
            $result[$key] = $array[$key];
        }
        return $result;
    }

endif;
if (!function_exists('waraPay_unitToDay')):

    function waraPay_unitToDay($num, $unit = 'pricePerDay')
    {
        switch ($unit) {
            case 'pricePerDay':
                $num *= 1;
                break;
            case 'pricePerWeek':
                $num *= 7;
                break;
            case 'pricePerMonth':
                $num *= 30;
                break;
            case 'pricePerQuarter':
                $num *= 91;
                break;
            case 'pricePerYear':
                $num *= 365;
                break;
            default:
        }
        return $num;
    }

    //@offset: -8,0,8
endif;
if (!function_exists('waraPay_num2time')):
    function waraPay_num2time($offset)
    {
        if (empty($offset)) {
            return '+0:00';
        }
        $fh     = ($offset >= 0) ? '+' : '-';
        $fl     = ($offset * 10 % 10 == 5) ? ':30' : ':00';
        $offset = intval(abs($offset));
        return $fh . $offset . $fl;
    }
endif;
function waraPay_request_handle()
{
    waraPay_isTodelete();
}

function waraPay_isTodelete()
{
    if (!waraPay_is_admin()) {
        return;
    }
    global $wpdb;
    if (isset($_GET['action']) && $_GET['action'] == 'delete') {
        if (!empty($_GET['proid'])) {
            $key = 'proid';
            $ID  = $_GET['proid'];
        } elseif (!empty($_GET['ordid'])) {
            $key = 'ordid';
            $ID  = $_GET['ordid'];
        } elseif (!empty($_GET['tplid'])) {
            $key = 'tplid';
            $ID  = $_GET['tplid'];
        }
        //$_GET['proid'] = esc_sql($_GET['proid']);
        $table   = $_GET['tab'];
        $wptable = $table;
        if (isset($_GET['sure'])) {
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->$wptable} WHERE `$key`=%d;", $ID));
            //remove_query_tag(array('action','sure'));
            //die(remove_query_arg(array('action','sure',$key)));
            wp_redirect(remove_query_arg(array('action', 'sure', $key)));
        }
    }
}

function waraPay_is_admin()
{
    $user = wp_get_current_user();
    if(!$user){
        return false;
    }
    return $user->has_cap('activate_plugins');
}

