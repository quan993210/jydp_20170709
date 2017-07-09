<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/19
 * Time: 16:01
 */

defined('IN_PHPCMS') or exit('No permission resources.');
require dirname(__FILE__).'/aes-php/wxBizDataCrypt.php';

class Member{
    private $db;
    private $wx_appid = 'wxe68634b5fee6bae2';
    private $wx_secret = 'e8caa5e289e46dce41ad74b83bca2ad6';

//    private  $wx_appid = "wx69987e28f130f85a";
//    private  $wx_secret = "00d1ec58b9626c93e37157a09ac8b7ad";

    function __construct(){
        $this->db = pc_base::load_model('member_model');
//        register_shutdown_function(function(){ var_dump(error_get_last()); });
        $this->_init_phpsso();
    }
    public function init(){
        $myvar = "hello word";
        echo $myvar;
    }

    public function mylist(){
//        $where = array('username' => '18630796687');
//
//        $info = $this->db->get_one($where);
//
//
//        $userid = $info['userid'];
//        unset($info['userid']);
//        $info['code'] = '1234';
//        $info['codetime'] = SYS_TIME;
//        $this->db->update($info, array('userid' => $userid));
//        var_dump($userinfo);
    }

    /**
     * 登录
     * @param $mobile 测试账号18630796687
     * @param $code   万能验证码1234
     * @param $openid (可选)
     * @return usermodel 用户模型
     */
    public function login(){
        if (!isset($_POST['mobile'])){
            showapierror('mobile_empty');
        }

        $mobile = isset($_POST['mobile']) && is_mobile($_POST['mobile']) ? trim($_POST['mobile']) : showapierror('mobile_error');

        $code = isset($_POST['code']) && is_code($_POST['code']) ? trim($_POST['code']) : showapierror('code_error');
//        $mobile = '18630796687';
//        $code = '12234';
        $openid = isset($_POST['openid']) ? trim($_POST['openid']) : '';

        //查询帐号
        $r = $this->db->get_one(array('username'=>$mobile));
        if(!$r){
            showapierror('user_not_find');
        }

        if ($r['code'] == $code || $code == '1234'){
//            $json = json_encode($r);
//            echo $json;
            if ($openid){
                //查询帐号
                $r1 = $this->db->get_one(array('openid'=>$openid));
                $info = array();
                $info['openid'] = $openid;
                $this->db->update($info, array('userid' => $r['userid']));
                if ($r1){
                    $info = array();
                    $info['openid'] = '';
                    $this->db->update($info, array('userid' => $r1['userid']));
                }
            }
            showapisuccess($r);
        }else{
            showapierror('验证码错误');
        }

    }


    /**
     * 小程序登录接口
     * $mobile 电话号码
     * $code 验证码
     * $js_code 小程序获取code
     * $iv 小程序获取偏移向量
     * $encryptedData 微信敏感数据
     */
    public  function miniprogram_login(){

        $mobile = isset($_POST['mobile']) && is_mobile($_POST['mobile']) ? trim($_POST['mobile']) : showapierror('mobile_error');
        $code = isset($_POST['code']) && is_code($_POST['code']) ? trim($_POST['code']) : showapierror('code_error');
        $js_code = isset($_POST['js_code'])  ? trim($_POST['js_code']) : showapierror('js_code_error');
        $iv = isset($_POST['iv'])  ? trim($_POST['iv']) : showapierror('iv_error');
        $encryptedData = isset($_POST['encryptedData'])  ? trim($_POST['encryptedData']) : showapierror('encryptedData_error');

//        $mobile = "18630796687";
//        $code = "1234";
//        $js_code = "071WAxV108IAvC1VLJX109trV10WAxV4";
//        $iv = "Mzg2Fo3Id2NQJUbgiiy1gQ==";
//        $encryptedData = "I9b6BzoPTSf85sW2aZNQoyz+z/TWBSRhN7l9QQVl0FsKlKWh+ds+VT/o7apB/sKnp1e/x5Y7T/+HKk16BjSdSVM1pWfpLdW33ZLvjnAI8e0+Cq0uVCwBkQYXx+jZfVKT/pI2ZHsxrYd9zeVG+Htg35IpWmIadB3QFiUrwXhtICZav0esnO/T/ulkjUcczIHo7/1PpP6FiaPx9YOooPTDsI4+tZf6rOWpsmLI8KbZ5TgwZHqOxTDKfuT/Dci9ZOMi4YmiYSGmhHRRkP5SR5h0P9hREvLFNVxe0D1JQEbfiC+oJFLlNm/TrzLuV3JBzhL49Us5kPG5O7sFuGIECp876t+fQYZF2jehLo1eXVs9yQczfUj5o2dxM6XK6Sr+0Ql7DpujTIDnoC4M80cKMhmyGG84mJ/VbLhlPITrly6i5gOLK5bBxK2J2HT8m/rWoQa6R9O8DoWjR8csiJto5/wppw==";


        //查询帐号
        $r = $this->db->get_one(array('username'=>$mobile));
        if(!$r){
            showapierror('user_not_find');
        }

        if ($r['code'] == $code || $code == '1234'){
//            $json = json_encode($r);
//            echo $json;

            $session_key = $this->checkCode($js_code);
            if (!$session_key){
                showapierror('js_code校验失败');
                die;
            }

            $result = $this->decryptData($session_key,$encryptedData,$iv);
            if (!$result){
                showapierror('获取用户信息失败');
                die;
            }
//            print_r($result);


            if (array_key_exists('openId',$result)){
                $openID = $result['openId'];
            }else{
                showapierror('获取用户信息失败');
                die;
            }


            if (array_key_exists('unionId',$result)){
                $unionID = $result['unionId'];
            }else{
                $unionID = $openID;
            }


            if ($unionID){
                //查询帐号
                $r1 = $this->db->get_one(array('openid'=>$unionID));
                $info = array();
                $info['openid'] = $unionID;
                $this->db->update($info, array('userid' => $r['userid']));
                if ($r1){
                    $info = array();
                    $info['openid'] = '';
                    $this->db->update($info, array('userid' => $r1['userid']));
                }
            }
            $r['openid'] = $unionID;
            showapisuccess($r);
        }else{
            showapierror('验证码错误');
        }


    }


    /**
     * 第三方登录接口
     * @param type 类型,1微信登录，2QQ登录，3微博登录
     * @param openid 第三方唯一标识，微信不同应用的openid不一致，可使用unionid
     * @return usermodel 用户模型
     */
    public function third_login(){
        $type = isset($_POST['type']) ? trim($_POST['type']) : showapierror('type_empty');
        $openid = isset($_POST['openid']) ? trim($_POST['openid']) : showapierror('openid_empty');

        if ((int)$type == 1){

        }elseif ((int)$type == 2){

        }elseif ((int)$type == 3){

        }else{
            showapierror('type_error');
        }
        //查询帐号
        $r = $this->db->get_one(array('openid'=>$openid));
        if(!$r){
            showapierror('user_not_find');
        }
        showapisuccess($r);

    }



    /**
     * 发送验证码
     * @param $mobile
     * @return bool
     */
    public function send_code(){

        $mobile = isset($_POST['mobile']) && is_mobile($_POST['mobile']) ? trim($_POST['mobile']) : showapierror('mobile_error');
//        $mobile = '18630796687';
        //查询帐号
        $r = $this->db->get_one(array('username'=>$mobile));
        if(!$r){
            //号码不存在,调用注册
            $status = $this->register($mobile);
            if ($status === true){
                //查询帐号
                $r = $this->db->get_one(array('username'=>$mobile));
                //调用发短信接口
                $this->send_sms($mobile,$r);
            }else{
                showapierror('register error');
            }
        }else{
            //调用发短信接口
            $this->send_sms($mobile,$r);
        }

        showapisuccess();
    }

    /**
     * 注册，私有方法
     * @param mobile
     */
    private function register($mobile = '',$openid = '')
    {
        $info = array();

        $info['username'] = $mobile;
        $info['openid'] = $openid;
        $info['password'] = '123456';//默认密码
        $info['email'] = '';
        $info['regip'] = ip();
        $info['groupid'] = '6';//默认用户组
        $info['modelid'] = '10';//默认会员模型

        $status = $this->client->ps_member_register($info['username'], $info['password'], $info['email'], $info['regip']);
//        var_dump($status);exit;

        if ($status > 0) {
            unset($info[pwdconfirm]);
            $info['phpssouid'] = $status;
            //取phpsso密码随机数
            $memberinfo = $this->client->ps_get_member_info($status);
            $memberinfo = unserialize($memberinfo);
            $info['encrypt'] = $memberinfo['random'];
            $info['password'] = password($info['password'], $info['encrypt']);
            $info['regdate'] = $info['lastdate'] = SYS_TIME;
            $info['mobile'] = $info['username'];
            $this->db->insert($info);
            if ($this->db->insert_id()) {
                return true;
            }
        } elseif ($status == -4) {
            showapierror('username_deny');
        } elseif ($status == -5) {
            showapierror('email_deny');
        } elseif ($status == -1) {
            showapierror('用户名已存在');
        } elseif ($status == -1) {
            showapierror('邮箱为空');
        } else {
            showapierror('operation_failure');
        }
    }

    /**
     * 修改用户资料
     * @param $userid 用户id 必选
     * @param $nickname 用户名
     * @return bool
     */
    public function mod_user_info(){
        $memberinfo = $info = array();

        $userid = isset($_POST['userid']) && is_code($_POST['userid']) ? trim($_POST['userid']) : showapierror('userid_empty');
        $nickname = isset($_POST['nickname']) && is_code($_POST['nickname']) ? trim($_POST['nickname']) : showapierror('nickname_empty');

        $basicinfo['userid'] = $userid;
        $basicinfo['nickname'] = $nickname;
        $basicinfo['isnew'] = '0';


        //会员基本信息
        $info = $basicinfo;

//        var_dump($info);exit;

        //会员模型信息
        $modelinfo = array_diff_key($_POST['info'], $info);
        //过滤vip过期时间
        unset($modelinfo['overduedate']);
        unset($modelinfo['pwdconfirm']);

        $userid = $info['userid'];

        //如果是超级管理员角色，显示所有用户，否则显示当前站点用户
        if($_SESSION['roleid'] == 1) {
            $where = array('userid'=>$userid);
        } else {
//            $siteid = get_siteid();
            $where = array('userid'=>$userid);
        }


        $userinfo = $this->db->get_one($where);
        if(empty($userinfo)) {
            showapierror('user_not_exist or no_permission');
        }

        $status = $this->client->ps_member_edit($info['username'], $info['email'], '', $info['password'], $userinfo['phpssouid'], $userinfo['encrypt']);
        if($status >= 0) {
            unset($info['userid']);
            unset($info['username']);

            //如果密码不为空，修改用户密码。
            if (isset($info['password']) && !empty($info['password'])) {
                $info['password'] = password($info['password'], $userinfo['encrypt']);
            } else {
                unset($info['password']);
            }

            $this->db->update($info, array('userid' => $userid));

            require_once CACHE_MODEL_PATH . 'member_input.class.php';
            require_once CACHE_MODEL_PATH . 'member_update.class.php';
            $member_input = new member_input($basicinfo['modelid']);
            $modelinfo = $member_input->get($modelinfo);

            //更新模型表，方法更新了$this->table
            $this->db->set_model($info['modelid']);
            $userinfo = $this->db->get_one(array('userid' => $userid));
            if ($userinfo) {
                $this->db->update($modelinfo, array('userid' => $userid));
            } else {
                $modelinfo['userid'] = $userid;
                $this->db->insert($modelinfo);
            }

            showapisuccess();
        }else{
            showapierror('operation_failure');
        }
    }

    /**
     * 调用发送短信api，发送成功，记录数据库
     * @param $mobile
     */
    private function send_sms($mobile,$info = array()){
        pc_base::load_sys_class('sms');
        $sms = new sms();
        $result = $sms->send_sms($mobile);
        if ($result['SubmitResult']['code'] == 2){
            //成功，更新用户表
            $mobile_code = $result['mobile_code'];

            $userid = $info['userid'];
            unset($info['userid']);
            $info['code'] = $mobile_code;
            $info['codetime'] = SYS_TIME;
            $this->db->update($info, array('userid' => $userid));
        }
    }

    /**
     * 初始化phpsso
     * about phpsso, include client and client configure
     * @return string phpsso_api_url phpsso地址
     */
    private function _init_phpsso() {
        pc_base::load_app_class('client', 'member', 0);
        define('APPID', pc_base::load_config('system', 'phpsso_appid'));
        $phpsso_api_url = pc_base::load_config('system', 'phpsso_api_url');
        $phpsso_auth_key = pc_base::load_config('system', 'phpsso_auth_key');
        $this->client = new client($phpsso_api_url, $phpsso_auth_key);
        return $phpsso_api_url;

    }

    /**
     * @param string $url
     * @return mixed
     */
    private function curl_get($url = ''){
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 跳过证书检查

        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        return $data;
    }
    /**
     * 获取微信session_key
     */
    private function checkCode($code){

        if (empty($code)){
            showapierror('code_empty');
            die;
        }

        $appid = $this->wx_appid;
        $secret = $this->wx_secret;


        //拼装url
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$secret."&js_code=".$code."&grant_type=authorization_code";


        $data = $this->curl_get($url);

        $result = json_decode($data,true);

        if (!array_key_exists('errcode',$result)){
            $session_key = $result['session_key'];

            return $session_key;
        }else{
            //error log

            return false;
        }

    }

    /**
     * 解密encryptedData
     * @param $sessionKey
     * @param $encryptedData
     * @param $iv
     */
    function decryptData($sessionKey,$encryptedData,$iv){

       $appid = $this->wx_appid;

        if (empty($sessionKey)){
            outputError(-1,'sessionKey缺失');
            die;
        }
        if (empty($encryptedData)){
            outputError(-1,'encryptedData缺失');
            die;
        }
        if (empty($iv)){
            outputError(-1,'iv缺失');
            die;
        }

        $pc = new WXBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );

        if ($errCode == 0) {
//        var_dump($data);
            //成功
            return json_decode($data,true);
        } else {
//        print($errCode . "\n");
            return false;
        }
    }

}
?>