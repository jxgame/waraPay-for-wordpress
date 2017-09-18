<?php



/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
 * @param $para 需要拼接的数组
 *              return 拼接完成以后的字符串
 */
function waraPay_createLinkstring($para)
{
    $arg = "";
    while (list ($key, $val) = each($para)) {
        $arg .= $key . "=" . $val . "&";
    }
    //去掉最后一个&字符
    $arg = substr($arg, 0, count($arg) - 2);
    //如果存在转义字符，那么去掉转义
    if (get_magic_quotes_gpc()) {
        $arg = stripslashes($arg);
    }
    return $arg;
}

/**
 * 除去数组中的空值和签名参数
 * @param $para 签名参数组
 *              return 去掉空值与签名参数后的新签名参数组
 */
function waraPay_paraFilter($para)
{
    $para_filter = array();
    while (list ($key, $val) = each($para)) {
        if ($key == "sign" || $key == "sign_type" || $val == "") {
            continue;
        } else {
            $para_filter[$key] = $para[$key];
        }
    }
    return $para_filter;
}

/**
 * 对数组排序
 * @param $para 排序前的数组
 *              return 排序后的数组
 */
function waraPay_argSort($para)
{
    ksort($para);
    reset($para);
    return $para;
}

/**
 * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
 * 注意：服务器需要开通fopen配置
 *
 * @param $word 要写入日志里的文本内容 默认值：空值
 */
function waraPay_logResult($word = '')
{
    $fp = fopen("log.txt", "a");
    flock($fp, LOCK_EX);
    fwrite($fp, "time：" . strftime("%Y%m%d%H%M%S", time()) . "\n" . $word . "\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

/**
 * 远程获取数据
 * 注意：该函数的功能可以用curl来实现和代替。curl需自行编写。
 * $url 指定URL完整路径地址
 *
 * @param $input_charset 编码格式。默认值：空值
 * @param $time_out      超时时间。默认值：60
 *                       return 远程输出的数据
 */
function waraPay_getHttpResponse($url, $input_charset = '', $time_out = "60")
{
    $urlarr       = parse_url($url);
    $errno        = "";
    $errstr       = "";
    $transports   = "";
    $responseText = "";
    if ($urlarr["scheme"] == "https") {
        $transports     = "ssl://";
        $urlarr["port"] = "443";
    } else {
        $transports     = "tcp://";
        $urlarr["port"] = "80";
    }
    if (trim($input_charset) !== '') {
        $url .= '&_input_charset=' . $input_charset;
    }
    $res          = wp_remote_post($url);
    $responseText = wp_remote_retrieve_body($res);
    return $responseText;
    /*
    $fp=@fsockopen($transports . $urlarr['host'],$urlarr['port'],$errno,$errstr,$time_out);
    if(!$fp) {
      die("ERROR: $errno - $errstr<br />\n");
    } else {
      if (trim($input_charset) == '') {
        fputs($fp, "POST ".$urlarr["path"]." HTTP/1.1\r\n");
      }
      else {
        fputs($fp, "POST ".$urlarr["path"].'?_input_charset='.$input_charset." HTTP/1.1\r\n");
      }
      fputs($fp, "Host: ".$urlarr["host"]."\r\n");
      fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
      fputs($fp, "Content-length: ".strlen($urlarr["query"])."\r\n");
      fputs($fp, "Connection: close\r\n\r\n");
      fputs($fp, $urlarr["query"] . "\r\n\r\n");
      while(!feof($fp)) {
        $responseText .= @fgets($fp, 1024);
      }
      fclose($fp);
      $responseText = trim(stristr($responseText,"\r\n\r\n"),"\r\n");

      return $responseText;
    }
    */
}

function waraPay_getHttpResponseCURL($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function tenpay_tradeid()
{
    //date_default_timezone_set(PRC);
    $strDate = date("Ymd");
    $strTime = date("His");
    //4位随机数
    $randNum = rand(1000, 9999);
    //10位序列号,可以自行调整。
    $strReq = $strTime . $randNum;
    /* 商家订单号,长度若超过32位，取前32位。财付通只记录商家订单号，不保证唯一。 */
    $sp_billno = $strReq;
    /* 财付通交易单号，规则为：10位商户号+8位时间（YYYYmmdd)+10位流水号 */
    $transaction_id = $bargainor_id . $strDate . $strReq;
    return $transaction_id;
}

function createLinkstringUrlencode($para)
{
    $arg = "";
    while (list ($key, $val) = each($para)) {
        $arg .= $key . "=" . urlencode($val) . "&";
    }
    //去掉最后一个&字符
    $arg = substr($arg, 0, count($arg) - 2);
    //如果存在转义字符，那么去掉转义
    if (get_magic_quotes_gpc()) {
        $arg = stripslashes($arg);
    }
    return $arg;
}

