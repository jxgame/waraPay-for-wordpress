<?php
/*
 Template of settings page
*/
require_once('cfg.config.php'); 
$waraPay_api_json = get_option('waraPay_settings_api');
$waraPay_api_arr = json_decode($waraPay_api_json, true);
$waraPay_api_fields = array(

    array('type' => 'html', 'html' => '<h2>'.__('waraPay帐号设置','waraPayi18N').'</h2>'),
    ////wara
    array('type' => 'html', 'html' => '<p class="clear_5"></p>'),
    'wara_currency'       => array(
        'type'    => 'select',
        'label'   => __('收款货币','waraPayi18N'),
        'default' => '1',
        'option'  => array(
            array(
                'value' => 'CNY',
                'label' => __('人民币','waraPayi18N')
            ),
            array(
                'value' => 'KER',
                'label' => __('韩元','waraPayi18N')
            ),
            array(
                'value' => 'VND',
                'label' => __('越南盾','waraPayi18N')
            ),
            array(
                'value' => 'JPY',
                'label' => __('日元','waraPayi18N')
            )			
        )
    ),

    'wara_appid'       => array('label' => __('waraPay接口appid','waraPayi18N')),
    'warapay_public_key'      => array('type'=>'textarea','label' => __('waraPay公钥','waraPayi18N')),
    'app_private_key'           => array('type'=>'textarea','label' => __('app私钥','waraPayi18N')),
    array(
        'type' => 'html',
        'html' => '<div><a style="line-height:2em;padding:10px;padding-bottom:0" href="http://www.warapay.com/" target="_blank">'.__('注册waraPay企业帐号','waraPayi18N').'</a></div>'
    ),
	
    ////邮件设置
    array('type' => 'html', 'html' => '<h2>'.__('邮件通知设置','waraPayi18N').'</h2>'),
    //管理员邮箱
    'notify_email'         => array('label' => __('管理员邮箱地址','waraPayi18N'), 'default' => get_option('admin_email')),
    //买卖家订单付款通知
    'buyer_ord_notify'     => array('type' => 'select', 'label' => __('买家订单通知','waraPayi18N'), 'default' => '0'),
    'buyer_pay_notify'     => array('type' => 'select', 'label' => __('买家付款通知(建议开启)','waraPayi18N'), 'default' => '1'),
    'seller_ord_notify'    => array('type' => 'select', 'label' => __('卖家订单通知','waraPayi18N'), 'default' => '0'),
    'seller_pay_notify'    => array('type' => 'select', 'label' => __('卖家付款通知(建议开启)','waraPayi18N'), 'default' => '1'),
    //缺货提醒
    'pro_lack_notify'      => array('type' => 'select', 'label' => __('管理员缺货通知(建议开启)','waraPayi18N'), 'default' => '1'),
    array('type' => 'html', 'html' => '<h2>'.__('其他设置','waraPayi18N').'</h2>'),
    'link_support'         => array('type' => 'text', 'label' => __('客服超链接(显示在邮件中)','waraPayi18N'), 'default' => ''),
    'user_must_login'      => array('type' => 'select', 'label' => __('购买商品必须登录','waraPayi18N'), 'default' => '0'),
    'allow_user_see_order' => array('type' => 'select', 'label' => __('允许登录用户看见自己的订单','waraPayi18N'), 'default' => '0'),
    array('type' => 'html', 'html' => '<br/>'),
    array('type' => 'html', 'html' => '<br/>'),
    array(
        'type' => 'html',
        'html' => '<p style="line-height:2em;padding:10px;padding-bottom:0">'.__('【注】使用问题请联系 jxgame@163.com','waraPayi18N').'</p>'
    ),
 
 
); 
///waraPay_get_setting('') 
?>
<script type="text/javascript">
    jQuery(function($) {//BOJQ

        $('#waraPay_api_form').submit(function() {
            var $data = $('#waraPay_api_form').serialize();

            $.ajax({
                url: '../wp-content/plugins/waraPay/includes/inc.dbloader.php',
                type: 'post',
                dataType: 'JSON',
                data: $data +
                  '&security_check=<?php echo waraPay_security_code();?>' +
                  '&action=78013',
                success: function(data) {
                    if (data == '')
                        alert('<?php echo __('保存成功！','waraPayi18N');?>');
                    else
                        alert("<?php echo __('保存失败！','waraPayi18N');?>");
                }

            });
            return false;
        });

        $('.waraPay_api_div input[type=checkbox]').bind('change', function() {
            if ($(this).attr('checked')) {
                $(this).val('1');
            } else {
                $(this).val('0');
            }
            alert($(this).val())
        });

        $('.waraPay_api_div input[type=checkbox]').each(function() {
            if ($(this).val() == '1') {
                $(this).attr('checked', 'checked');
            }
            if ($(this).val() == '0') {
                $(this).removeAttr('checked');
            }
        });

    });//EOJQ
</script>
<style type="text/css">

</style>
<div class="wrap waraPay_main_wrap">
    <?php include_once('tpl.tab.nav.php'); ?>
    <div id="icon-options-general" class="icon32"><br/></div>
    <h2><?php echo __('常规设置','waraPayi18N');?></h2>

    <div class="waraPay_api_div postbox">
        <form action="" method="post" class="api_form" id="waraPay_api_form">
            <?php echo waraPay_input_html($waraPay_api_fields, $waraPay_api_arr); ?>
            <p class="clear_10"></p>

            <div class="newline"></div>
            <div class="newline"></div>
            <div class="newline"></div>

            <input type="submit" class="button-primary update" id="waraPay_api_update" value="<?php echo __('保存设置','waraPayi18N');?>"/>
        </form>

        <div class="clear"></div>
        <div class="newline"></div>
    </div>
</div>