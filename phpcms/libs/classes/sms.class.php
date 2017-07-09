<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/22
 * Time: 10:59
 */

class sms{
    private $account;
    private $password;
    private $mobile;

    function __construct() {
        $this->account = 'C04496545';
        $this->password = '9e20f2d490900b34971834f1a40dc03b';
        $this->mobile = null;
    }

    public function send_sms($mobile = ''){
        //短信接口地址
        $url = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
        //获取手机号
//        $mobile = '18630796687';
        //生成的随机数
        $mobile_code = $this->random(4,1);
        $this->mobile = $mobile;
        if(empty($this->mobile)){
            exit('手机号码不能为空');
        }

        $post_data = "account=".$this->account."&password=".$this->password."&mobile=".$mobile."&content=".rawurlencode("您的验证码是：".$mobile_code."。请不要把验证码泄露给其他人。");

        $result =  $this->xml_to_array($this->curl_post($post_data, $url));
        $result['mobile_code'] = $mobile_code;
//        var_dump($result);
        return $result;
    }


    //请求数据到短信接口，检查环境是否 开启 curl init。
    private function curl_post($curlPost,$url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }

    //将 xml数据转换为数组格式。
    private function xml_to_array($xml){
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if(preg_match_all($reg, $xml, $matches)){
            $count = count($matches[0]);
            for($i = 0; $i < $count; $i++){
                $subxml= $matches[2][$i];
                $key = $matches[1][$i];
                if(preg_match( $reg, $subxml )){
                    $arr[$key] = $this->xml_to_array( $subxml );
                }else{
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }

    //random() 函数返回随机整数。
    private function random($length = 6 , $numeric = 0) {
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        if($numeric) {
            $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
            $max = strlen($chars) - 1;
            for($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }
}