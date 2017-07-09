<?php
/**
 * Created by PhpStorm.
 * User: xkq
 * Date: 2017/6/25 0025
 * Time: 23:11
 * 点赞
 */
defined('IN_PHPCMS') or exit('No permission resources.');
class Like {
    //数据库连接
    private $like_db, $like_data_db, $like_table_db;

    public $msg_code = 0;

    public function __construct() {
        $this->like_db = pc_base::load_model('like_model');
        $this->like_data_db = pc_base::load_model('like_data_model');
        $this->like_table_db = pc_base::load_model('like_table_model');
        $this->db_member = pc_base::load_model('member_model');
    }


    /**
     * 添加点赞
     * @param integer $catid 分类ID
     * @param integer $contentid 文章ID
     * @param integer $siteid 站点ID
     * @param integer $userid 用户ID
     * @param string $title 文章标题   选填
     * @param string $url 文章URL地址  选填
     */
    public function addlike() {
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
        $likeid = id_encode("like_$catid",$contentid,$siteid);
        //开始查询点赞这条点赞是否存在。
        $title = new_addslashes($title);
        if (!$like = $this->like_db->get_one(array('likeid'=>$likeid, 'siteid'=>$siteid), 'tableid, likeid')) { //点赞不存在
            //取得当前可以使用的内容数据表
            $r = $this->like_table_db->get_one('', 'tableid, total', 'tableid desc');
            $tableid = $r['tableid'];
            if ($r['total'] >= 1000000) {
                //当上一张数据表存的数据已经达到1000000时，创建新的数据存储表，存储数据。
                if (!$tableid = $this->like_table_db->creat_table()) {
                    showapierror('自动创建数据存储表时出错，请联系管理员。');
                }
            }
            //新建点赞到点赞总表中。
            $like_data = array('likeid'=>$likeid, 'siteid'=>$siteid, 'tableid'=>$tableid);
            if (!empty($title)) $like_data['title'] = $title;
            if (!empty($url)) $like_data['url'] = $url;
            if (!$this->like_db->insert($like_data)) {
                showapierror('尝试添加点赞数据时出错，请联系管理员。');
            }
        } else {//点赞存在时
            $tableid = $like['tableid'];
        }
        if (empty($tableid)) {
            showapierror('没有获取到正常的数据存储表。');
        }
        //为数据存储数据模型设置 数据表名。
        $this->like_data_db->table_name($tableid);
        //检查数据存储表。
        if (!$this->like_data_db->table_exists('like_data_'.$tableid)) {
            //当存储数据表不存时，尝试创建数据表。
            if (!$tableid = $this->like_table_db->creat_table($tableid)) {
                showapierror('数据存储表不存在，并在尝试创建数据存储表时出现错误，请联系管理员。');
            }
        }

        $like = $this->like_data_db->get_one(array('likeid'=>$likeid, 'siteid'=>$siteid,'userid'=>$member['userid']));
        if($like){
            showapierror('已经点过赞，请勿重复点击');
        }
        //向数据存储表中写入数据。
        $data['likeid'] = $likeid;
        $data['siteid'] = $siteid;
        $data['ip'] = ip();
        $data['creat_at'] = SYS_TIME;
        if ($like_data_id = $this->like_data_db->insert($data, true)) {
           
            //开始更新数据存储表数据总条数
            $this->like_table_db->edit_total($tableid, '+=1');
            //开始更新点赞总表数据总数
            $sql['lastupdate'] = SYS_TIME;
            $sql['total'] = '+=1';
            $this->like_db->update($sql, array('likeid'=>$likeid));
            showapisuccess('点赞发表成功。');
        } else {
            showapierror('写入数据存储表时出错，请联系管理员。');
        }
    }


    /**
     * 获取点赞列表
     * @param integer $catid 分类ID
     * @param integer $contentid 文章ID
     * @param integer $siteid 站点ID
     * @param integer $page 选填
     */
    public function getlikeList()
    {
        $catid = $_POST['catid'];
        $contentid = $_POST['contentid'];
        $siteid = $_POST['siteid'];
        if(empty($catid) || empty($contentid) || empty($siteid)){
            showapierror('参数错误，请联系管理员。');
        }
        $likeid = id_encode("like_$catid",$contentid,$siteid);
        $pagesize = 20;
        $page = intval($_POST['page']) ? intval($_POST['page']) : 1;
        if($page<=0){
            $page=1;
        }
        $offset = ($page - 1) * $pagesize;
        $data = array(
            'likeid'=>$likeid,
            'siteid'=>$siteid,
            'limit'=>$offset.",".$pagesize,
            'action'=>'lists',
            );

        $likeid = $data['likeid'];
        if (empty($likeid)) showapierror('参数错误，请联系管理员。');
        $siteid = $data['siteid'];
        if (empty($siteid)) {
            list($module,$contentid, $siteid) = $this->decode_likeid($likeid);
        }
        $like = $this->like_db->get_one(array('likeid'=>$likeid, 'siteid'=>$siteid));
        if (!$like) showapierror('参数错误，请联系管理员。');
        //设置存储数据表
        $this->like_data_db->table_name($like['tableid']);

        $sql = array('likeid'=>$likeid);

        $infos = $this->like_data_db->select($sql, '*', $data['limit'], 'id'.' desc ');
        showapisuccess($infos);

    }

    /**
     * 解析点赞ID
     * @param $likeid 点赞ID
     */
    private function decode_likeid($likeid) {
        return explode('-', $likeid);
    }


}