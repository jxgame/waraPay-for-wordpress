<?php
require_once('cfg.config.php');
require_once('cls.info.php');
require_once('cls.mail.php');
class waraPayReturn
{

    var $para, $wpdb, $pro, $order, $ad, $proInfo, $ordInfo, $proid, $ordid, $opt, $mail;

    function __construct($para_ret)
    {
        $this->waraPayReturn($para_ret);
    }

    function waraPayReturn($para_ret)
    {
        global $wpdb;
        //储存参数数组对象
        $this->para = $para_ret;
        //储存数据库对象
        $this->wpdb = $wpdb;
        //创建商品对象
        $this->pro = new waraPay_product();
        //创建订单对象
        $this->order = new waraPay_order();
        //创建设置数组对象
        $this->opt = waraPay_get_settings();
        //创建邮件对象
        $this->mail = new waraPay_Mail();
    }

    function returnProcess()
    {
        //判断订单号是否存在(有可能被管理员删除了)
        $verifyOrder = $this->verifyOrder();
        //订单号不存在的情况下,给管理员和客户发送通知,主要内容需包含平台订单号和相应说明
        if (!$verifyOrder) {
            //wp_mail( $this->opt['notify_email'] , '发货失败提醒', '订单已经不存在,商品尚未发送给客户' );
            $this->mail->send($this->opt['notify_email'], 'ORD_NOT_FOUND', true);
            return 'ORD_NOT_FOUND';
        }
        //获取商品ID
        $verifyProid = $this->getProId();
        //商品已经被删除的情况下,给管理员和客户发送通知,主要内容需包含平台订单号和相应说明
        if (!$verifyProid) {
            //wp_mail( $this->opt['notify_email'] , '发货失败提醒', '商品已经不存在,商品尚未发送给客户' );
            $this->mail->send($this->opt['notify_email'], 'PRO_NOT_FOUND', true);
            return 'PRO_NOT_FOUND';
        }
        //获取商品信息
        $this->proInfo = $this->pro->get_info($this->proid);
        //获取订单ID
        $this->getOrdId();
        //获取订单信息
        $this->ordInfo = $this->order->get_info($this->ordid);
        //更新邮件参数
        $this->mail->refresh($this->proInfo, $this->ordInfo);
        //获取发货状态
        $status = $this->ordInfo['status'];
        //如果订单状态为已付款,那么跳出该函数.
        if ($status == 1) {
            return 'PAY_SUCCESS';
            //die();
        }
        $ordtype = $this->order->get('ordtype');
        $stime   = current_time('timestamp');
        if ($ordtype == 'ADP') { //ADP 处理
            $this->ad = new Alipay_Ads($this->proid);
            //重算有效时间
            $startTime = $stime;
            $endTime   = $startTime + $this->ordInfo['timeLong'];
            $newad     = array(
                'ordid'     => $this->ordInfo['ordid'],
                'imgSrc'    => $this->ordInfo['imgSrc'],
                'imgLink'   => $this->ordInfo['imgLink'],
                'imgMd5'    => $this->ordInfo['imgMd5'],
                'startTime' => $startTime,
                'endTime'   => $endTime,
                'timeLong'  => $this->ordInfo['timeLong'],
            );
            $this->ad->add($newad);
            $this->order->set('startTime', $startTime);
            $this->order->set('endTime', $endTime);
        } elseif ($ordtype == 'LINK') { //LINK 处理
            //重算有效时间
            $startTime         = $stime;
            $endTime           = $startTime + $this->ordInfo['timeLong'];
            $linkData          = array(
                'link_name'        => $this->ordInfo['linkName'],
                'link_url'         => $this->ordInfo['linkUrl'],
                'link_description' => $this->ordInfo['linkDesc'],
                'link_target'      => '_blank',
            );
            $insertLinkSuccess = wp_insert_link($linkData);
            $this->order->set('startTime', $startTime);
            $this->order->set('endTime', $endTime);
        }
        $toUpdateOrdInfo = array(
            'aliacc'      => $this->para['buyer_id'],
            'stime'       => date('Y-m-d H:i:s', $stime),
            'platTradeNo' => $this->para['plat_ordno'],
        );
        //更新状态
        $this->order->set('', '', $toUpdateOrdInfo);
        $this->ordInfo = $this->order->get_info($this->ordid);
        //更新邮件参数
        $this->mail->refresh($this->proInfo, $this->ordInfo);
        //自动发货处理
        $bln_autoSend = $this->proInfo['autosend'];
        $sended       = true;
        if ($bln_autoSend) {
            //获取自动发货源
            $sendSrc = $this->getSendSrc();
            //如果货源为空,给管理员和客户发送通知,主要内容需包含平台订单号和相应说明
            if (trim($sendSrc) == '') {
                $this->mail->send($this->opt['notify_email'], 'SRC_EMPTY', true);
                $sended = false;
            } else {
                $this->order->set('emailsend', 1);
                $this->order->set('sendsrc', $sendSrc);
                //更新邮件参数
                $this->ordInfo['sendsrc'] = $sendSrc;
                $this->mail->refresh($this->proInfo, $this->ordInfo);
                //更新数量
                $snumpre = $this->proInfo['snum'];
                $buynum  = $this->ordInfo['buynum'];
                $this->pro->set('num', (int)$snumpre + (int)$buynum);
                $num = $this->proInfo['num'];
                //echo $num;
                $this->pro->set('num', (int)$num - (int)$buynum);
            }
        } else {
            //更新数量
            $snumpre = $this->proInfo['snum'];
            $buynum  = $this->ordInfo['buynum'];
            $this->pro->set('num', (int)$snumpre + (int)$buynum);
            $num = $this->proInfo['num'];
            //echo $num;
            $this->pro->set('num', (int)$num - (int)$buynum);
        }
        if (waraPay_get_setting('buyer_pay_notify') && isset($this->ordInfo['email'])) {
            $this->mail->send($this->ordInfo['email'], 'PAY_SUCCESS', false);
        }
        if (waraPay_get_setting('seller_pay_notify')) {
            $this->mail->send($this->opt['notify_email'], 'PAY_SUCCESS', true);
        }
        if ($sended) {
            $this->order->set('status', '1');
        }
        return 'PAY_SUCCESS';
    }

    function send($who, $what)
    {
        wp_mail($who, 'SUBJECT', '$what');
    }

    ////验证订单号存在,如果存在则返回真,反之亦然
    private function verifyOrder()
    {
        return $this->order->exist('series', $this->para['out_ordno']);
    }

    ////验证商品号存在,如果存在则返回真,反之亦然
    private function getProId()
    {
        //return $this->proInfo['proid'];
        $ret         = $this->order->get('proid', 'series', $this->para['out_ordno']);
        $this->proid = $ret;
        return $ret;
    }

    ////获取商品ID
    private function getOrdId()
    {
        $ret         = $this->order->get('ordid', 'series', $this->para['out_ordno']);
        $this->ordid = $ret;
        return $ret;
    }

    ////获取自动发货源
    private function getSendSrc()
    {
        //获取购买数量
        $num = $this->ordInfo['buynum'];
        //获取货源分隔符
        $autosepori = $autosep = trim($this->proInfo['autosep']);
        //对正则表达式的特殊符号进行转义,加转义反斜杠
        $autosep = waraPay_preg_pre($autosep);
        //对于默认的空白分隔符进行默认换行分割.
        if ($autosep == '') {
            $autosep = '\r\n';
        }
        //根据分隔符将货源分割
        $arr_autosrc = preg_split("@$autosep@", $this->proInfo['autosrc']);
        //剔除无效的货源,这里剔除的是空行,其余的无效性无法判断
        $arr_autosrc = waraPay_filter_empty($arr_autosrc);
        //获得当前第一个货源
        //$sendsrc = array_shift( $arr_autosrc );
        //货源数量
        $numSrc = count($arr_autosrc);
        //重写发货数量
        $num = ($num < $numSrc) ? $num : $numSrc;
        for ($i = 1; $i <= $num; $i++) {
            $sendsrc   = array_shift($arr_autosrc);
            $sendSrc[] = $sendsrc;
        }
        if (empty($sendSrc)) {
            return '';
        }
        $sendSrcStr = implode('<br />----------------------------------------<br />', $sendSrc);
        //从货源中删除该货源,为数据更新做准备
        $backsrc = waraPay_array_reduce($arr_autosrc, $autosepori);
        //更新数据
        $this->pro->set('autosrc', $backsrc);
        return $sendSrcStr;
    }
}

