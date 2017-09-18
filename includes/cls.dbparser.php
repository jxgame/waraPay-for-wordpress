<?php
/**
 *pass in the proid
 *it will output the html
 *use $this->ret
 *
 *
 *
 */
require_once('cfg.config.php');
require_once('cls.info.php');
class waraPay_db_parser
{

    private $tpl;

    private $css;

    public $ret;

    public function __construct($proid = 0)
    {
        global $wpdb;
        if ((int)$proid == 0) {
            $this->ret = __('【waraPay】:商品参数不合法,未知的商品ID<br/>','waraPayi18N');
            return;
        }
        //		$sql = "SELECT *
        //				FROM $wpdb->products
        //				WHERE proid = $proid;";
        //		$proInfo = $wpdb->get_results( $sql );
        $pro     = new  waraPay_product();
        $proInfo = $pro->get_info($proid);
        //print_r($proInfo);
        if (empty($proInfo)) {
            $this->ret = __('【waraPay】:该商品不存在<br/>','waraPayi18N');
            return;
        }
        if (empty($proInfo['protype'])) {
            $pro->set('protype', 'CUSTOM');
            $proInfo['protype'] = 'CUSTOM';
        }
        $oriKeys = array_keys($proInfo);
        //$proInfo	 = (array)$proInfo[0];
        //FORMAT: array('in_{field}') || array('out_{field}', {default});
        $shortCodes = array(
            array('in_pay'),
            array('in_num'),
            array('in_email'),
            array('in_msg'),
            array('in_extra'),
            array('in_addr'),
            array('in_tel'),
            array('in_ordname'),
            array('in_postcode'),
            array('out_oprice'),
            array('out_cprice'),
            array('out_sprice', 0),
        );
        $shortCodes = apply_filters('waraPay_short_codes', $shortCodes);
        //Format
        //$proInfo['short_code']	= 'classname_frontpage'		;
        //USAGE:
        //<input class="[short_code]" type="text" />
        //$tbl = $wpdb->templates;
        //$pro = $wpdb->products;
        //GET THE TPL HTML
        $tplid    = $proInfo['tplid'];
        $proname  = $proInfo['name'];
        $probtime = $proInfo['probtime'];
        $tplinfo  = $wpdb->get_results("SELECT * FROM {$wpdb->templates} WHERE tplid=$tplid;");
        if (empty($tplinfo[0])) {
            $this->ret = sprintf(__('【waraPay】:"%s",请<a href="' . WARAPAY_URL . '/includes/tpl.cart.php?proid=%s" target="_blank">点此购买</a><br/>','waraPayi18N'),$proname,$proid);
            return;
        }
        $tplinfo = (array)$tplinfo[0];
        $btime   = strtotime($proInfo['btime']);
        if (time() < $btime && !empty($btime)) {
            $this->ret = sprintf(__('【waraPay】:商品"%s"还未上架!','waraPayi18N'),$proname);
            return;
        }
        $etime = strtotime($proInfo['etime']);
        if ($etime < time() && !empty($etime)) {
            $this->ret = sprintf(__('【waraPay】:商品"%s"已下架!','waraPayi18N'),$proname);
            return;
        }
        //促销价格重载
        if ($proInfo['promote'] == 1 && $proInfo['probdate'] < date('Ymd') && date(
              'Ymd'
          ) < $proInfo['proedate'] && $proInfo['discountb'] == 1
        ) {
            if (($proInfo['protime'] == 1 && $proInfo['probtime'] < date('His') && date(
                    'His'
                ) < $proInfo['proetime']) || $proInfo['protime'] == 0
            ) {
                $proInfo['price'] *= $proInfo['discount'];
                $proInfo['price']  = round($proInfo['price'], 2);
                $proInfo['cprice'] = $proInfo['price'];
                $proInfo['sprice'] = $proInfo['oprice'] - $proInfo['cprice'];
            }
        }
        //运费价格重载
        if ($proInfo['spfre'] == 1) {
            $proInfo['price'] += $proInfo['freight'];
        }
        foreach ($shortCodes as $v) {
            $type = preg_split('@_@', $v[0], 2);
            if (empty($type[1])) {
                continue;
            }
            $type[0] = strtolower($type[0]);
            $type[1] = strtolower($type[1]);
            if ($type[0] == 'in') {
                //Make it like :$proInfo['in_pay'] = 'waraPay_buy_pay';
                $proInfo[$v[0]]      = (!empty($v[1])) ? $v[1] : 'waraPay_buy_' . $type[1];
                $postField[$type[1]] = '';
            } elseif ($type[0] == 'out') {
                if (!empty($v[1])) {
                    $proInfo[$v[0]] = $v[1];
                } elseif (isset($proInfo[$type[1]])) {
                    $proInfo[$v[0]] = $proInfo[$type[1]];
                }
            }
        }
        ##GET THE ROW BY tplnum
        ##HERE ARE TPL_CSS TPL_HTML
        $postField['nonce']   = '';
        $postField['proid']   = '';
        $postField['protype'] = '';
        $postFieldStr         = implode(',', array_keys($postField));
        $css                  = stripslashes($tplinfo['tplcss']);
        $html                 = stripslashes($tplinfo['tplhtml']);
        $js                   = stripslashes($tplinfo['tpljs']);
        $nonce                = wp_create_nonce('waraPay_front_nonce_action', 'waraPay_front_nonce_name');
        $html1                = '<div class="waraPay_buy_wrap">';
        $html1 .= '<input type="hidden" class="waraPay_buy_nonce" value="' . $nonce . '"/>';
        $html1 .= '<input type="hidden" class="waraPay_buy_protype" value="' . $proInfo['protype'] . '"/>';
        $html1 .= '<input type="hidden" class="waraPay_buy_fields" value="' . $postFieldStr . '"/>';
        $html1 .= '<input type="hidden" value="[proid]" class="waraPay_buy_proid"/>
		' . $html . '
		</div>';
        //$html是从数据库中读出的模版代码,含有商品参数的短代码
        //下面是将短代码转换成CSS中的类名,这个累供JS操作.主要是读取它的值并附加到URL的query中.
        //为什么要用类名而不用ID,因为一个页面可能有多个商品模版,而ID只能是唯一的.
        //这里的proinfo['foo']='bar'就是对短代码和类名的一一映射.
        //为什么要使用此类,而不实用WP自带的短代码函数:因为为了避免模版中的短代码不和其他插件的短代码冲突.
        //这里的短代码是为加入到WP的全局短代码数组中的.
        //############################################################################
        //END OF THE DATE FORM DB
        //下面是关键代码
        //SHORTCODE FILTER
        $patterns  = array_keys($proInfo);
        $patterns2 = array_keys($proInfo);
        foreach ($patterns as &$value) {
            if (in_array($value, $oriKeys)) {
                $value = "@\[out_{$value}\]@i";
            } else {
                $value = "@\[{$value}\]@i";
            }
        }
        foreach ($patterns2 as &$value) {
            $value = "@\[{$value}\]@i";
        }
        //去除$proInfo子元素的数组元素,否则会出现类型错误.
        foreach ($proInfo as &$value) {
            if (is_array($value)) {
                $value = "";
            }
        }
        $this->ret = preg_replace($patterns, $proInfo, $css . $html1 . $js);
        $this->ret = preg_replace($patterns2, $proInfo, $this->ret);
    }
    //END OF FN
}

