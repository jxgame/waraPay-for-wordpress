<?php
class waraPay_AdWidget extends WP_Widget{

    //构造函数
    function waraPay_AdWidget()
    {
        parent::WP_Widget('waraPay', $name = __('支付宝广告','waraPayi18N'), array('description' => __('使用该小工具可以添加一个自助广告位','waraPayi18N')));
    }

    function __construct()
    {
        $this->waraPay_AdWidget();
    }

    //前台HTML
    function widget($args, $ins)
    {
        require_once(WARAPAY_INC . 'cls.info.php');
        $ad  = new waraPay_Ads($ins['proId']);
        $ads = $ad->get($ad->adfield);
        if (isset($_POST['waraPay_ad_preview'])) {
            $ins['imgSrc']  = $_POST['waraPay_ad_preview_src'];
            $ins['imgHref'] = $_POST['waraPay_ad_preview_url'];
            $formcls        = '';
            $formcls_s      = 'waraPay_widget_hide';
        } elseif ($ads[0]['endTime'] > time()) {
            $ins['imgSrc']  = $ads[0]['imgSrc'];
            $ins['imgHref'] = $ads[0]['imgLink'];
            $formcls        = '';
            $formcls_s      = 'waraPay_widget_hide';
        } else {
            $ins['imgSrc']  = $ins['imgSrc'];
            $ins['imgHref'] = 'javascript:void(0);';
            $formcls        = 'waraPay_widget_form';
            $formcls_s      = 'waraPay_widget_show';
            if ($ins['showChar'] == 'show') {
                $formcls_s = 'waraPay_widget_show';
            } else {
                $formcls_s = 'waraPay_widget_hide';
            }
        }
        extract($args);
        if (empty($ins['proId'])) {
            $tip = __('proid is not found!', 'waraPayi18N');
        } else {
            $tip = '';
        }
        $html = <<<HTML
$before_widget 
$before_title {$ins['title']} $after_title
<div class="waraPay_widget_wrap waraPay_buy_wrap"><!--1-->
<a href="{$ins['imgHref']}" target="_blank"><img src="{$ins['imgSrc']}" width="{$ins['imgWidth']}px" height="{$ins['imgHeight']}px" /></a>

<div class="$formcls_s" style="width:{$ins['imgWidth']}px;height:{$ins['imgHeight']}px;">
<p class="waraPay_widget_size">{$ins['imgWidth']}&nbsp;&nbsp;X&nbsp;&nbsp;{$ins['imgHeight']}</p>
$tip 
<p class="waraPay_widget_char">{&nbsp;{__('Goods!','waraPayi18N')}&nbsp;}</p>
<p class="waraPay_widget_char">{&nbsp;虚位以待&nbsp;}</p>
<p class="waraPay_widget_char">自助广告&nbsp;:&nbsp;{$this->choosePrice($ins)}</p>
<input type="button" value="试一试" name="waraPay_ad_preview" class="waraPay_widget_try"/>
</div>

<div class="$formcls" style="display:none;width:{$ins['imgWidth']}px;height:{$ins['imgHeight']}px;">
<!--2-->
<div class="waraPay_widget_form_wrap waraPay_buy_wrap"><!--3-->
<form method="POST" target="_blank">
<p>
<label>粘贴入您的广告图片地址</label>
<input type="text" name="waraPay_ad_preview_src" class="waraPay_buy_imgSrc"/>
</p>

<p>
<label>跳转地址</label>
<input type="text" name="waraPay_ad_preview_url" class="waraPay_buy_imgLink"/>
</p>

<p>

<input type="hidden" class="waraPay_buy_fields" value="imgSrc,imgLink,proid"/>
<input type="hidden" class="waraPay_buy_proid" value="{$ins['proId']}"/>
<input type="button" value="订购" name="waraPay_ad_order" class="waraPay_widget_btn waraPay_buy_pay"/>
<input type="submit" value="预览" name="waraPay_ad_preview" class="waraPay_widget_btn waraPay_widget_preview"/>
</p>

</form>
</div><!--3-->
</div><!--2-->
</div><!--1-->

$after_widget
HTML;
        echo $html;
    }

    //更新事件数据过滤器
    function update($new_instance, $old_instance)
    {
        $ins                    = $new_instance;
        $ins['title']           = strip_tags($ins['title']);
        $ins['imgWidth']        = (int)$ins['imgWidth'];
        $ins['imgHeight']       = (int)$ins['imgHeight'];
        $ins['pricePerDay']     = number_format(abs(floatval($ins['pricePerDay'])), 2, '.', '');
        $ins['pricePerWeek']    = number_format(abs(floatval($ins['pricePerWeek'])), 2, '.', '');
        $ins['pricePerMonth']   = number_format(abs(floatval($ins['pricePerMonth'])), 2, '.', '');
        $ins['pricePerQuarter'] = number_format(abs(floatval($ins['pricePerQuarter'])), 2, '.', '');
        $ins['pricePerYear']    = number_format(abs(floatval($ins['pricePerYear'])), 2, '.', '');
        $multiPrice             = array(
            'protype'         => 'ADP',
            'pricePerDay'     => $ins['pricePerDay'],
            'pricePerWeek'    => $ins['pricePerWeek'],
            'pricePerMonth'   => $ins['pricePerMonth'],
            'pricePerQuarter' => $ins['pricePerQuarter'],
            'pricePerYear'    => $ins['pricePerYear'],
        );
        include(WARAPAY_INC . 'cls.info.php');
        $pro     = new waraPay_product($ins['proId']);
        $proInfo = $pro->set('', '', $multiPrice);
        return $ins;
    }

    //后台HTML
    function form($ins)
    {
        //echo $this->number;
        $arr_fields = array( /*'title',*/ /*'imgSrc',*/ /*'imgWidth','imgHeight',*/
            'proId',
            'showChar' /*'cyclePrice',*/ /*'cycleUnit'*/
        );
        $ins        = waraPay_no_empty($arr_fields, $ins);
        isset($ins['cycleUnit']) || $ins['cycleUnit'] = 'M';
        isset($ins['title']) || $ins['title'] = __('Auto Ads', 'waraPayi18N');
        isset($ins['imgSrc']) || $ins['imgSrc'] = WARAPAY_IMG_URL . '/ad_01.gif';
        isset($ins['imgWidth']) || $ins['imgWidth'] = 250;
        isset($ins['imgHeight']) || $ins['imgHeight'] = 200;
        isset($ins['cyclePrice']) || $ins['cyclePrice'] = 300;
        isset($ins['pricePerDay']) || $ins['pricePerDay'] = 8;
        isset($ins['pricePerWeek']) || $ins['pricePerWeek'] = 48;
        isset($ins['pricePerMonth']) || $ins['pricePerMonth'] = 188;
        isset($ins['pricePerQuarter']) || $ins['pricePerQuarter'] = 548;
        isset($ins['pricePerYear']) || $ins['pricePerYear'] = 1888;
        $arr_input    = array(
            'title'           => array(__('Title', 'waraPayi18N')),
            'imgSrc'          => array(__('ImageSrc', 'waraPayi18N')),
            'imgWidth'        => array(__('Image', 'waraPayi18N') . __('Width', 'waraPayi18N') . '(px)'),
            'imgHeight'       => array(__('Image', 'waraPayi18N') . __('Height', 'waraPayi18N') . '(px)'),
            'proId'           => array(__('productid', 'waraPayi18N')),
            'pricePerDay'     => array(__('pricePerDay', 'waraPayi18N')),
            'pricePerWeek'    => array(__('pricePerWeek', 'waraPayi18N')),
            'pricePerMonth'   => array(__('pricePerMonth', 'waraPayi18N')),
            'pricePerQuarter' => array(__('pricePerQuarter', 'waraPayi18N')),
            'pricePerYear'    => array(__('pricePerYear', 'waraPayi18N')),
            //'cyclePrice' => array(__('cyclePrice','')),
            //'cycleUnit'  => array(__('cycleUnit','') , 'html'=>'
            //				<select style="display:block;width:100%" name="'.$this->get_field_name('cycleUnit').'" id="'.$this->get_field_id('cycleUnit').'">
            //			<option value="D"'.$this->sl($ins['cycleUnit'],'D').'>'.__('day','').'</option>
            //			<option value="W"'.$this->sl($ins['cycleUnit'],'W').'>'.__('week','').'</option>
            //			<option value="M"'.$this->sl($ins['cycleUnit'],'M').'>'.__('month','').'</option>
            //			<option value="Q"'.$this->sl($ins['cycleUnit'],'Q').'>'.__('quarter','').'</option>
            //			<option value="Y"'.$this->sl($ins['cycleUnit'],'Y').'>'.__('year','').'</option>
            //				</select>
            //			'),
            'showChar'        => array(
                __('show the characters', 'waraPayi18N'),
                'html' => '
				<select style="display:block;width:100%" name="' . $this->get_field_name(
                      'showChar'
                  ) . '" id="' . $this->get_field_id('showChar') . '">
		<option value="show"' . $this->sl($ins['showChar'], 'show') . '>' . __('show', 'waraPayi18N') . '</option>
		<option value="hide"' . $this->sl($ins['showChar'], 'hide') . '>' . __('hide', 'waraPayi18N') . '</option>
				</select>
			'
            ),
        );
        $ins['title'] = esc_attr($ins['title']);
        echo '<p>';
        foreach ($arr_input as $k => $v) {
            $name = $this->get_field_name($k);
            $id   = $this->get_field_id($k);
            $val  = $ins[$k];
            echo '<label for="' . $name . '">' . _e($v[0], '') . ':</label>';
            if (isset($v['html'])) {
                echo $v['html'];
            } else {
                echo '
<input class="widefat" id="' . $id . '"  name="' . $name . '" type="text" value="' . $val . '" />';
            }
        }
        echo '</p>';
        ?>

    <?php
    }

    //-----------------------------------------------------------------------
    // TOOLS
    //-----------------------------------------------------------------------
    function sl($value, $default)
    {
        return ($value == $default) ? ' selected="selected" ' : '';
    }

    function e2c($e)
    {
        switch ($e) {
            case 'D':
                $c = __('Day', 'waraPayi18N');
                break;
            case 'W':
                $c = __('Week', 'waraPayi18N');
                break;
            case 'M':
                $c = __('Month', 'waraPayi18N');
                break;
            case 'Q':
                $c = __('Quarter', 'waraPayi18N');
                break;
            case 'Y':
                $c = __('Year', 'waraPayi18N');
                break;
            default:
                $c = $e;
        }
        return $c;
    }

    function choosePrice($ins)
    {
        $arr_fields = array('pricePerDay', 'pricePerWeek', 'pricePerMonth', 'pricePerQuarter', 'pricePerYear');
        $ins        = waraPay_no_empty($arr_fields, $ins);
        if (floatval($ins['pricePerDay']) > 0) {
            return "￥{$ins['pricePerDay']}/" . __('Day', 'waraPayi18N');
        } elseif (floatval($ins['pricePerWeek']) > 0) {
            return "￥{$ins['pricePerWeek']}/" . __('Week', 'waraPayi18N');
        } elseif (floatval($ins['pricePerMonth']) > 0) {
            return "￥{$ins['pricePerMonth']}/" . __('Month', 'waraPayi18N');
        } elseif (floatval($ins['pricePerQuarter']) > 0) {
            return "￥{$ins['pricePerQuarter']}/" . __('Quarte', 'waraPayi18N');
        } elseif (floatval($ins['pricePerYear']) > 0) {
            return "￥{$ins['pricePerYear']}/" . __('Year', 'waraPayi18N');
        }
    }
} // class FooWidget
// 注册 FooWidget 挂件
add_action('widgets_init', create_function('', 'return register_widget("waraPay_AdWidget");'));

