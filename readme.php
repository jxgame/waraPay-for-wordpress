<?php
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'waraPay'.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'cfg.config.php');
$c = array();
//$c[] = array('anchor'=>'','cat'=>1,'head'=>'','content'=>'');
$c[] = array(
    'anchor'  => 'before-use',
    'cat'     => 1,
    'head'    => __("获得一个waraPay企业帐户","waraPayi18N"),
    'content' =>__('<p>waraPay企业帐户可以帮助您在WordPress中实现商城、支付功能。<p>只需要注册waraPay您可可拥有支付宝、微信、PayPal等全球知名在线支付功能。</p><p>waraPay的商城中您可以出售实物商品、虚拟商品(自动发货)、广告位、友情链接等类别。</p><p>如需帮助，请访问<a href="http://www.warapay.com/" target="_blank">waraPay官网</a>。</p>',"waraPayi18N"),
);

$c[] = array(
    'anchor'  => 'development',
    'cat'     => 1,
    'head'    => __("授权给您的开发权益","waraPayi18N"),
    'content' => __('<p>如果您有一定的开发能力，waraPay欢迎您修改、传播本插件。</p><p>不仅仅如此，您也可以将waraPay集成到其它的系统中。请参考：插件目录下\includes\api_warapay\相关文件或规范进行开发。</p>',"waraPayi18N"),
);
$c[] = array(
    'anchor'  => 'emial-support',
    'cat'     => 1,
    'head'    => __("请启用邮件支持","waraPayi18N"),
    'content' => __('<p>本插件在交易的同时很大程度上依赖于Email的交互。</p><p>如果您是租用的的主机,那么您几乎不必担心邮件的问题,如果您是自己搭建的主机,请安装SMTP相关插件启用Email支持!</p>',"waraPayi18N"),
);
 
$c[] = array(
    'anchor'  => 'protect-key',
    'cat'     => 1,
    'head'    => __("保护您的接口密钥","waraPayi18N"),
    'content' => __('<p>正如您所见,您可以直接在常规设置面板中设置您的接口密钥。</p><p>如果您觉得密钥被保存在数据库中不安全,你可以打开插件的文件进行密钥变量设置,具体如下:</p><p>..includes\api_warapay\cfg.warapay.php</p><p>将你的接口密钥填入其中，修改完后请在该插件的常规设置面板中将对应的密钥字段清空即可。</p>',"waraPayi18N"),
);
$c[] = array(
    'anchor'  => 'delete-caution',
    'cat'     => 1,
    'head'    => __("请勿随意删除订单和商品","waraPayi18N"),
    'content' =>__('<p>如果您是自己在测试插件所产生的订单，删除订单尚无大碍。</p><p>如果是客户提交的订单,建议您不要在短期内删除相关商品和订单，否则可能造成付款后无法自动发货。</p>',"waraPayi18N"),
);
$c[] = array(
    'anchor'  => 'create-template',
    'cat'     => 1,
    'head'    => __("如何创建一个模版？","waraPayi18N"),
    'content' =>__('<p>由于每个网站风格不同，为了更加适配您原有的风格，waraPay提供插件美化的功能，方法如下：</p><p>在插件的"模版管理"中,你可以看到有CSS,HTML,JS代码的输入框,你更多的需要关注HTML和CSS的编写,而JS可以提升用户的体验,但不是必须.如下范例</p><div><textarea style="height:300px;">',"waraPayi18N").hs(
          '<div><label>'.__('收件人姓名:',"waraPayi18N").'</label><input type="text" class="[in_ordname]" value=""/></div>
          <div><label>'.__('收件人电话:',"waraPayi18N").'</label><input type="text" class="[in_tel]" value=""/></div>
          <div><label>'.__('收件人邮编:',"waraPayi18N").'</label><input type="text" class="[in_postcode]" value=""/></div>
          <div><label>'.__('收件人地址:',"waraPayi18N").'</label><input type="text" class="[in_addr]" value=""/></div>
          <div><label>'.__('备注信息:',"waraPayi18N").'</label></div>
          <div><textarea class="[in_extra]"></textarea></div>
          <div><label>'.__('买家留言:',"waraPayi18N").'</label></div>
          <div><textarea class="[in_msg]"></textarea></div>
          <div><label>'.__('收货邮箱:',"waraPayi18N").'</label></div>
          <div><input type="text" class="[in_email]" value="" /></div>
          <div><label>'.__('购买数量:',"waraPayi18N").'</label></div>
          <div><select class="[in_num]">
          <option value="1">'.__('1件',"waraPayi18N").'</option>
          <option value="2">'.__('2件',"waraPayi18N").'</option>
          <option value="3">'.__('3件',"waraPayi18N").'</option>
          </select></div>
          <div><input type="button" class="[in_pay]" value="'.__('点此购买',"waraPayi18N").'"></div>').__('</textarea></div><p>您至少需要布置点击购买按钮,即:</p><p>',"waraPayi18N").hs('<div><input type="button" class="[in_pay]" value="'.__('点此购买',"waraPayi18N").'"></div>').__('</p><p>其他的,你还可以添加买家邮箱等信息,他们的布置都遵循一个规则,都使用表单的标签,且在其class属性中添加如[in_email]这样的短代码。但是这个短代码也不是随意写的,首先需要用户输入的都用in_开头,需要显示给用户看的都用out_开头。</p>',"waraPayi18N"),
);

$c[] = array(
    'anchor'  => 'template_flag',
    'cat'     => 1,
    'head'    => __("风格中标签代码的含义","waraPayi18N"),
    'content' =>'<div>'.__('目前支持的字段如下:',"waraPayi18N").'<table border="1px" cellspacing="0" style="margin: 0 auto;line-height: 40px;">
<thead>
	<tr>
		<th>'.__("标签代码","waraPayi18N").'</th><th>'.__("代码含义","waraPayi18N").'</th>
	</tr>
</thead>
<tbody>
	
	<td>in_pay</td><td>'.__('点击购买(用于type="button")',"waraPayi18N").'</td></tr>
	<tr><td>in_num</td><td>'.__("购买数量","waraPayi18N").'</td></tr>
	<tr><td>in_email</td><td>'.__("买家邮箱","waraPayi18N").'</td></tr>
	<tr><td>in_addr</td><td>'.__("收件人详细地址","waraPayi18N").'</td></tr>
	<tr><td>in_tel</td><td>'.__("收件人电话/手机","waraPayi18N").'</td></tr>
	<tr><td>in_ordname</td><td>'.__("收件人姓名","waraPayi18N").'</td></tr>
	<tr><td>in_postcode</td><td>'.__("收件人邮编","waraPayi18N").'</td></tr>
	<tr><td>in_imgSrc</td><td>'.__("图片地址","waraPayi18N").'</td></tr>
	<tr><td>in_imgLink</td><td>'.__("点击图片跳转地址","waraPayi18N").'</td></tr>
	<tr><td>in_linkName</td><td>'.__("链接名称","waraPayi18N").'</td></tr>
	<tr><td>in_linkUrl</td><td>'.__("链接地址","waraPayi18N").'</td></tr>
	<tr><td>in_linkDesc</td><td>'.__("链接描述","waraPayi18N").'</td></tr>
	
	<tr><td>……</td><td>……</td></tr>
	
	<tr><td>out_proid</td><td>'.__("商品ID","waraPayi18N").'</td></tr>
	<tr><td>out_name</td><td>'.__("商品名称","waraPayi18N").'</td></tr>
	<tr><td>out_price</td><td>'.__("商品价格","waraPayi18N").'</td></tr>
	<tr><td>out_oprice</td><td>'.__("商品原价","waraPayi18N").'</td></tr>
	<tr><td>out_cprice</td><td>'.__("商品当前价格","waraPayi18N").'</td></tr>
	<tr><td>out_sprice</td><td>'.__("当前购买所能节约的价格","waraPayi18N").'</td></tr>
	<tr><td>out_num</td><td>'.__("商品剩余数量","waraPayi18N").'</td></tr>
	<tr><td>out_snum</td><td>'.__("卖出数量","waraPayi18N").'</td></tr>
	<tr><td>out_weight</td><td>'.__("商品重量KG","waraPayi18N").'</td></tr>
	<tr><td>out_description</td><td>'.__("商品描述","waraPayi18N").'</td></tr>
	<tr><td>out_images</td><td>'.__("商品图片","waraPayi18N").'</td></tr>
	<tr><td>out_service</td><td>'.__("客服QQ/阿里旺旺","waraPayi18N").'</td></tr>
	<tr><td>out_download</td><td>'.__("下载链接","waraPayi18N").'</td></tr>
	<tr><td>out_zipcode</td><td>'.__("解压密码","waraPayi18N").'</td></tr>
	<tr><td>out_callback</td><td>'.__("返回地址","waraPayi18N").'</td></tr>
	<tr><td>out_categories</td><td>'.__("商品分类","waraPayi18N").'</td></tr>
	<tr><td>out_tags</td><td>'.__("商品标签","waraPayi18N").'</td></tr>
	<tr><td>out_spfre</td><td>'.__("买家承担运费","waraPayi18N").'</td></tr>
	<tr><td>out_freight</td><td>'.__("运费","waraPayi18N").'</td></tr>
	<tr><td>out_location</td><td>'.__("所在地","waraPayi18N").'</td></tr>
	<tr><td>out_atime</td><td>'.__("商品添加时间戳","waraPayi18N").'</td></tr>
	<tr><td>out_btime</td><td>'.__("商品开始时间戳","waraPayi18N").'</td></tr>
	<tr><td>out_etime</td><td>'.__("商品结束时间戳","waraPayi18N").'</td></tr>
	<tr><td>out_probdate</td><td>'.__("促销开始日期","waraPayi18N").'</td></tr>
	<tr><td>out_proedate</td><td>'.__("促销结束日期","waraPayi18N").'</td></tr>
	<tr><td>out_probtime</td><td>'.__("促销开始时刻","waraPayi18N").'</td></tr>
	<tr><td>out_proetime</td><td>'.__("促销结束时刻","waraPayi18N").'</td></tr>
	<tr><td>out_discount</td><td>'.__("商品折扣","waraPayi18N").'</td></tr>

</tbody>
</table></div>',
);
$c[] = array(
    'anchor'  => 'how2usetpl',
    'cat'     => 1,
    'head'    => __("如何使用已创建的模版","waraPayi18N"),
    'content' =>__('<p>每一个模版都对应一个唯一的ID,这个ID可以在模版管理中查看,将该ID填入商品管理中商品的"模版选择"字段中即可使用该模版。</p><p><strong>同样，您可以修改..styles\css\下的相关css样式文件达到全局适配的目的。</strong></p>',"waraPayi18N"),
);
$c[] = array(
    'anchor'  => 'how2useinpost',
    'cat'     => 1,
    'head'    => __("如何在文章中调用商品？","waraPayi18N"),
    'content' => __('<p>在文章编辑器中使用短代码<span style="display:inline-block;margin:0 5px;color:#f00;font-weight:bold;">[zfb id=100]</span>  即可调用商品id为100的商品，以此类推。</p>',"waraPayi18N"),
);
$c[] = array(
    'anchor'  => 'mailfailed',
    'cat'     => 1,
    'head'    => __("无法发送邮件？","waraPayi18N"),
    'content' => __('<p>1、是否安装SMTP插件？网站中本插件之外的邮件是否能收到？</p><p>2、常规设置中是否开启相关事件的邮件？</p><p>3、检查垃圾箱是否有邮件？</p>',"waraPayi18N"),
);
$c[] = array(
	'anchor' => 'refreshfailed',
	'cat' => 1, 
	'head' => __("后台数据更新滞后","waraPayi18N"),
	'content' => __('<p>出现这个情况很可能是您启用了数据库缓存等插件造成的。</p>',"waraPayi18N")
);
$c[] = array(
	'anchor' => 'tplloadfail',
	'cat' => 2, 
	'head' => __("请关注waraPay","waraPayi18N"),
	'content' => __('<p>waraPay需要您持续的关注，以便获得商家活动、插件更新等支持。</p>',"waraPayi18N")
);

$menu = '<ol>';
$content = '';

foreach ($c as $item) {
    !empty($item['anchor']) || $item['anchor'] = 'anchor' . rand(10000, 99999);
    $content .= '<a name="' . $item['anchor'] . '"></a>';
    $content .= '<h3>✍ ' . $item['head'] . '</h3>';
    $menu .= '<li><a href="#' . $item['anchor'] . '">' . $item['head'] . '</a></li>';
    $content .= '<p>' . $item['content'] . '</p>';
}
$menu .= '</ol>';

function hs($in)
{
    return htmlspecialchars($in);
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>waraPay DOCS</title>
    <style type="text/css">
		*,body,html{margin:0;padding:0;font-family:'Microsoft YaHei',微软雅黑;}
		#main_wrap{background: #f3f3f3;}
		#main_wrap #menu{width: 220px;position: fixed;top: 72px;}
		#main_wrap #child_wrap{width: 90%;margin: 0 auto;padding: 20px;}		
		#main_wrap #menu ol{margin-bottom:10px;padding:20px;padding-left:25%;border-radius:5px;background:#CCC;}
		#main_wrap #menu p{text-align:center;font-size:24px;}
		#main_wrap #content{padding:10px 20px;border-radius:5px;background:#eee;margin-left: 230px;}
		#main_wrap #content h3{margin-bottom:5px;color:#966;}
		#main_wrap #content p{margin-bottom:20px;text-indent:2em;}
		#main_wrap #content textarea{width:90%;min-height:100px;}
		#main_wrap #content table td,#main_wrap #content table th{padding-left:15px;width:300px;}
		#translate{float:right;overflow:hidden;height:80px;}		#topbar{position:fixed;right:20px;bottom:20px;width:50px;height:50px;border-radius:10px;background:#903;color:#000;text-align:center;line-height:50px;opacity:.5;}
		#topbar:hover{background:#900;}
		h1{float:left;margin-bottom:30px;padding:5px 30px;border-radius:15px 0 15px 0;background:#BF0630;color:#CCC;text-shadow:#000 0 -1px;}
		.clear{float:none;clear:both;height:0!important;}
    </style>
</head>
<body>
<div id="main_wrap">

    <div id="child_wrap">
        <a name="top"></a>
        <h1><?php echo __("waraPay For WordPress插件帮助文档","waraPayi18N");?></h1>
        <div class="clear"></div>
        <div id="menu">
            <p><?php echo __("目录(MENU)","waraPayi18N");?></p>
            <?php echo $menu; ?>
        </div>
        <div id="content">
            <?php echo $content; ?>
        </div>
    </div>

</div>

<a href="#top">
    <div id="topbar">TOP</div>
</a>
</body>
</html>