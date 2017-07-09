<?php
/**
 * Created by PhpStorm.
 * User: xkq
 * Date: 2017/6/20 0020
 * Time: 20:32
 * 个人中心记录
 */
defined('IN_PHPCMS') or exit('No permission resources.');

class Myrecord
{
    function __construct()
    {
        $this->db_category = pc_base::load_model('category_model');
        $this->db_content = pc_base::load_model('content_model');
        $this->db_message = pc_base::load_model('message_model');
        $this->db_member = pc_base::load_model('member_model');
        $this->db_chaopai_registre = pc_base::load_model('chaopai_registre_model');
        $this->db_site = pc_base::load_model('site_model');
        $this->db_fans = pc_base::load_model('fans_model');
        $this->db_heijingbi = pc_base::load_model('heijingbi_model');
        $this->db_sales = pc_base::load_model('sales_model');
        $this->db_reward = pc_base::load_model('reward_model');
        $this->db_withdrawals = pc_base::load_model('withdrawals_model');

    }

    /**
     * 我的席位列表
     * @param siteid  必填
     * @param page     选填
     */
    public function myxiwei()
    {
        $page = $_POST['page'] ? $_POST['page'] :1;
        $userid = intval($_POST['userid']);
        if($userid) {
            $infos = array();
            $where = "userid=$userid";
            $infos = $this->db_chaopai_registre->listinfo($where,'id desc',$page,10);
        }else{
            showapierror('参数错误！');
        }
        showapisuccess($infos);
    }

    /**
     * 席位详情
     * @param userid  必填
     * @param id      必填
     */
    public function myxiwei_detail()
    {
        $userid = intval($_POST['userid']);
        $id = intval($_POST['id']);
        if($userid && $id) {
            $infos = array();
            $where = "userid = $userid and id = $id";
            $infos = $this->db_chaopai_registre->get_one($where);
        }else{
            showapierror('参数错误！');
        }
        showapisuccess($infos);
    }


    /**
     * 我的消息列表
     * @param userid  必填
     * @param page     选填
     */
    public function my_message()
    {
        $page = $_POST['page'] ? $_POST['page'] :1;
        $userid = intval($_POST['userid']);
        $member = $this->db_member->get_one(array('userid'=>$userid));
        if($member) {
            $infos = array();
            $where = "send_to_id={$member['username']}";
            $infos = $this->db_message->listinfo($where,'messageid desc',$page,10);
        }else{
            showapierror('参数错误！');
        }
        showapisuccess($infos);
    }

    /**
     * 查看消息
     * @param messageid    必填
     */
    public function read_message()
    {
        if(empty($_POST['messageid'])){
            showapierror('参数错误！');
        }
        ;
        $messageid = intval($_POST['messageid']);
        //判断是否属于当前用户
        $result = $this->db_message->get_one(array("messageid"=>$messageid));
        if(!$result){//不是当前用户的消息，不能查看
            showapierror('参数错误！');
        }

        //查看过修改状态 为 0
        $this->db_message->update(array('status'=>'0'),array('messageid'=>$messageid));
        //查询消息详情
        $infos = $this->db_message->get_one(array('messageid'=>$messageid));
        if($infos['send_from_id']!='SYSTEM') $infos = new_html_special_chars($infos);
        //过滤一下
        $info['send_from_id'] = safe_replace($infos['send_from_id']);
        $info['send_to_id'] = safe_replace($infos['send_to_id']);
        //查询回复消息
      // $where = array('replyid'=>$infos['messageid']);
     //  $reply_infos = $this->db_message->listinfo($where,$order = 'messageid ASC',$page, $pages = '10');
     //   $show_validator = $show_scroll = $show_header = true;
        showapisuccess($infos);
    }


    /**
     * 批量查看消息
     * @param messageids   必填
     */
    public function batch_read_message()
    {
        if(empty($_POST['messageids'])){
            showapierror('参数错误！');
        }


        $messageids = explode(',',$_POST['messageids']);
        if(is_array($messageids)){
            foreach($messageids as $key=>$messageid){
                //判断是否属于当前用户
                $result = $this->db_message->get_one(array("messageid"=>$messageid));
                if(!$result){//不是当前用户的消息，不能查看
                    showapierror('参数错误！');
                }
                //查看过修改状态 为 0
                $this->db_message->update(array('status'=>'0'),array('messageid'=>$messageid));
            }
        }else{
            showapierror('参数错误！');
        }

        showapisuccess('查看成功');
    }


    /**
     * 添加粉丝
     * @param $userid    必填
     * @param $pid       必填
     * @param $siteid    必填
     * @param $source    选填
     */
    public function add_fans()
    {
        if(empty($_POST['userid']) || empty($_POST['pid'])){
            showapierror('参数不全');
        }
        $userid = intval($_POST['userid']);
        $pid = intval($_POST['pid']);

        $siteid = $_POST['siteid'];
        $site = $this->db_site->get_one("siteid=$siteid");
        $member = $this->db_member->get_one(array('userid'=>$userid));
        $p_member = $this->db_member->get_one(array('userid'=>$pid));

        $data = array();
        $data['siteid'] = $_POST['siteid'];
        $data['sitename'] = $site['name'];
        $data['userid'] = $member['userid'];
        $data['nickname'] = $member['nickname'];
        $data['pid'] = $p_member['userid'];
        $data['pid_nickname'] = $p_member['nickname'];
        $data['source'] = $_POST['source'];
        $data['addtime'] = SYS_TIME;
        $data['addtimes'] = SYS_TIME_FORMAT;
        if($this->db_fans->insert($data)){
            $heijingbi = $p_member['heijingbi'] + 20;
            $this->db_member->update(array('heijingbi'=>$heijingbi),array('userid'=>$p_member['userid']));

            //增加黑金币流水
            $heijing = array();
            $heijing['userid'] = $p_member['userid'];
            $heijing['nickname'] = $p_member['nickname'];
            $heijing['num'] = 20;
            $heijing['num_format'] = '+20';
            $heijing['status'] = 'add';
            $heijing['type'] = '新增粉丝';
            $heijing['addtime'] = SYS_TIME;
            $heijing['addtimes'] = SYS_TIME_FORMAT;
            $this->db_heijingbi->insert($heijing);

            //系统发送拓展粉丝消息
            $username= "系统";
            $tousername = $p_member['username'];
            $subject = "拓展新粉丝";
            $content = "太棒了！您拓展了一位新粉丝，获得20黑精币，祝您生活愉快。";
            if($this->db_message->add_message($tousername,$username,$subject,$content,true)){
                showapisuccess('粉丝添加成功');
            }else{
                showapierror('粉丝添加失败');
            }
        }else{
            showapierror('粉丝添加失败');
        }
    }


    /**
     * 我的粉丝列表
     * @param $userid    必填
     */
    public function myfans()
    {
        if(empty($_POST['userid'])){
            showapierror('参数不全');
        }
        $pid = intval($_POST['userid']);
      //  $siteid = $_POST['siteid'];
        $page = $_POST['page'] ? $_POST['page'] :1;

        if($pid) {
            $infos = array();
            $where = "pid=$pid";
            $infos = $this->db_fans->listinfo($where,'id desc',$page,10);
        }else{
            showapierror('参数错误！');
        }
        showapisuccess($infos);
    }


    /**
     * 添加销售记录
     * @param userid    必填
     * @param card_no   选填
     * @param price    选填
     */
    public function add_sales(){
        if(empty($_POST['userid']) ){
            showapierror('参数不全');
        }
        $userid = intval($_POST['userid']);

        $member = $this->db_member->get_one(array('userid'=>$userid));
        $fans = $this->db_fans->get_one(array('userid'=>$userid),'pid');

        $data = array();
        $data['userid'] = $member['userid'];
        $data['nickname'] = $member['nickname'];
        $data['pid'] = $fans['pid'] ? $fans['pid'] : 0;
        $data['pid_nickname'] = $fans['pid_nickname'] ? $fans['pid_nickname'] : "";
        $data['card_no'] = $_POST['card_no'];
        $data['price'] = $_POST['price'];
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
                showapisuccess('销售记录添加成功');
            }else{
                showapierror('销售记录添加失败');
            }
        }else{
            showapierror('销售记录添加失败');
        }

    }


    /**
     * 我的销售记录
     * @param userid    必填
     * @param page    选填
     */
    public function mysales(){
        if(empty($_POST['userid'])){
            showapierror('参数不全');
        }
        $userid = intval($_POST['userid']);
        $page = $_POST['page'] ? $_POST['page'] :1;

        if($userid) {
            $infos = array();
            $where = "userid=$userid";
            $infos = $this->db_sales->listinfo($where,'id desc',$page,10);
        }else{
            showapierror('参数错误！');
        }
        showapisuccess($infos);
    }


    /**
     * 添加提现申请
     * @param userid    必填
     * @param money    必填
     * @param phone    必填
     */
    public function add_withdrawals(){
        if(empty($_POST['userid']) || empty($_POST['money']) || empty($_POST['phone'])|| empty($_POST['ali_account'])|| empty($_POST['ali_username'])){
            showapierror('参数不全');
        }
        $userid = intval($_POST['userid']);
        $member = $this->db_member->get_one(array('userid'=>$userid));
        if(!$member){
            showapierror('申请提现用户不存在');
        }

        if(strlen($_POST['ali_account'])<6){
            showapierror('请填写真实支付宝账号');
        }
        if(strlen($_POST['ali_username'])<2){ //真实姓名
            showapierror('请填写真实姓名');
        }

        if($_POST['money'] < 100){
            showapierror('提现金额低于100');
        }
        if(!preg_match("/^1[34578]\d{9}$/", $_POST['phone'])){
            showapierror('手机号错误');
        }

        $data = array();
        $data['userid'] = $member['userid'];
        $data['nickname'] = $member['nickname'];
        $data['ali_account'] = $_POST['ali_account'];
        $data['ali_username'] = $_POST['ali_username'];
        $data['phone'] = $_POST['phone'];
        $data['money'] = $_POST['money'];
        $data['ip'] = ip();
        $data['addtime'] = SYS_TIME;
        $data['addtimes'] = SYS_TIME_FORMAT;
        if($this->db_withdrawals->insert($data)){
            showapisuccess('提现申请成功');
        }else{
            showapierror('提现申请失败');
        }

    }
}