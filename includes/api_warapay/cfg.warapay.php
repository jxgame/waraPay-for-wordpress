<?php
$dirParent = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
require_once($dirParent . 'cfg.config.php');
//appid，
$warapay_config['wara_currency'] = waraPay_get_setting('wara_currency');
$warapay_config['wara_appid'] = waraPay_get_setting('wara_appid');
//安全检验码
$warapay_config['warapay_public_key'] = waraPay_get_setting('warapay_public_key');
$warapay_config['app_private_key'] = waraPay_get_setting('app_private_key');
//页面跳转同步通知页面路径
$warapay_config['return_url'] = WARAPAY_URL . '/includes/api_warapay/inc.warapay_return.php';
//服务器异步通知页面路径，要用 http://格式的完整路径，不允许加?id=123这类自定义参数
$warapay_config['notify_url'] = WARAPAY_URL . '/includes/api_warapay/inc.warapay_notify.php';
//签名方式 不需修改
$warapay_config['version'] = '2.0';
$warapay_config['sign_type'] = 'RSA';
//字符编码格式 目前支持 gbk 或 utf-8
$warapay_config['charset'] = "utf-8";
//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$warapay_config['transport'] = 'http';