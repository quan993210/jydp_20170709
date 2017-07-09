<?php
/**
 * Created by PhpStorm.
 * User: xkq
 * Date: 2017/6/25 0025
 * Time: 23:11
 * 转发
 */
defined('IN_PHPCMS') or exit('No permission resources.');
class Forward {
    //数据库连接
    private $forward_db, $forward_data_db, $forward_table_db;

    public $msg_code = 0;

    public function __construct() {
        $this->forward_db = pc_base::load_model('forward_model');
        $this->forward_data_db = pc_base::load_model('forward_data_model');
        $this->forward_table_db = pc_base::load_model('forward_table_model');
        $this->db_member = pc_base::load_model('member_model');
    }


    /**
     * 添加转发
     * @param integer $catid 分类ID
     * @param integer $contentid 文章ID
     * @param integer $siteid 站点ID
     * @param integer $userid 用户ID
     * @param string $title 文章标题   选填
     * @param string $url 文章URL地址  选填
     */
    public function addforward() {
        $catid = $_POST['catid'];
        $contentid = $_POST['contentid'];
        $siteid = $_POST['siteid'];
        if(empty($catid) || empty($contentid) || empty($siteid)){
            showapierror('参数错误，请联系管理员。');
        }
      
        $data['userid'] = $_POST['userid'];
        $member = $this->db_member->get_one(array('userid'=>$data['userid']));
        if(!$member){
            showapierror('用户id错误。');
        }
        $data['username'] =  $member['nickname'];
        $title = $_POST['title'];
        $url = $_POST['url'];
        $forwardid = id_encode("forward_$catid",$contentid,$siteid);
        //开始查询转发这条转发是否存在。
        $title = new_addslashes($title);
        if (!$forward = $this->forward_db->get_one(array('forwardid'=>$forwardid, 'siteid'=>$siteid), 'tableid, forwardid')) { //转发不存在
            //取得当前可以使用的内容数据表
            $r = $this->forward_table_db->get_one('', 'tableid, total', 'tableid desc');
            $tableid = $r['tableid'];
            if ($r['total'] >= 1000000) {
                //当上一张数据表存的数据已经达到1000000时，创建新的数据存储表，存储数据。
                if (!$tableid = $this->forward_table_db->creat_table()) {
                    showapierror('自动创建数据存储表时出错，请联系管理员。');
                }
            }
            //新建转发到转发总表中。
            $forward_data = array('forwardid'=>$forwardid, 'siteid'=>$siteid, 'tableid'=>$tableid);
            if (!empty($title)) $forward_data['title'] = $title;
            if (!empty($url)) $forward_data['url'] = $url;
            if (!$this->forward_db->insert($forward_data)) {
                showapierror('尝试添加转发数据时出错，请联系管理员。');
            }
        } else {//转发存在时
            $tableid = $forward['tableid'];
        }
        if (empty($tableid)) {
            showapierror('没有获取到正常的数据存储表。');
        }
        //为数据存储数据模型设置 数据表名。
        $this->forward_data_db->table_name($tableid);
        //检查数据存储表。
        if (!$this->forward_data_db->table_exists('forward_data_'.$tableid)) {
            //当存储数据表不存时，尝试创建数据表。
            if (!$tableid = $this->forward_table_db->creat_table($tableid)) {
                showapierror('数据存储表不存在，并在尝试创建数据存储表时出现错误，请联系管理员。');
            }
        }
        //向数据存储表中写入数据。
        $data['forwardid'] = $forwardid;
        $data['siteid'] = $siteid;
        $data['ip'] = ip();
        $data['creat_at'] = SYS_TIME;
        if ($forward_data_id = $this->forward_data_db->insert($data, true)) {
           
            //开始更新数据存储表数据总条数
            $this->forward_table_db->edit_total($tableid, '+=1');
            //开始更新转发总表数据总数
            $sql['lastupdate'] = SYS_TIME;
            $sql['total'] = '+=1';
            $this->forward_db->update($sql, array('forwardid'=>$forwardid));
            showapisuccess('转发发表成功。');
        } else {
            showapierror('写入数据存储表时出错，请联系管理员。');
        }
    }


    /**
     * 获取转发列表
     * @param integer $catid 分类ID
     * @param integer $contentid 文章ID
     * @param integer $siteid 站点ID
     * @param integer $page 选填
     */
    public function getForwardList()
    {
        $catid = $_POST['catid'];
        $contentid = $_POST['contentid'];
        $siteid = $_POST['siteid'];
        if(empty($catid) || empty($contentid) || empty($siteid)){
            showapierror('参数错误，请联系管理员。');
        }
        $forwardid = id_encode("forward_$catid",$contentid,$siteid);
        $pagesize = 20;
        $page = intval($_POST['page']) ? intval($_POST['page']) : 1;
        if($page<=0){
            $page=1;
        }
        $offset = ($page - 1) * $pagesize;
        $data = array(
            'forwardid'=>$forwardid,
            'siteid'=>$siteid,
            'limit'=>$offset.",".$pagesize,
            'action'=>'lists',
            );

        $forwardid = $data['forwardid'];
        if (empty($forwardid)) showapierror('参数错误，请联系管理员。');
        $siteid = $data['siteid'];
        if (empty($siteid)) {
            list($module,$contentid, $siteid) = $this->decode_forwardid($forwardid);
        }
        $forward = $this->forward_db->get_one(array('forwardid'=>$forwardid, 'siteid'=>$siteid));
        if (!$forward) showapierror('参数错误，请联系管理员。');
        //设置存储数据表
        $this->forward_data_db->table_name($forward['tableid']);

        $sql = array('forwardid'=>$forwardid);

        $infos = $this->forward_data_db->select($sql, '*', $data['limit'], 'id'.' desc ');
        showapisuccess($infos);

    }

    /**
     * 解析转发ID
     * @param $forwardid 转发ID
     */
    private function decode_forwardid($forwardid) {
        return explode('-', $forwardid);
    }


}