<?php
//header( 'Content-Type:text/html; charset=utf-8' );
include_once('cfg.config.php');
if (!class_exists('waraPay_Mail')):
    class waraPay_Mail
    {

        var $proInfo, $ordInfo, $opt, $status, $arrOrdInfo, $arrProInfo;

        var $tip = array();

        function __construct($proInfo = null, $ordInfo = null)
        {
            $this->waraPay_Mail($proInfo, $ordInfo);
        }

        function waraPay_Mail($proInfo = null, $ordInfo = null)
        {
            $this->proInfo        = $proInfo;
            $this->ordInfo        = $ordInfo;
            $this->tip['MSG_01']  = __('亲爱的','waraPayi18N');
            $this->tip['MSG_02']  = __('您好！','waraPayi18N');
            $this->tip['MSG_03']  = __('感谢您的购买！','waraPayi18N');
            $this->tip['MSG_04']  = __('请仔细阅读以下内容:','waraPayi18N');
            $this->tip['MSG_05']  = __('订单详情如下:','waraPayi18N');
            $this->tip['MSG_06']  = __('商品信息如下:','waraPayi18N');
            $this->tip['MSG_07']  = __('此为系统邮件，请勿回复','waraPayi18N');
            $this->tip['MSG_08']  = __('请保管好您的邮箱，避免信息被他人窃取','waraPayi18N');
            $this->tip['MSG_09']  = __('如有任何疑问，可与','waraPayi18N');
            $this->tip['MSG_10']  = __('客服','waraPayi18N');
            $this->tip['MSG_11']  = __('联系进行咨询。','waraPayi18N');
            $this->tip['MSG_12']  = __('会员','waraPayi18N');
            $this->tip['MSG_13']  = __('友情提醒:','waraPayi18N');
            $this->tip['MSG_20']  = '';
            $this->tip['ORDINFO'] = false;
            $this->tip['PROINFO'] = false;
            //设置当前为HTML模式
            add_filter('wp_mail_from_name', create_function('', 'return "<?php echo __(\'支付交易提醒\',\'waraPayi18N\');?>";'), 999999);
            add_filter('wp_mail_content_type', create_function('', 'return "text/html";'), 999999);
        }

        function refresh($proInfo = null, $ordInfo = null)
        {
            $this->proInfo = $proInfo;
            $this->ordInfo = $ordInfo;
        }

        function send($receiver, $task, $isSeller = false)
        {
            $this->arrOrdInfo = array();
            $this->arrProInfo = array();
            $headers          = "BCC: " . get_bloginfo('admin_email') . "\r\n";
            //重写发送对象
            if ($isSeller) {
                $this->tip['MSG_12'] = __('管理员','waraPayi18N');
            } else {
                $this->tip['MSG_12'] = __('会员','waraPayi18N');
            }
            //创建新任务
            switch (strtoupper($task)) {
                case 'ORDER': //下了一个订单
                    if ($isSeller) {
                        $this->tip['SUBJECT'] = __('[管理员]新交易已创建:等待买家付款','waraPayi18N');
                        $this->tip['MSG_20']  = __('新交易已创建, 等待买家付款','waraPayi18N');
                        $this->tip['MSG_03']  = __('您有一个新的订单!','waraPayi18N');
                    } else {
                        $this->tip['SUBJECT'] = __('交易状态已改变为:等待付款','waraPayi18N');
                        $this->tip['MSG_20']  = __('您的订单已被记录, 请尽快完成支付。','waraPayi18N');
                    }
                    $this->arrOrdInfo[] = array(__('交易状态已改变为:等待付款','waraPayi18N'), $this->ordInfo['series']);
                    $this->arrOrdInfo[] = array(__('交易状态已改变为:等待付款','waraPayi18N'), $this->proInfo['proid']);
                    $this->arrOrdInfo[] = array(__('交易状态已改变为:等待付款','waraPayi18N'), $this->proInfo['name']);
                    $this->arrOrdInfo[] = array(__('商品单价','waraPayi18N'), $this->ordInfo['payprice']);
                    $this->arrOrdInfo[] = array(__('购买数量','waraPayi18N'), $this->ordInfo['buynum']);
                    $this->arrOrdInfo[] = array(__('商品运费','waraPayi18N'), $this->ordInfo['freight']);
                    $this->arrOrdInfo[] = array(__('支付总计','waraPayi18N'), $this->ordInfo['ordfee']);
                    $this->arrOrdInfo[] = array(__('支付平台','waraPayi18N'), $this->gateName());
                    $this->arrOrdInfo[] = array(__('下单时间','waraPayi18N'), $this->ordInfo['otime']);
                    $headers            = '';
                    break;
                case 'PAY_SUCCESS': //支付成功
                    if ($isSeller) {
                        $this->tip['SUBJECT'] = __('[管理员]交易状态已改变为:买家付款成功','waraPayi18N');
                        $this->tip['MSG_20']  = __('买家已经付款成功','waraPayi18N');
                        $this->tip['MSG_03']  = __('有一笔交易已经支付成功!','waraPayi18N');
                    } else {
                        $this->tip['SUBJECT'] = __('交易状态已改变为:付款成功','waraPayi18N');
                        $this->tip['MSG_20']  = __('您已经支付成功!<br />(为提供交易依据,该邮件已同时发送给买家和卖家)','waraPayi18N');
                    }
                    $this->arrOrdInfo[] = array(__('商户订单','waraPayi18N'), $this->ordInfo['series']);
                    $this->arrOrdInfo[] = array(__('平台订单','waraPayi18N'), $this->ordInfo['platTradeNo']);
                    $this->arrOrdInfo[] = array(__('商品名称','waraPayi18N'), $this->proInfo['name']);
                    $this->arrOrdInfo[] = array(__('商品单价','waraPayi18N'), $this->ordInfo['payprice']);
                    $this->arrOrdInfo[] = array(__('购买数量','waraPayi18N'), $this->ordInfo['buynum']);
                    $this->arrOrdInfo[] = array(__('商品运费','waraPayi18N'), $this->ordInfo['freight']);
                    $this->arrOrdInfo[] = array(__('支付总计','waraPayi18N'), $this->ordInfo['ordfee']);
                    $this->arrOrdInfo[] = array(__('支付平台','waraPayi18N'), $this->gateName());
                    if (!empty($this->ordInfo['sendsrc'])) {
                        $this->arrProInfo[] = array('自动发货', $this->ordInfo['sendsrc']);
                    }
                    if (!empty($this->proInfo['download'])) {
                        $this->arrProInfo[] = array('下载地址', $this->proInfo['download']);
                    }
                    if (!empty($this->proInfo['zipcode'])) {
                        $this->arrProInfo[] = array('解压密码', $this->proInfo['zipcode']);
                    }
                    if (!empty($this->proInfo['emailtip'])) {
                        $this->arrProInfo[] = array('其他说明', $this->proInfo['emailtip']);
                    }
                    //$this->arrProInfo[] = array('生效时间', $this->ordInfo['stime']);
                    //$this->arrProInfo[] = array('失效时间', $this->endTime());
                    break;
                //FOR ADMINISTRATOR
                case 'PROLESS': //商品余量不足
                    $this->tip['SUBJECT'] = __('[管理员]商品余量不足, 请及时补充!','waraPayi18N');
                    $this->tip['MSG_12']  = __('管理员','waraPayi18N');
                    $this->tip['MSG_03']  = __('您有一个新订单！','waraPayi18N');
                    $this->tip['MSG_20']  = __('仓库余量不足。','waraPayi18N');
                    $this->arrOrdInfo[]   = array(__('商户编号','waraPayi18N'), $this->proInfo['proid']);
                    $this->arrOrdInfo[]   = array(__('商品名称','waraPayi18N'), $this->proInfo['name']);
                    $this->arrOrdInfo[]   = array(__('商品描述','waraPayi18N'), $this->proInfo['description']);
                    $this->arrOrdInfo[]   = array(__('商品单价','waraPayi18N'), $this->proInfo['price']);
                    $this->arrOrdInfo[]   = array(__('剩余数量','waraPayi18N'), $this->proInfo['num']);
                    //$this->arrOrdInfo[] = array('管理地址', '');
                    break;
                //FOR ADMINISTRATOR
                case 'ORD_NOT_FOUND'; //订单丢失
                    $this->tip['SUBJECT'] = __('[管理员]重要提醒:订单缺失, 等待发货','waraPayi18N');
                    $this->tip['MSG_12']  = __('管理员','waraPayi18N');
                    $this->tip['MSG_03']  = __('您有一笔交易,客户已经支付成功！但交易还未结束.','waraPayi18N');
                    $this->tip['MSG_20']  = __('买家已经完成付款, 但订单已经缺失, 当前处于"等待发货"状态。<br />请积极与客户取得联系!人工完成发货(客户的信息可以在支付平台上查询到)','waraPayi18N');
                    break;
                //FOR ADMINISTRATOR
                case 'PRO_NOT_FOUND'; //商品丢失
                    $this->tip['SUBJECT'] = __('[管理员]重要提醒:商品缺失, 等待发货','waraPayi18N');
                    $this->tip['MSG_12']  = __('管理员','waraPayi18N');
                    $this->tip['MSG_03']  = __('您有一笔交易,客户已经支付成功！但交易还未结束.','waraPayi18N');
                    $this->tip['MSG_20']  = __('买家已经完成付款, 但订购的商品已经缺失, 当前处于"等待发货"状态。<br />请积极与客户取得联系!人工完成发货(客户的信息可以在支付平台上查询到)','waraPayi18N');
                    break;
                //FOR ADMINISTRATOR
                case 'SRC_EMPTY': //货源为空
                    $this->tip['SUBJECT'] = __('[管理员]重要提醒:货源为空, 等待发货','waraPayi18N');
                    $this->tip['MSG_12']  = __('管理员','waraPayi18N');
                    $this->tip['MSG_03']  = __('您有一笔交易,客户已经支付成功！但交易还未结束.','waraPayi18N');
                    $this->tip['MSG_20']  = __('买家已经完成付款, 但订购的商品自动货源已经为空, 当前处于"等待发货"状态。<br />请积极与客户取得联系!人工完成发货(客户的信息可以在支付平台上查询到)','waraPayi18N');
                    break;
            }
            //die($this->generateHtml());
            return wp_mail($receiver, $this->tip['SUBJECT'], $this->generateHtml(), $headers);
        }

        function generateHtml()
        {
            $siteName   = get_bloginfo('name');
            $siteUrl    = get_bloginfo('url');
            $supportUrl = waraPay_get_setting('link_support');
            if (!filter_var($supportUrl, FILTER_VALIDATE_URL)) {
                $supportUrl = $siteUrl;
            }
            $ordInfoHtml = '';
            foreach ($this->arrOrdInfo as $li) {
                $ordInfoHtml .= '<li>' . $li[0] . ':&nbsp;&nbsp;' . $li[1] . '</li>';
            }
            $proInfoHtml = '';
            foreach ($this->arrProInfo as $li) {
                $proInfoHtml .= '<li>' . $li[0] . ':&nbsp;&nbsp;' . $li[1] . '</li>';
            }
            /////////////////////////////////////////////////////////////////////////////////////
            $html = <<<HTML
<div style="width:860px;margin:0 auto;background:#F0FCFB;padding-top:30px">
<div style="width:700px;margin:0 auto;background:#5A5A5A;padding:5px;border-radius:7px 7px 0 0;">
<div style="width:660px;_width:680px;margin:0 auto;background:#FFF;padding:20px;border-radius:10px 10px 0 0;">

<div style="width:auto;padding:0 10px;margin:0 auto;">
	<div style="line-height:1.5;font-size:14px;margin-bottom:25px;color:#4d4d4d;">
		<strong style="display:block;margin-bottom:15px;">{$this->tip['MSG_01']}{$this->tip['MSG_12']}，{$this->tip['MSG_02']}</strong>

    </div>

	<div style="margin-bottom:30px;color:#FF7400;">
		<p style="text-indent:2em">{$this->tip['MSG_03']}{$this->tip['MSG_04']}</p>
    </div>

HTML;
            /////////////////////////////////////////////////////////////////////////////////////
            $html .= <<<HTML

    <div style="margin-bottom:30px;background-color:#f4f4f4;font-size:14px;color:#4d4d4d;line-height:1.5;padding:10px;border-radius:5px">
		<strong style="display:block;margin-bottom:15px;">{$this->tip['MSG_13']}</strong>
   		<p style="color:#4d4d4d;">{$this->tip['MSG_20']}</p>	
    </div>
        
HTML;
            /////////////////////////////////////////////////////////////////////////////////////
            if (!empty($this->arrOrdInfo)) {
                $html .= <<<HTML

    <div style="margin-bottom:30px;background-color:#f4f4f4;font-size:14px;color:#4d4d4d;line-height:1.5;padding:10px;border-radius:5px">
   		<strong style="display:block;margin-bottom:15px;">{$this->tip['MSG_05']}</strong>
		<ul style="list-style:none;margin-left:30px">
			$ordInfoHtml
        </ul>
    </div>

HTML;
            }
            /////////////////////////////////////////////////////////////////////////////////////
            if (!empty($this->arrProInfo)) {
                $html .= <<<HTML

    <div style="margin-bottom:30px;background-color:#f4f4f4;font-size:14px;color:#4d4d4d;line-height:1.5;padding:10px;border-radius:5px">
   		<strong style="display:block;margin-bottom:15px;">{$this->tip['MSG_06']}</strong>
		<ul style="list-style:none;margin-left:30px">
            $proInfoHtml
        </ul>
    </div>

HTML;
            }
            /////////////////////////////////////////////////////////////////////////////////////
            $html .= <<<HTML

	<div style="padding:10px 10px 0;border-top:1px solid #ccc;color:#999;margin-bottom:20px;line-height:1.3em;font-size:12px;">
		<p style="margin-bottom:15px;">{$this->tip['MSG_07']}<br>
		{$this->tip['MSG_08']}</p>
		<p>{$this->tip['MSG_09']}
		<a target="_blank" style="color:#666;text-decoration:none;" href="$supportUrl">
		{$this->tip['MSG_10']}</a>&nbsp; {$this->tip['MSG_11']}
		<a target="_blank" style="color:#666;text-decoration:none;" href="$siteUrl">
		$siteUrl</a><br>
		Copyright $siteName 2010-2012 All Rights Reserved</div>
</div>

</div></div></div>
		
HTML;
            return $html;
        }

        //TOOLS
        function gateName($en = null)
        {
            !empty($en) || $en = $this->ordInfo['paygate'];
            switch (strtoupper($en)) {
                case 'WARAPAY':
                    $name = 'waraPay';
                    break;
                case 'ALIPAY':
                    $name = __('支付宝','waraPayi18N');
                    break;
                case 'WECHAT':
                    $name = __('微信','waraPayi18N');
                    break;
                case 'PAYPAL':
                    $name = __('PayPal','waraPayi18N');
                    break;
                default:
                    $name = $en;
            }
            return $name;
        }

        function endTime()
        {
            if (empty($this->ordInfo['endTime'])) {
                return '不失效';
            } else {
                return $this->formatTime($this->ordInfo['endTime']);
            }
        }

        function formatTime($timestamp)
        {
            return date('Y-m-d H:i:s', $timestamp);
        }
    }
endif;





