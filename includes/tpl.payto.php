<!DOCTYPE HTML>
<html>
<head>
    <title><?php echo __('页面跳转中...','waraPayi18N');?></title>
    <meta charset="utf-8"/>
    <style type="text/css">
		*{margin:0;padding:0;}
		body,html{width:auto;height:100%;}
		#outer_wrap{position:absolute;top:40%;left:50%;display:block;margin-top:-1em;margin-left:-300px;width:600px;height:2em;text-align:center;font-weight:700;font-size:20px;font-family:microsoft yahei,youyuan;line-height:2em;}
		#inner_wrap{position:relative;display:block;margin:150px auto;background:#FFC;text-align:center;}
		a.redirect{font-size:14px;}
		#loading{padding:10px;background:url(../styles/images/loading.gif) no-repeat 0 16px;background6:#ccc;}
		.info{display:block;font-size:14px;}
    </style>
</head>

<body>
<div id="outer_wrap">

    <span id="loading">&nbsp;&nbsp;&nbsp;</span><?php echo __('正在跳转中, 请稍候......','waraPayi18N');?>&nbsp;&nbsp;&nbsp;
    <noscript><label class="info"><?php echo __('【提示】您的浏览器未开启脚本支持,此操作无法继续进行,请启用javascript支持','waraPayi18N');?>&nbsp;&nbsp;&nbsp;<a
              href="http://www.baidu.com/s?tn=monline_4_dg&rn=100&bs=%C6%F4%D3%C3javascript&f=8&rsv_bp=1&wd=%D4%F5%C3%B4%C6%F4%D3%C3javascript">how to enable?</a></label>
    </noscript>
</div>
