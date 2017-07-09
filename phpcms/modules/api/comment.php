<?php
/**
 * Created by PhpStorm.
 * User: xkq
 * Date: 2017/6/25 0025
 * Time: 23:11
 * 评论
 */
defined('IN_PHPCMS') or exit('No permission resources.');
class Comment {
    //数据库连接
    private $comment_db, $comment_setting_db, $comment_data_db, $comment_table_db, $comment_check_db;

    public $msg_code = 0;

    public function __construct() {
        $this->comment_db = pc_base::load_model('comment_model');
        $this->comment_setting_db = pc_base::load_model('comment_setting_model');
        $this->comment_data_db = pc_base::load_model('comment_data_model');
        $this->comment_table_db = pc_base::load_model('comment_table_model');
        $this->comment_check_db = pc_base::load_model('comment_check_model');
        $this->db_member = pc_base::load_model('member_model');
    }


/*
评论解释：

评论开始前需要在数据表comment_table 中添加数据（tableid可扩展点赞）

$commentid = id_encode($catid,$contentid,$siteid);

id 为评论comment_data_1中的id  回复需要get id

添加评论  http://jydp.com/index.php?m=comment&c=index&a=post&commentid=content_15-7-1

回复评论  http://jydp.com/index.php?m=comment&c=index&a=post&commentid=content_15-7-1&id=4

$LANG['coment_class_php_1'] = '评论发表成功。';
$LANG['coment_class_php_1'] = '没有获取到正常的数据存储表。';
$LANG['coment_class_php_2'] = '数据存储表不存在，并在尝试创建数据存储表时出现错误，请联系管理员。';
$LANG['coment_class_php_3'] = '写入数据存储表时出错，请联系管理员。';
$LANG['coment_class_php_4'] = '自动创建数据存储表时出错，请联系管理员。';
$LANG['coment_class_php_5'] = '尝试添加评论数据时出错，请联系管理员。';
$LANG['coment_class_php_6'] = '评论没有找到。';
$LANG['coment_class_php_7'] = '评论发表成功，需要等待管理员审核，才会显示。';
* */




    /**
     * 添加评论
     * @param integer $catid 分类ID
     * @param integer $contentid 文章ID
     * @param integer $siteid 站点ID
     * @param integer $userid 用户ID
     * @param string content 内容
     * @param integer direction 方向 选填（0:没有方向 ,1:正方,2:反方,3:中立)
     * @param integer $id 回复评论id  可不填(扩展回复可用)
     * @param string $title 文章标题   选填
     * @param string $url 文章URL地址  选填
     */
    public function addComment() {
        $catid = $_POST['catid'];
        $contentid = $_POST['contentid'];
        $siteid = $_POST['siteid'];
        if(empty($catid) || empty($contentid) || empty($siteid) || empty($_POST['content'])){
            showapierror('参数错误，请联系管理员。');
        }
        $data['direction'] = $_POST['direction'];
        $data['userid'] = $_POST['userid'];
        $data['content'] = $_POST['content'];
        $member = $this->db_member->get_one(array('userid'=>$data['userid']));
        if(!$member){
            showapierror('用户id错误。');
        }
        $data['username'] =  $member['nickname'];
        $title = $_POST['title'];
        $url = $_POST['url'];
        $commentid = id_encode("content_$catid",$contentid,$siteid);
        //开始查询评论这条评论是否存在。
        $title = new_addslashes($title);
        if (!$comment = $this->comment_db->get_one(array('commentid'=>$commentid, 'siteid'=>$siteid), 'tableid, commentid')) { //评论不存在
            //取得当前可以使用的内容数据表
            $r = $this->comment_table_db->get_one('', 'tableid, total', 'tableid desc');
            $tableid = $r['tableid'];
            if ($r['total'] >= 1000000) {
                //当上一张数据表存的数据已经达到1000000时，创建新的数据存储表，存储数据。
                if (!$tableid = $this->comment_table_db->creat_table()) {
                   // $this->msg_code = 4;
                   // return false;
                    showapierror('自动创建数据存储表时出错，请联系管理员。');
                }
            }
            //新建评论到评论总表中。
            $comment_data = array('commentid'=>$commentid, 'siteid'=>$siteid, 'tableid'=>$tableid, 'display_type'=>($data['direction']>0 ? 1 : 0));
            if (!empty($title)) $comment_data['title'] = $title;
            if (!empty($url)) $comment_data['url'] = $url;
            if (!$this->comment_db->insert($comment_data)) {
               // $this->msg_code = 5;
               // return false;
                showapierror('尝试添加评论数据时出错，请联系管理员。');
            }
        } else {//评论存在时
            $tableid = $comment['tableid'];
        }
        if (empty($tableid)) {
           // $this->msg_code = 1;
           // return false;
            showapierror('没有获取到正常的数据存储表。');
        }
        //为数据存储数据模型设置 数据表名。
        $this->comment_data_db->table_name($tableid);
        //检查数据存储表。
        if (!$this->comment_data_db->table_exists('comment_data_'.$tableid)) {
            //当存储数据表不存时，尝试创建数据表。
            if (!$tableid = $this->comment_table_db->creat_table($tableid)) {
                //$this->msg_code = 2;
               // return false;
                showapierror('数据存储表不存在，并在尝试创建数据存储表时出现错误，请联系管理员。');
            }
        }
        //向数据存储表中写入数据。
        $data['commentid'] = $commentid;
        $data['siteid'] = $siteid;
        $data['ip'] = ip();
        $data['status'] = 1;
        $data['creat_at'] = SYS_TIME;
        //对评论的内容进行关键词过滤。
        $data['content'] = strip_tags($data['content']);
        $badword = pc_base::load_model('badword_model');
        $data['content'] = $badword->replace_badword($data['content']);
        /*if ($id) {
            $r = $this->comment_data_db->get_one(array('id'=>$id));
            if ($r) {
                pc_base::load_sys_class('format', '', 0);
                if ($r['reply']) {
                    $data['content'] = '<div class="content">'.str_replace('<span></span>', '<span class="blue f12">'.$r['username'].' '.L('chez').' '.format::date($r['creat_at'], 1).L('release').'</span>', $r['content']).'</div><span></span>'.$data['content'];
                } else {
                    $data['content'] = '<div class="content"><span class="blue f12">'.$r['username'].' '.L('chez').' '.format::date($r['creat_at'], 1).L('release').'</span><pre>'.$r['content'].'</pre></div><span></span>'.$data['content'];
                }
                $data['reply'] = 1;
            }
        }*/
        //判断当前站点是否需要审核
        $site = $this->comment_setting_db->site($siteid);
        if ($site['check']) {
            $data['status'] = 0;
        }
        $data['content'] = addslashes($data['content']);
        $data['appcontent'] = $data['content']; //评论添加冗余评论内容
        if ($comment_data_id = $this->comment_data_db->insert($data, true)) {
            //需要审核，插入到审核表
            if ($data['status']==0) {
                $this->comment_check_db->insert(array('comment_data_id'=>$comment_data_id, 'siteid'=>$siteid,'tableid'=>$tableid));
            } elseif (!empty($data['userid']) && !empty($site['add_point']) && module_exists('pay')) { //不需要审核直接给用户添加积分
                pc_base::load_app_class('receipts', 'pay', 0);
                receipts::point($site['add_point'], $data['userid'], $data['username'], '', 'selfincome', 'Comment');
            }
            //开始更新数据存储表数据总条数
            $this->comment_table_db->edit_total($tableid, '+=1');
            //开始更新评论总表数据总数
            $sql['lastupdate'] = SYS_TIME;
            //只有在评论通过的时候才更新评论主表的评论数
            if ($data['status'] == 1) {
                $sql['total'] = '+=1';
                switch ($data['direction']) {
                    case 1: //正方
                        $sql['square'] = '+=1';
                        break;
                    case 2://反方
                        $sql['anti'] = '+=1';
                        break;
                    case 3://中立方
                        $sql['neutral'] = '+=1';
                        break;
                }
            }
            $this->comment_db->update($sql, array('commentid'=>$commentid));
            if ($site['check']) {
               // $this->msg_code = 7;
                showapisuccess('评论发表成功，需要等待管理员审核，才会显示。');
            } else {
                //$this->msg_code = 0;
                showapisuccess('评论发表成功。');
            }
          //  $this->msg_code = 0;
           // return true;
            showapisuccess('评论发表成功。');
        } else {
          //  $this->msg_code = 3;
           // return false;
            showapierror('写入数据存储表时出错，请联系管理员。');
        }
    }


    /**
     * 获取评论列表
     * @param integer $catid 分类ID
     * @param integer $contentid 文章ID
     * @param integer $siteid 站点ID
     * @param integer $page 选填
     */
    public function getCommentList()
    {
        $catid = $_POST['catid'];
        $contentid = $_POST['contentid'];
        $siteid = $_POST['siteid'];
        if(empty($catid) || empty($contentid) || empty($siteid)){
            showapierror('参数错误，请联系管理员。');
        }
        $commentid = id_encode("content_$catid",$contentid,$siteid);
        $pagesize = 20;
        $page = intval($_POST['page']) ? intval($_POST['page']) : 1;
        if($page<=0){
            $page=1;
        }
        $offset = ($page - 1) * $pagesize;
        $data = array(
            'commentid'=>$commentid,
            'siteid'=>$siteid,
            'limit'=>$offset.",".$pagesize,
            'action'=>'lists',
            );

        $commentid = $data['commentid'];
        if (empty($commentid)) showapierror('参数错误，请联系管理员。');
        $siteid = $data['siteid'];
        if (empty($siteid)) {
            pc_base::load_app_func('global', 'comment');
            list($module,$contentid, $siteid) = decode_commentid($commentid);
        }
        $comment = $this->comment_db->get_one(array('commentid'=>$commentid, 'siteid'=>$siteid));
        if (!$comment) showapierror('参数错误，请联系管理员。');
        //设置存储数据表
        $this->comment_data_db->table_name($comment['tableid']);

        //是否按评论方向获取
        $direction = isset($data['direction']) && intval($data['direction']) ? intval($data['direction']) : 0;
        if (!in_array($direction, array(0,1,2,3))) {
            $direction = 0;
        }

        switch ($direction) {
            case 1://正方
                $sql = array('commentid'=>$commentid, 'direction'=>1, 'status'=>1);
                break;
            case 2://反方
                $sql = array('commentid'=>$commentid, 'direction'=>2, 'status'=>1);
                break;
            case 3://中立方
                $sql = array('commentid'=>$commentid, 'direction'=>3, 'status'=>1);
                break;
            default://获取所有
                $sql = array('commentid'=>$commentid, 'status'=>1);
        }
        $infos = $this->comment_data_db->select($sql, '*', $data['limit'], 'id'.' desc ');
        showapisuccess($infos);

    }

}