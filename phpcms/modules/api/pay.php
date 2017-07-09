<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/31
 * Time: 15:12
 */
//register_shutdown_function(function(){ var_dump(error_get_last()); });
defined('IN_PHPCMS') or exit('No permission resources.');
require dirname(__FILE__).'/conf/common.php';
require dirname(__FILE__)."/lib/Autoload".CLASS_EXT;

//echo LIB_PATH;

class Pay{
    private $db;
    private $userdb;
    private $notifydb;
    private $logdb;
//    private $appid = "wxd678efh567hg6787";
//    private $mch_id = "1230000109";
    function __construct(){
        $this->db = pc_base::load_model('order_model');
        $this->logdb = pc_base::load_model('order_error_log_model');
        $this->notifydb = pc_base::load_model('pay_notify_model');
        $this->userdb = pc_base::load_model('member_model');
//      $this->db = pc_base::load_model('site_model');
    }

    /**
     * 创建订单
     * @param userid 用户id
     * @param channel 1微信，2支付宝(微信支付暂时未通)
     * @return 当channel为微信时，返回值预付订单信息，当择支付宝支付时，返回值为签名过的订单信息
     */
    public function create_order(){
        //查找用户信息
        $userid = isset($_POST['userid']) ? trim($_POST['userid']) : showapierror('userid_error');
        $channel = isset($_POST['channel']) ? trim($_POST['channel']) : showapierror('channel_error');
//        $userid = '23';
//        $channel = '2';

        $userinfo = $this->userdb->get_one(array('userid'=>$userid));
        if(!$userinfo){
            showapierror('user_not_find');
        }

//        $time = microtime(true);
        $ordersn = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

//        var_dump($ordersn);exit();
        

        $info = array(
            'ordersn' => $ordersn,
            'channel' => $channel,
            'userid' =>  $userinfo['userid'],
            'username' => $userinfo['nickname'],
            'mobile' => $userinfo['mobile'],
            'commodity' => "黑精年费",
            'total' => '0.01',
            'add_time' => SYS_TIME,
            'add_time_format' => SYS_TIME_FORMAT
        );

        $orderinfo = $this->db->insert($info);
        if (!$orderinfo){
            showapierror('order_error');
        }

        //微信支付
        if (intval($channel) == 1){
            $this->wx_pay($info);
        }elseif (intval($channel) == 2){//支付宝
            $this->ali_pay($info);
        }
    }

    /**
     * 微信支付
     */
    private function wx_pay($info){
        // 注册自动加载类
        spl_autoload_register("Autoload::autoload");

//        $body = filter_input(INPUT_POST, 'body');
        $body = $info['commodity'];
        $out_trade_no = $info['ordersn'];
        $total_fee = $info['total'];
        if (empty($body) || empty($out_trade_no) || empty($total_fee)) {
            showapierror('order_info_error');
        }

        $spbill_create_ip = ip();
        $body = urldecode($body);

        $data = array(
            'body'				=>	$body,
            'out_trade_no'		=>	$out_trade_no,
            'total_fee'			=>	$total_fee,
            'spbill_create_ip'	=>	$spbill_create_ip,
        );												/** 模拟数据 */

        $encpt = WeEncryption::getInstance();		//实例化签名类
        $url = WE_NOTIFY_URL;
        $encpt->setNotifyUrl($url);			//设置异步通知地址



        $curl = new Curl();				//实例化传输类；
        $xml_data = $encpt->sendRequest($curl, $data);		//发送请求
//
        $postObj = $encpt->xmlToObject($xml_data);			//解析返回数据
        var_dump($postObj);


    }

    /**
     * 微信支付回调(客户端不用调此接口)
     * @return 微信服务器通知订单状态变更地址，用于更改订单状态，并记录log
     */
    public function wx_notify(){
        // 注册自动加载类
        spl_autoload_register("Autoload::autoload");
        $responseData = file_get_contents("php://input");	//接受通知参数

        if (!$responseData){
            exit;
        }
        $encpt = WeEncryption::getInstance();
        $obj = $encpt->getNotifyData($responseData);
        if ($obj === false) {
            exit;
        }

        //初始化错误数组
        $error = array(
            'channel' => 1,//微信支付
            'data' => $responseData,
            'ip' => ip(),
            'add_time' => SYS_TIME,
            'add_time_format' => SYS_TIME_FORMAT
        );
        if (!empty($obj->return_code)) {
            if ($obj->return_code == 'FAIL') {
                $error['errcode'] = '100006';
                $error['errmsg'] = $obj->return_msg;
                $this->logdb->insert($error);
                return false;
            }
        }
        if ($obj) {
            $data = array(
                'appid'				=>	$obj->appid,
                'mch_id'			=>	$obj->mch_id,
                'nonce_str'			=>	$obj->nonce_str,
                'result_code'		=>	$obj->result_code,
                'openid'			=>	$obj->openid,
                'trade_type'		=>	$obj->trade_type,
                'bank_type'			=>	$obj->bank_type,
                'total_fee'			=>	$obj->total_fee,
                'cash_fee'			=>	$obj->cash_fee,
                'transaction_id'	=>	$obj->transaction_id,
                'out_trade_no'		=>	$obj->out_trade_no,
                'time_end'			=>	$obj->time_end
            );
            $error['ordersn'] = $obj->out_trade_no;
            //查询订单是否存在
            $orderinfo = $this->db->get_one(array('ordersn'=>$obj->out_trade_no));
            if (!$orderinfo){
                $error['errcode'] = '100001';
                $error['errmsg'] = '订单不存在';
                $this->logdb->insert($error);
                exit();
            }
            //判断交易金额是否正确
            if (intval($obj->total_fee) != $orderinfo['total']){
                $error['errcode'] = '100002';
                $error['errmsg'] = '订单金额不匹配';
                $this->logdb->insert($error);
                exit();
            }
            //判读appid是否正确
            if ($obj->appid != APPID){
                $error['errcode'] = '100003';
                $error['errmsg'] = 'appid不匹配';
                $this->logdb->insert($error);
                exit();
            }
            //
            if ($orderinfo['status'] == 1){
                $reply = "<xml>
					<return_code><![CDATA[SUCCESS]]></return_code>
					<return_msg><![CDATA[OK]]></return_msg>
				</xml>";
                echo $reply;
                exit();
            }

            //验证签名
            $sign = $encpt->getSign($data);
            if ($sign == $obj->sign) {
                $info = array();
                $info['remark'] = $obj->return_code;
                //更改交易状态
                //支付成功，订单成功
                $info['status'] = '1';
                $info['pay_time'] = SYS_TIME;
                $info['pay_time_format'] = SYS_TIME_FORMAT;
                $this->db->update($info,array('ordersn'=>$obj->out_trade_no));

                $reply = "<xml>
					<return_code><![CDATA[SUCCESS]]></return_code>
					<return_msg><![CDATA[OK]]></return_msg>
				</xml>";
                echo $reply;
                exit;
            }else{
                $error['errcode'] = '100007';
                $error['errmsg'] = '微信验签失败';
                $this->logdb->insert($error);
            }
        }
    }

    /**
     * 阿里支付
     */
    private function ali_pay($info){
        // 注册自动加载类
        spl_autoload_register("Autoload::autoload");

        $subject = $info['commodity'];  //商品标题
        $total_amount = $info['total']; //价格
        $out_trade_no = $info['ordersn'];


        if (empty($subject) || empty($total_amount) || empty($out_trade_no)) {
            showapierror('order_info_error');
        }

        // 将 subject 进行编码，防止中文出错
        $subject = urldecode($subject);


        // 业务参数数组
        $bizContent = array(
            "timeout_express"	=>	"30m",
            "product_code"		=>	"QUICK_MSECURITY_PAY",
            "total_amount"		=>	$total_amount,
            "subject"			=>	$subject,
            "out_trade_no"		=>	$out_trade_no
        );
        $bizContent = json_encode($bizContent);
        // 公共参数数组
        $sParam = array(
            'app_id'			=>	APP_ID,
            'method'			=>	'alipay.trade.app.pay',
            'charset'			=>	'utf-8',
            'sign_type'			=>	'RSA',
            'sign'				=>	'',
            'timestamp'			=>	date("Y-m-d H:i:s",time()),
            'version'			=>	'1.0',
            'notify_url'		=>	ALI_NOTIFY_URL,
            'biz_content'		=>	$bizContent
        );

        $encpt = new Encryption();		// 实例化支付宝支付类
        /** 设置私钥 */
        $encpt->setRsaPriKeyFile(dirname(__FILE__).PRIVATE_KEY);
        /** 获取签名 */
        $curl = new Curl();
        $content = $encpt->requestAlipay($sParam, $curl);
        if ($content){
            $array = array(
                'ordersn' => $out_trade_no,
                'alisign' => $content
            );
            showapisuccess($array);
        }else{
            showapierror('支付宝签名失败');
        }
    }

    /**
     * 支付宝回调
     * @return 支付宝服务器通知订单状态变更地址，用于更改订单状态，并记录log
     */
    public function ali_notify(){

        // 注册自动加载类
        spl_autoload_register("Autoload::autoload");

        $responseData = file_get_contents("php://input");	//接受通知参数
//        if (!$responseData){
//            exit;
//        }

//        //回调记录到数据库中
//        $notifyInfo = array(
//            'ordersn' => '',
//            'channel' => 2,
//            'data' => $responseData,
//            'add_time' => SYS_TIME,
//            'add_time_format' => SYS_TIME_FORMAT
//        );
//        $this->notifydb->insert($notifyInfo);

//        $ordersn = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $encpt = new Encryption();
        $encpt->setRsaPubKeyFile(dirname(__FILE__).ALIPAY_PUBLIC_KEY);
        /** 处理获取到的参数 */
        $rParam = $encpt->disposeResponseData($responseData);
        $stringToBeSign = $rParam['stringToBeSigned'];
        $signature = $rParam['signature'];
        /** 验证支付结果 */
        $res = $encpt->verify($stringToBeSign, $signature);
        if ($res) {
            //验签成功
            $ordersn = $_POST['out_trade_no'];//订单号
            $total = $_POST['total_amount'];//订单金额
            $appid = $_POST['app_id'];//appid
            $seller_id = $_POST['seller_id'];//商户号
            $trade_status= $_POST['trade_status'];//交易状态

            //回调记录到数据库中
            $notifyInfo = array(
                'ordersn' => $ordersn,
                'channel' => 2,
                'data' => $responseData,
                'add_time' => SYS_TIME,
                'add_time_format' => SYS_TIME_FORMAT
            );
            $this->notifydb->insert($notifyInfo);

            //初始化错误数组
            $error = array(
                'ordersn' => $ordersn,
                'channel' => 2,
                'data' => $responseData,
                'ip' => ip(),
                'add_time' => SYS_TIME,
                'add_time_format' => SYS_TIME_FORMAT
            );
            //查询订单是否存在
            $orderinfo = $this->db->get_one(array('ordersn'=>$ordersn));
            if (!$orderinfo){
                $error['errcode'] = '100001';
                $error['errmsg'] = '订单不存在';
                $this->logdb->insert($error);
                exit();
            }
            //判断交易金额是否正确
            if (intval($total) != $orderinfo['total']){
                $error['errcode'] = '100002';
                $error['errmsg'] = '订单金额不匹配';
                $this->logdb->insert($error);
                exit();
            }
            //判读appid是否正确
            if ($appid != APP_ID){
                $error['errcode'] = '100003';
                $error['errmsg'] = 'appid不匹配';
                $this->logdb->insert($error);
                exit();
            }
            //判断收款账户是否正确
            if ($seller_id != PID){
                $error['errcode'] = '100004';
                $error['errmsg'] = 'seller_id不匹配';
                $this->logdb->insert($error);
                exit();
            }

            if ($orderinfo['status'] == 1){
                echo 'success';
                exit();
            }

            $info = array();
            $info['remark'] = $trade_status;
            //更改交易状态
            if ($trade_status == 'TRADE_SUCCESS' ){
                //支付成功，订单成功
                $info['status'] = '1';
                $info['pay_time'] = SYS_TIME;
                $info['pay_time_format'] = SYS_TIME_FORMAT;
                $this->db->update($info,array('ordersn'=>$ordersn));
            }elseif ($trade_status == 'TRADE_FINISHED'){
                //交易完成，订单成功
                $info['status'] = '1';
                $info['pay_time'] = SYS_TIME;
                $info['pay_time_format'] = SYS_TIME_FORMAT;
                $this->db->update($info,array('ordersn'=>$ordersn));
            }elseif ($trade_status == 'TRADE_CLOSED'){
                //交易关闭，订单失败
                $info['status'] = '2';
                $this->db->update($info,array('ordersn'=>$ordersn));
            }else{
                //未知错误
                $info['status'] = '9';
                $this->db->update($info,array('ordersn'=>$ordersn));
                echo 'failure';exit();
            }
            echo 'success';
        }else{
            //初始化错误数组
            $error = array(
                'ordersn' => '',
                'channel' => 2,
                'data' => $responseData,
                'ip' => ip(),
                'add_time' => SYS_TIME,
                'add_time_format' => SYS_TIME_FORMAT
            );            //验签失败，记录log
            $error['errcode'] = '100005';
            $error['errmsg'] = '支付宝验签失败';
            $this->logdb->insert($error);
            echo '1005 failure';
        }
    }


}

?>