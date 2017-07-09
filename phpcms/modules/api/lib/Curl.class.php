<?php 

class Curl {

	private $ch;
	private $url;

	public function __construct(){
		$this -> ch = curl_init();
	}

	/**
	 * 设置一个请求链接
	 * @param  String $url 请求的地址
	 */
	public function setUrl($url){
		$this -> url = $url;
	}

	/**
	 * 设置请求属性
	 * @param  boolean $isHttps     是否采用https方式请求
	 * @param  string  $requestType 请求方式
	 */
	private function setopt($isHttps, $requestType, $data, $useCert = false){
		curl_setopt($this -> ch, CURLOPT_URL, $this -> url);
		curl_setopt($this -> ch, CURLOPT_HEADER, 0);
		curl_setopt($this -> ch, CURLOPT_RETURNTRANSFER, 1);
		if ($isHttps) {
			curl_setopt($this -> ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this -> ch, CURLOPT_SSL_VERIFYHOST, 2);
		}

        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($this->ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($this->ch,CURLOPT_SSLCERT, SSLCERT_PATH);
            curl_setopt($this->ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($this->ch,CURLOPT_SSLKEY, SSLKEY_PATH);
        }

		if (!empty($data)) {
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
		}

		if ($requestType == 'POST') {
			curl_setopt($this->ch, CURLOPT_POST, true);
		}
	}

	/**
	 * 执行一个请求
	 * @return resource 返回执行结果
	 */
	public function execute($isHttps = false, $requestType = 'GET', $data = null, $useCert = false){
		$this->setopt($isHttps, $requestType, $data, $useCert);
		$content = curl_exec($this->ch);
		$this->close();
		if (!empty($content)) {
            return $content;
        } else {
            return curl_error($this->ch);
        }
	}

	/**
	 * 关闭一个请求资源句柄
	 */
	private function close(){
		curl_close($this -> ch);
	}
}