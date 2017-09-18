<?php
require_once('cfg.warapay.php');
class warapayService{ 
    var $config; 
    var $gatewayUrl;

    function __construct($config){
		$temp=array('CNY'=>'cn','KER'=>'kr','VND'=>'vn','JPY'=>'jp',);	
		$domain='https://wara-'.$temp[$config['wara_currency']].'.miguyouxi.com/';
		$this->gatewayUrl=$domain.'api/gateway.html';
		$this->paymentUrl=$domain.'api/payment.html';
		$this->appid = $config['wara_appid'];
		$this->warapay_public_key=$config['warapay_public_key'];		
		$this->app_private_key=$config['app_private_key'];
		$this->queryUrl=$domain.'api/query.html';
		$this->notify_url=$config['notify_url'];	
		$this->return_url=$config['return_url'];
		$this->wara_currency=$config['wara_currency'];
		
    }

    /**
     *
     * @return 表单提交HTML信息
     */
	public function qrPay($data){
		$data['v']="2.0";
		$data['t']=time();
		$post=array(
			'appid'=>$this->appid,
			'subject'=>$data['subject'],
			'body'=>$data['body'],	
			'buyer_email'=>$data['buyer_email'],	
			'notify_url'=>$this->notify_url,	
			'return_url'=>$this->return_url,	
		);
		unset($data['subject'],$data['body'],$data['buyer_email']);
		$post['data']=self::rsa(http_build_query($data));
		$result=$this->_post($this->gatewayUrl, 500000, http_build_query($post)); 
		$result=json_decode($result,320);
		if($result['code']==0){
			$array=array();
			$data=isset($result['data'])?$result['data']:array();
			$array['channel']=$data['channel'];
			$array['trade_no']=$data['trade_no'];
			$array['data']=self::rsa($data['channel'].'&'.$array['trade_no'].'&'.time());
			return self::_buildForm($array);
		}else{
			$result=json_encode($result,320);
		}
		return $result;
	}
	
	public function queryTrade($data=array()){
		$data['version']="2.0";
		$data['time']=time();
		$post=array(
			'appid'=>$this->appid,		
		);

		$post['data']=self::rsa(http_build_query($data));
		$result=$this->_post($this->queryUrl, 500000, http_build_query($post)); 
		
		return $result;
	}
	
	private function _buildForm($para, $method='get') {
		//待请求参数数组
		$sHtml = '';
		$sHtml .= "<form id='submit' name='submit' action='" . $this->paymentUrl . "' method='" . $method . "'>";
		while (list($key, $val) = each($para)) {
			$sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
		}
		$sHtml .= "</form>";
		$sHtml .= "<script>document.forms['submit'].submit();</script" . ">";
		return $sHtml;
	}
	//unPackage
	public function unPackage($data){ 
		$str=self::rsa($data,"DECODE"); 
		$arr=array();
		parse_str($str,$arr);
		return $arr;
	} 
	
	final private function rsa($string,$type="ENCODE"){
		if($type=="ENCODE"){
			if(!openssl_pkey_get_private($this->app_private_key)) die('app_private_key is empty or error.');
			if (openssl_private_encrypt($string, $encryptData, $this->app_private_key)){  
				return base64_encode($encryptData); 
			} else {
				self::EXITJSON(array('code'=>30000,'data'=>'encode error.'));	
			} 
		}elseif($type=="DECODE"){
			if(!openssl_pkey_get_public($this->warapay_public_key)) die('warapay_public_key is empty or error.');
			$decryptData ='';
			//var_dump(base64_decode($string));
			if (openssl_public_decrypt(base64_decode($string), $decryptData, $this->warapay_public_key)) {
				return $decryptData;  
			} else {
				self::EXITJSON(array('code'=>10004));	//解密失败
			}  
		}

	}
	public function getCurrencyFlag(){
		return self::currency($this->wara_currency);
	}
	
	public function currency($curr=''){
		$array=array(
			"KER"=>"한국 화폐(₩)",
			"CNY"=>"人民币(¥)",			
			"VND"=>"VIỆT NAM ĐỒNG(₫)",
			"USD"=>"Dollar($)",
			"GBP"=>"Pound(£)",
			"EUR"=>"Euro(€)",
			"HKD"=>"港幣(HK$)",
			"TWD"=>"新臺幣(NT$)",
			"JPY"=>"円(JPY¥)",
		);
		if($curr){
			return $array[$curr]?$array[$curr]:'unKown';
		}else{
			return	$array;
		}
	}
	
	/**
	 *  post数据
	 *  @param string $url		post的url
	 *  @param int $limit		返回的数据的长度
	 *  @param string $post		post数据，字符串形式username='dalarge'&password='123456'
	 *  @param string $cookie	模拟 cookie，字符串形式username='dalarge'&password='123456'
	 *  @param string $ip		ip地址
	 *  @param int $timeout		连接超时时间
	 *  @param bool $block		是否为阻塞模式
	 *  @return string			返回字符串
	 */
	
	private function _post($url, $limit = 0, $post = '', $cookie = '', $ip = '', $timeout = 15, $block = true) {
		$return = '';
		$matches = parse_url($url);
		$host = $matches['host'];
		$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
		$port = !empty($matches['port']) ? $matches['port'] : 80;
		$siteurl = $this->_get_url();
		if($post) {
			$out = "POST $path HTTP/1.1\r\n";
			$out .= "Accept: */*\r\n";
			$out .= "Referer: ".$siteurl."\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host\r\n" ;
			$out .= 'Content-Length: '.strlen($post)."\r\n" ;
			$out .= "Connection: Close\r\n" ;
			$out .= "Cache-Control: no-cache\r\n" ;
			$out .= "Cookie: $cookie\r\n\r\n" ;
			$out .= $post ;
		} else {
			$out = "GET $path HTTP/1.1\r\n";
			$out .= "Accept: */*\r\n";
			$out .= "Referer: ".$siteurl."\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Cookie: $cookie\r\n\r\n";
		}
		$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
		if(!$fp) return '';
	
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
	
		if($status['timed_out']) return '';	
		while (!feof($fp)) {
			if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n"))  break;				
		}
		
		$stop = false;
		while(!feof($fp) && !$stop) {
			$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
			$return .= $data;
			if($limit) {
				$limit -= strlen($data);
				$stop = $limit <= 0;
			}
		}
		@fclose($fp);
		
		//部分虚拟主机返回数值有误，暂不确定原因，过滤返回数据格式
		$return_arr = explode("\n", $return);
		if(isset($return_arr[1])) {
			$return = trim($return_arr[1]);
		}
		unset($return_arr);
		
		return $return;
	}
	

	/**
	 * 获取当前页面完整URL地址
	 */
	private function _get_url() {
		$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
		$php_self = $_SERVER['PHP_SELF'] ? $this->_safe_replace($_SERVER['PHP_SELF']) : $this->_safe_replace($_SERVER['SCRIPT_NAME']);
		$path_info = isset($_SERVER['PATH_INFO']) ? $this->_safe_replace($_SERVER['PATH_INFO']) : '';
		$relate_url = isset($_SERVER['REQUEST_URI']) ? $this->_safe_replace($_SERVER['REQUEST_URI']) : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$this->_safe_replace($_SERVER['QUERY_STRING']) : $path_info);
		return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
	}
	/**
	 * 安全过滤函数
	 *
	 * @param $string
	 * @return string
	 */
	private function _safe_replace($string) {
		$string = str_replace('%20','',$string);
		$string = str_replace('%27','',$string);
		$string = str_replace('%2527','',$string);
		$string = str_replace('*','',$string);
		$string = str_replace('"','&quot;',$string);
		$string = str_replace("'",'',$string);
		$string = str_replace('"','',$string);
		$string = str_replace(';','',$string);
		$string = str_replace('<','&lt;',$string);
		$string = str_replace('>','&gt;',$string);
		$string = str_replace("{",'',$string);
		$string = str_replace('}','',$string);
		$string = str_replace('\\','',$string);
		return $string;
	}
	
	function EXITJSON($array){
		exit(json_encode($array));
	}
}

