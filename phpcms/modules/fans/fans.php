<?php
/**
 * Created by PhpStorm.
 * User: xkq
 * Date: 2017/6/23 0023
 * Time: 20:32
 * 添加粉丝
 */

defined('IN_PHPCMS') or exit('No permission resources.');
//模型缓存路径
define('CACHE_MODEL_PATH',CACHE_PATH.'caches_model'.DIRECTORY_SEPARATOR.'caches_data'.DIRECTORY_SEPARATOR);

class fans {

    function __construct() {
        $this->db_message = pc_base::load_model('message_model');
        $this->db_member = pc_base::load_model('member_model');
        $this->db_site = pc_base::load_model('site_model');
        $this->db_fans = pc_base::load_model('fans_model');
        $this->db_heijingbi = pc_base::load_model('heijingbi_model');
        $this->db_sales = pc_base::load_model('sales_model');
    }


    /**
     * 添加粉丝
     * @param $userid    必填
     * @param $pid       必填
     * @param $siteid    必填
     * @param $source    选填
     */
    public function add_fans($userid,$pid,$siteid,$source)
    {
        if(empty($userid) || empty($pid) || empty($siteid)){
            return false;
        }

        $site = $this->db_site->get_one("siteid=$siteid");
        $member = $this->db_member->get_one(array('userid'=>$userid));
        $p_member = $this->db_member->get_one(array('userid'=>$pid));

        $data = array();
        $data['siteid'] = $siteid;
        $data['sitename'] = $site['name'];
        $data['userid'] = $member['userid'];
        $data['nickname'] = $member['nickname'];
        $data['pid'] = $p_member['userid'];
        $data['pid_nickname'] = $p_member['nickname'];
        $data['source'] = $source;
        $data['addtime'] = SYS_TIME;
        $data['addtimes'] = SYS_TIME_FORMAT;
        if($this->db_fans->insert($data)){
            //新增粉丝更新黑金币，添加流水记录
            $this->add_heijingbi($p_member['userid']);
            //系统发送拓展粉丝消息
            $username= "系统";
            $tousername = $p_member['username'];
            $subject = "拓展新粉丝";
            $content = "太棒了！您拓展了一位新粉丝，获得20黑精币，祝您生活愉快。";
            if($this->db_message->add_message($tousername,$username,$subject,$content,true)){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function add_heijingbi($userid)
    {
        $member = $this->db_member->get_one(array('userid'=>$userid));
        $heijingbi = $member['heijingbi'] + 20;
        $this->db_member->update(array('heijingbi'=>$heijingbi),array('userid'=>$member['userid']));
        //增加黑金币流水
        $heijing = array();
        $heijing['userid'] = $member['userid'];
        $heijing['nickname'] = $member['nickname'];
        $heijing['num'] = 20;
        $heijing['num_format'] = '+20';
        $heijing['status'] = 'add';
        $heijing['type'] = '新增粉丝';
        $heijing['addtime'] = SYS_TIME;
        $heijing['addtimes'] = SYS_TIME_FORMAT;
        if($this->db_heijingbi->insert($heijing)){
            return true;
        }

    }



}
?>