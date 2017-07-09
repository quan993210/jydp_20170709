<?php
/**
 * Created by PhpStorm.
 * User: xkq
 * Date: 2017/6/23 0023
 * Time: 22:23
 * 添加销售记录
 */
defined('IN_PHPCMS') or exit('No permission resources.');
//模型缓存路径
define('CACHE_MODEL_PATH',CACHE_PATH.'caches_model'.DIRECTORY_SEPARATOR.'caches_data'.DIRECTORY_SEPARATOR);

class reward {

    function __construct() {
        $this->db_message = pc_base::load_model('message_model');
        $this->db_member = pc_base::load_model('member_model');
        $this->db_fans = pc_base::load_model('fans_model');
        $this->db_sales = pc_base::load_model('sales_model');
        $this->db_reward = pc_base::load_model('reward_model');
    }

    /**
     * 添加销售记录
     * @param userid    必填
     * @param card_no   选填
     * @param price    选填
     */
    public function add_sales($userid,$card_no=0,$price=0){
    if(empty($userid)){
        return false;
    }

    $member = $this->db_member->get_one(array('userid'=>$userid));
    $fans = $this->db_fans->get_one(array('userid'=>$userid),'pid');

    $data = array();
    $data['userid'] = $member['userid'];
    $data['nickname'] = $member['nickname'];
    $data['pid'] = $fans['pid'] ? $fans['pid'] : 0;
    $data['pid_nickname'] = $fans['pid_nickname'] ? $fans['pid_nickname'] : "";
    $data['card_no'] = $card_no;
    $data['price'] = $price;
    $data['addtime'] = SYS_TIME;
    $data['addtimes'] = SYS_TIME_FORMAT;
    if($this->db_sales->insert($data)){
        if($fans['pid']){
            //上级奖励
            $reward = $member['reward'] + 50;
            $this->db_member->update(array('reward'=>$reward),array('userid'=>$fans['pid']));
            //增加奖励流水
            $re = array();
            $re['userid'] = $fans['userid'];
            $re['nickname'] = $fans['nickname'];
            $re['reward'] = 50;
            $re['reward_format'] = '+50';
            $re['status'] = 'add';
            $re['type'] = '购买黑精卡';
            $re['addtime'] = SYS_TIME;
            $re['addtimes'] = SYS_TIME_FORMAT;
            $this->db_reward->insert($re);
        }


        //系统发送拓展粉丝消息
        $username= "系统";
        $tousername = $member['username'];
        $subject = "购买黑精卡";
        $time = date('Y-m-d H:i:s',time());
        $content = "您于".$time." 购买了黑精卡，有效期为1年，您已成为黑精卡会员尊享黑精特权，祝您生活愉快。";
        if($this->db_message->add_message($tousername,$username,$subject,$content,true)){
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }

}





}
?>