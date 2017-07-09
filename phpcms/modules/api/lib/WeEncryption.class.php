<?php 

class WeEncryption {

	private static $instance;
	private $sTpl;
	private $appid;
	private $mch_id;
	private $key;
	private $notify_url;
	private $trade_type = "APP";
	private static $details;

	/**
	 * 构造函数，初始化成员变量
	 * @param  String $appid  商户的应用ID
	 * @param  Int $mch_id 商户编号
	 * @param String $key 秘钥
	 */
	private function __construct() {
		if (is_string(APPID) && is_string(MCHID)) {
			$this->appid = APPID;
			$this->mch_id = MCHID;
			$this->key = APP_KEY;
		}
	}

    /**
     * 获取当前类实例
     * @return WeEncryption     本类实例
     */
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new Self();
		}
		return self::$instance;
	}

	/**
	 * 发送下单请求；
	 * @param  Curl   $curl 请求资源句柄
	 * @return mixed       请求返回数据
	 */
	public function sendRequest(Curl $curl, $data) {
		$data = $this->setSendData($data);
//		var_dump($data);

		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		$curl->setUrl($url);
		$content = $curl->execute(true, 'POST', $data);
		return $content;
	}

	/**
	 * 拼装请求的数据
	 * @return  String 拼装完成的数据
	 */
	private function setSendData($data) {
		$this->sTpl = "<xml>
	<appid><![CDATA[%s]]></appid>
	<body><![CDATA[%s]]></body>
	<mch_id><![CDATA[%s]]></mch_id>
	<nonce_str><![CDATA[%s]]></nonce_str>
	<notify_url><![CDATA[%s]]></notify_url>
	<out_trade_no><![CDATA[%s]]></out_trade_no>
	<spbill_create_ip><![CDATA[%s]]></spbill_create_ip>
	<total_fee><![CDATA[%d]]></total_fee>
	<trade_type><![CDATA[%s]]></trade_type>
	<sign><![CDATA[%s]]></sign>
</xml>";

		$nonce_str = $this->getNonceStr();
		$body = $data['body'];
		$out_trade_no = $data['out_trade_no'];
		$total_fee = $data['total_fee'];
		$spbill_create_ip = $data['spbill_create_ip'];
		$trade_type = $this->trade_type;

		$data['appid'] = $this->appid;
		$data['mch_id'] = $this->mch_id;
		$data['nonce_str'] = $nonce_str;
		$data['notify_url'] = $this->notify_url;
		$data['trade_type'] = $this->trade_type;
		$sign = $this->getSign($data);
		$data = sprintf($this->sTpl, $this->appid, $body, $this->mch_id, $nonce_str, $this->notify_url, $out_trade_no, $spbill_create_ip, $total_fee, $trade_type, $sign);
		return $data;
	}

	/**
	 * 设置通知地址
	 * @param  String $url 通知地址；
	 */
	public function setNotifyUrl($url) {
		if (is_string($url)) {
			$this->notify_url = $url;
		}
	}

	/**
	 * 获取签名；
	 * @return String 通过计算得到的签名；
	 */
	public function getSign($params) {
		ksort($params);
		foreach ($params as $key => $item) {
			if (!empty($item)) {
				$newArr[] = $key.'='.$item;
			}
		}
		$stringA = implode("&", $newArr);
		$stringSignTemp = $stringA."&key=".$this->key;
		$stringSignTemp = MD5($stringSignTemp);
		$sign = strtoupper($stringSignTemp);
		return $sign;
	}

	/**
	 * 获取随机数；
	 * @return String 返回生成的随机数；
	 */
	public function getNonceStr() {
		$code = "";
		for ($i=0; $i > 10; $i++) { 
			$code .= mt_rand(10000);
		}
		$nonceStrTemp = md5($code);
		$nonce_str = mb_substr($nonceStrTemp, 5,37);
		return $nonce_str;
	}

	/**
	 * 获取客户端支付信息
	 * @param  Array $data 参与签名的信息数组
	 * @return String       签名字符串
	 */
	public function getClientPay($data) {
		$sign = $this->getSign($data);
		return $sign;
	}

	/**
	 * 接收支付结果通知参数
	 * @return Object 返回结果对象；
	 */
	public function getNotifyData($postXml) {
		if (empty($postXml)) {
			return false;
		}
		$postObj = $this->xmlToObject($postXml);
		if ($postObj === false) {
			return false;
		}
		
		return $postObj;
	}

	/**
	 * 解析xml文档，转化为对象
	 * @param  String $xmlStr xml文档
	 * @return Object         返回Obj对象
	 */
	public function xmlToObject($xmlStr) {
		if (!is_string($xmlStr) || empty($xmlStr)) {
			return false;
		}
		$postObj = simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		$postObj = json_decode(json_encode($postObj));
		return $postObj;
	}
	
	public static function saveDetails($obj) {
		self::$details = $obj;
	}

	/**
	 * 查询订单状态
	 * @param  Curl   $curl         工具类
	 * @param  string $out_trade_no 订单号
	 * @return xml               订单查询结果
	 */
	public function queryOrder(Curl $curl, $out_trade_no) {
		$nonce_str = $this->getNonceStr();
		$data = array(
			'appid'		=>	$this->appid,
			'mch_id'	=>	$this->mch_id,
			'out_trade_no'	=>	$out_trade_no,
			'nonce_str'			=>	$nonce_str
			);
		$sign = $this->getSign($data);
		$xml_data = '<xml>
   <appid>%s</appid>
   <mch_id>%s</mch_id>
   <nonce_str>%s</nonce_str>
   <out_trade_no>%s</out_trade_no>
   <sign>%s</sign>
</xml>';
		$xml_data = sprintf($xml_data, $this->appid, $this->mch_id, $nonce_str, $out_trade_no, $sign);
		$url = "https://api.mch.weixin.qq.com/pay/orderquery";
		$curl->setUrl($url);
		$content = $curl->execute(true, 'POST', $xml_data);
		return $content;
	}

    /**
     * 查询退款状态
     * @param $out_trade_no     充值单号
     */
    public function refundQuery($out_trade_no)
    {
        $nonce_str = $this->getNonceStr();
        $signData = [
            'appid'         =>  APPID,
            'mch_id'        =>  MCHID,
            'nonce_str'     =>  $nonce_str,
            'sign'          =>  '',
            'out_trade_no'  =>  $out_trade_no
        ];
        $sign = $this->getSign($signData);
        $sData = '<xml>
                    <appid>'.APPID.'</appid>
                    <mch_id>'.MCHID.'</mch_id>
                    <nonce_str>'.$nonce_str.'</nonce_str>
                    <out_refund_no></out_refund_no>
                    <out_trade_no>'.$out_trade_no.'</out_trade_no>
                    <refund_id></refund_id>
                    <transaction_id></transaction_id>
                    <sign>'.$sign.'</sign>
                </xml>';
        $curl = new Curl();
        $curl->setUrl('https://api.mch.weixin.qq.com/pay/refundquery');
        $response = $curl->execute(true, 'GET', $sData);
        return $response;
	}
}