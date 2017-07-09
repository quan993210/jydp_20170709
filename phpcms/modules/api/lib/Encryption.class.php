<?php 

class Encryption {

	private $rsaPriKeyFile = null;
	private $rsaPubKeyFile = null;
	private $verifyStr = null;
    private $rsaPriKey = "MIIEpgIBAAKCAQEAvn0UZak1s8LU5ED29bqgSFZNrdMFJFXjXe+B+DJ6WPwd5nxH+2TdGiJETkKzZM20a/YClFKTdsmn/PZoCst7E51OT8Y/CJjg2GW13BmqUBlwPcX6PpZ/+uonrH152Hmby4irp1BpTlaUp0u9QvJ9Tz0BLH8j5PWOBQPPSynYlMaLh7unNy8a8P88WyIYFnkRS0dpbtFW8LX/A7KRulF5mb2w9BfgTWM/iMSUNniHGvIC/TAqnLAVq02p2bt8+SXic2kBnAPwUUvIeJ9O4MQBauHsWRsnsFMLqCfIMSZ4pkWdtEF1+44mUqsDnOgr/ZZ3CO/CY+nedOe/wh3yhk0hcQIDAQABAoIBAQCejROuLnJhhniW6C/mhokkzJjpZuwvu0F9jX47rSBC8s6carOrXt/eAcmi7kfNTp6vAdxRwo21YNHvbtVYrtdpkxR8uAD9UepMlBQT+FTXSOUwtZm+AJzTp5SQMPx7H3V6qu5dXEPajZw7x4HnuFhu3NMeS1EAIRctu2cOI+1+nne+v+dr83iLQrVim+8BjtyQoI2cyV9Z569lu42TvGZW3PeEGDHDuyQkguIf1Z9pUsQQW5lZorHDttxpYvP/uRewQBBD8f62RBM4Eu3vwAgRATrdAi6V7yG7ucz7xa6ze0MgmC26vqig75SSknMCSlMUzMGxU0ZdknCQf/FMreK1AoGBAOdpUkxWGarCLJWid6K+JS947FOaBewxtqvJ688JkY9PocyAIQ5ePuLp+YfmCX0REZBk3/Wnq4vIFs/9WWLW+m/NrZi65m503tWUVFUG0JNFs1idZfltJZyroULXoRhjNkdUEGvMsWLNrL4MMe2S72H/Uj54Se9FHnvH7ByIeRqfAoGBANK6m0dUKXhuCDWymdjMIPZRjOaQ7viAnN5fPIQ6xF0kBz3FOaxDeLSbV/oM3V3QZ7QeUwYoOSB3VSraHC3I4mm6oZwUi4ELEN6KxwDXPkCHYdW/ur05wCE5Pv26VcW9EJLRZAJdZYfqTixAGE590A9ZWcFLooa9RcQohZkXhFnvAoGBAM7tbA0ctMjv5wRLCmW5V/ESVWkQgplJfwowfi9dZA7da1Y9gNGjTuKDzIFMH1H5sFYJDgdRzmEpI9UKvCJZEApnxgKbpjBBS316rMp3VI1Mt2nXHXejtQ1an9HwlXoERWYU8rYijMBIIF608vR4/pwHvphj4eEWUoLnK+f71ScLAoGBALThVHPvfAFRgV6GRaRf41NLQMSQV2+bBguid9GQmAjS6hxNdGm3KJ7uUJ/j9weV4c4r0VwRnqeNht9XhKSQMDhM6HeO8wf7NamwOY0xNNAy5PmSr3nJXm+mMbmc/g7TcKx1a4DUJlh5+EvnYHXPbI4gRfizMXiycnYjyjlUhFilAoGBAKnN4I45ZVWnFsWfNN8R/zGKALZwbFmDha2Y4qaRTfFpBiG+R8/oMjiI4VpMvO14GPl5wWpfil1lKfJdd8s/JaE/+ymeBeJYc1p5xDtkTNIiMh0NaWQ4dLZGmjxJRAVJKGLwiaLZhuMx1H+Zv73jrpb05g/q6HFeIlMRIEz+Nmfu";
    private $rsaPubKey = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB";
//	private $rsaPubKey = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDIgHnOn7LLILlKETd6BFRJ0GqgS2Y3mn1wMQmyh9zEyWlz5p1zrahRahbXAfCfSqshSNfqOmAQzSHRVjCqjsAw1jyqrXaPdKBmr90DIpIxmIyKXv4GGAkPyJ/6FTFY99uhpiq0qadD/uSzQsefWo0aTvP/65zi3eof7TcZ32oWpwIDAQAB";
    /**
	 * 设置私钥文件路径
	 * @param  String $path 私钥文件路径
	 */
	public function setRsaPriKeyFile($path) {
		$this->rsaPriKeyFile = $path;
	}

	/**
	 * 设置公钥文件路径
	 * @param  String $path 公钥文件路径
	 */
	public function setRsaPubKeyFile($path) {
		$this->rsaPubKeyFile = $path;
	}

	/**
	 * 请求支付宝网关
	 * @return Mixed       返回请求到的内容；
	 */
	public function requestAlipay($sParam, Curl $curl) {
		$str = $this->getSignature($sParam);			// 获取签名
		return $str;
	}

	/**
	 * 获取待传输的数据
	 * @return String 待传输的数据
	 */
	public function getSignature($sParam) {
		$tempStr = $this->Assembling($sParam);		/** 拼接待签名字符串 */
		$sign = $this->Signature($tempStr);			/** 获取签名 */
		$encode_str = $this->Assembling($sParam, false, true);
		$str = $encode_str.'&sign='.rawurlencode($sign);
		return $str;
	}

	/**
	 * 待签名字符串拼接函数
	 * @param boolean $isEncode 是否进行url编码
	 */
	private function Assembling($params, $filterSignType = false, $isEncode = false) {
		/** 当filterSignType参数为真时，剔除sign_type参数 */
		if ($filterSignType) {
		    if (array_key_exists('sign_type',$params)){
                unset($params['sign_type']);
            }
		}
		ksort($params);			/** 将参数数组按照键的自然顺序排序 */
		$stringToBeSigned = "";		// 将要被签名的字符串
		foreach ($params as $k => $v) {
			if (false === empty($v) && 'sign' != $k) {
				// 转换成目标字符集
				$v = mb_convert_encoding($v, 'utf-8');
				$filterArr[] = ($isEncode) ? $k.'='.rawurlencode($v) : $k.'='.$v;
			}
		}

		$stringToBeSigned = implode("&", $filterArr);		//使用 & 连接参数

		unset ($k, $v);
		return $stringToBeSigned;
	}

	/**
	 * 获取参数签名方法
	 * @param  String $str 待签名的参数字符串
	 * @return mixed 
	 *         String：签名过的参数字符串
	 *         boolean：私钥文件不存在；
	 */
	private function Signature($tempStr) {
		//读取私钥文件
//		$priKey = file_get_contents($this->rsaPriKeyFile);
        $priKey = $this->rsaPriKey;
        $priKey=str_replace("-----BEGIN RSA PRIVATE KEY-----","",$priKey);
        $priKey=str_replace("-----END RSA PRIVATE KEY-----","",$priKey);
        $priKey=str_replace("\n","",$priKey);
        $priKey="-----BEGIN RSA PRIVATE KEY-----".PHP_EOL .wordwrap($priKey, 64, "\n", true). PHP_EOL."-----END RSA PRIVATE KEY-----";
        $res = openssl_get_privatekey($priKey);
		($res) or die('您使用的私钥格式错误，请检查RSA私钥配置'); 
		openssl_sign($tempStr, $sign, $res);
		openssl_free_key($res);
		$sign = base64_encode($sign);
		return $sign;
	}

	/**
	 * 处理支付宝异步通知参数
	 * @param  Array $rParam 异步通知的参数
	 * @return Array 返回包含待验签参数字符串和待验证签名；
	 */
	public function disposeResponseData($rParam) {
		/** 获取待签名字符串 */
		$stringToBeSigned = $this->Assembling($rParam, true, true);
		$stringToBeSigned = rawurlencode($stringToBeSigned);
		/** 获取sign节点内容 */
		$signature = base64_decode($rParam['sign']);
		$ret = array(
			'stringToBeSigned'	=>	$stringToBeSigned,
			'signature'			=>	$signature
			);
		return $ret;
	}

	/**
	 * 验证签名方法
	 * @param  string $stringToBeSigned 待验签的参数
	 * @param  string $signature        待验证的签名
	 * @return boolean                   验证结果
	 */
	public function verify($stringToBeSigned, $signature) {
		/** 使用RSA的验签方法，通过签名字符串、签名参数（经过base64解码）及支付宝公钥验证签名。 */
		if (file_exists($this->rsaPubKeyFile)) {
			//读取公钥文件
//			$pubKey = file_get_contents($this->rsaPubKeyFile);
            $pubKey = $this->rsaPubKey;
            $pubKey=str_replace("-----BEGIN PUBLIC KEY-----","",$pubKey);
            $pubKey=str_replace("-----END PUBLIC KEY-----","",$pubKey);
            $pubKey=str_replace("\n","",$pubKey);
            $pubKey="-----BEGIN PUBLIC KEY-----".PHP_EOL .wordwrap($pubKey, 64, "\n", true). PHP_EOL."-----END PUBLIC KEY-----";
			//转换为openssl格式密钥
			$res = openssl_get_publickey($pubKey);
			($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确'); 
			//调用openssl内置方法验签，返回bool值
			$result = (bool)openssl_verify($stringToBeSigned, base64_decode($signature), $res, OPENSSL_ALGO_SHA1);
			openssl_free_key($res);	//释放资源
			return $result;
		}
	}

    /**
     * 退单查询接口
     * @param $out_trade_no     退款单号
     * @return resource     查询结果
     */
    public function refundQuery($out_trade_no, $out_request_no)
    {
        $biz_Content = array(
            'out_trade_no'      =>  $out_trade_no,
            'out_request_no'    =>  $out_request_no
        );
        $bizContent = json_encode($biz_Content);
        $signData = array(
            'app_id'        =>  APP_ID,
            'method'        =>  'alipay.trade.fastpay.refund.query',
            'format'        =>  'JSON',
            'charset'       =>  'utf-8',
            'sign_type'     =>  'RSA',
            'sign'          =>  '',
            'timestamp'     =>  date('Y-m-d H:i:s',time()),
            'version'       =>  '1.0',
            'biz_content'   =>  $bizContent
        );

        /** 设置私钥 */
        $this->setRsaPriKeyFile(PRIVATE_KEY);

        $sign = $this->getSignature($signData);
        $curl = new Curl();
        $curl->setUrl('https://openapi.alipay.com/gateway.do');
        $response = $curl->execute(true, 'GET', $sign);
        return $response;
	}
}