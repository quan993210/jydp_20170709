<?php
/**
 * Created by PhpStorm.
 * User: xkq
 * Date: 2017/6/13 0013
 * Time: 20:03
 * 潮派api
 */
defined('IN_PHPCMS') or exit('No permission resources.');

class Chaopai{
    function __construct(){
        $this->db_data = pc_base::load_model('position_data_model');
        $this->db_category = pc_base::load_model('category_model');
        $this->db_content = pc_base::load_model('content_model');
        $this->db_message = pc_base::load_model('message_model');
        $this->db_member = pc_base::load_model('member_model');
        $this->db_chaopai_registre = pc_base::load_model('chaopai_registre_model');
        $this->db_site = pc_base::load_model('site_model');
        $this->db_poster = pc_base::load_model('poster_model');

    }


    /**
     * 潮派首页推荐广告
     * @param siteid  必填
     */
    public function init(){
        $posid =getChaopaiPosid(intval($_POST['siteid']));
        $siteid = intval($_POST['siteid']);
        if($posid && $siteid){
            $CATEGORY = getcache('category_content_'.$siteid,'commons');
            $page = $_POST['page'] ? $_POST['page'] : '1';
            $pos_arr = $this->db_data->listinfo(array('posid'=>$posid,'siteid'=>$siteid),'listorder DESC', $page, $pagesize = 20);
            $infos = array();
            if(count($pos_arr)> 0 ){
                foreach ($pos_arr as $_k => $_v) {
                    $r = string2array($_v['data']);
                    $r['catname'] = $CATEGORY[$_v['catid']]['catname'];
                    $r['modelid'] = $_v['modelid'];
                    $r['posid'] = $_v['posid'];
                    $r['id'] = $_v['id'];
                    $r['listorder'] = $_v['listorder'];
                    $r['catid'] = $_v['catid'];
                    $r['url'] = go($_v['catid'], $_v['id']);
                    $key = $r['modelid'].'-'.$r['id'];
                    $infos[] = $r;
                }
            }

        }else{
            showapierror('posid参数错误！');
        }
        showapisuccess($infos);
    }


    /**
     * 潮派首页广告版位列表
     * @param siteid  必填
     * @param page  选填
     */
    public function spaceList(){
        $siteid = intval($_POST['siteid']);
        $spaceid = getApiSiteid(intval($_POST['siteid']),3,2);
        if($spaceid && $siteid){
            if (!isset($spaceid) || empty($spaceid)) {
                showapierror('catid参数错误！');
            }
            $page = max($_GET['page'], 1);
            $infos = $this->db_poster->listinfo(array('spaceid'=>$spaceid, 'siteid'=>$siteid,'status'=>1), '`listorder` ASC, `id` DESC', $page);
            foreach($infos as $key=>$val){
                $r = string2array($val['setting']);
                $arr=parse_url($r[1]['linkurl']);
                $arr_param = $this->convertUrlQuery($arr['query']);
                $infos[$key]['linkurl'] = $r[1]['linkurl'];
                $infos[$key]['imageurl'] = $r[1]['imageurl'];
                $infos[$key]['alt'] = $r[1]['alt'];
                $infos[$key]['catid'] = $arr_param['catid'] ? $arr_param['catid'] : 0;
                $cat = $this->db_category->get_one(array('catid'=>$infos[$key]['catid']));
                $infos[$key]['catname'] = $cat['catname'];
                $infos[$key]['contentid'] = $arr_param['id'] ? $arr_param['id'] : 0;
            }

        }else{
            showapierror('posid参数错误！');
        }
        showapisuccess($infos);
    }


    /**
     * 潮牌活动列表
     * @param siteid  必填
     * @param page    选填
     */
    public function actionList(){
        $page = $_POST['page'] ? $_POST['page'] :1;
        $catid = getApiSiteid(intval($_POST['siteid']),3,1);
        $siteid = intval($_POST['siteid']);
        $categorys = getcache('category_content_'.$siteid,'commons');
        if($catid && $categorys[$catid]['siteid']==$siteid) {
            $category = $categorys[$catid];
            $modelid = $category['modelid'];
            $this->db_content->set_model($modelid);
            $infos = array();
            $where = 'catid='.$catid.' AND status=99';
            $infos = $this->db_content->listinfo($where,'id desc',$page);
            foreach($infos as $key=>$val){
                $this->db_content->table_name = $this->db_content->table_name.'_data';
                $r2 = $this->db_content->get_one(array('id'=>$val['id']));
                $info = $r2 ? array_merge($val,$r2) : $val;
                $start_time = strtotime($info['start_time']);
                $end_time = strtotime($info['end_time']);
                if($start_time > SYS_TIME){
                    $info['state'] = "活动未开始";
                }elseif($start_time < SYS_TIME && SYS_TIME < $end_time){
                    $info['state'] = "活动进行中";
                }else{
                    $info['state'] = "活动已结束";
                }
                $image = [$info['image1'],$info['image2'],$info['image3']];
                $info['image'] = $image;
                unset($info['image1'],$info['image2'],$info['image3']);

                $infos[$key] = $info;

            }
        }else{
            showapierror('参数错误！');
        }
        showapisuccess($infos);
    }


    /**
     * 潮派活动详情与相关活动
     * @param siteid  必填
     * @param id      必填
     * @param userid    选填
     */
    public function action_details()
    {
       // $catid = intval($_POST['catid']);
        $catid = getApiSiteid(intval($_POST['siteid']),3,1);
        $siteid = intval($_POST['siteid']);
        $id = intval($_POST['id']);
        $categorys = getcache('category_content_'.$siteid,'commons');
        if($catid && $id && $categorys[$catid]['siteid']==$siteid) {
            $category = $categorys[$catid];
            $modelid = $category['modelid'];
            $this->db_content->set_model($modelid);
            $r = $this->db_content->get_one("id=$id");
            $this->db_content->table_name = $this->db_content->table_name.'_data';
            $r2 = $this->db_content->get_one(array('id'=>$id));
            if(!$r2){
                showapierror('参数错误！');
            }
            $infos = $r2 ? array_merge($r,$r2) : $r;

            $start_time = strtotime($infos['start_time']);
            $end_time = strtotime($infos['end_time']);
            if($start_time > SYS_TIME){
                $infos['state'] = "活动未开始";
            }elseif($start_time < SYS_TIME && SYS_TIME < $end_time){
                $infos['state'] = "活动进行中";
            }else{
                $infos['state'] = "活动已结束";
            }
            $image = [$infos['image1'],$infos['image2'],$infos['image3']];
            $infos['image'] = $image;
            unset($infos['image1'],$infos['image2'],$infos['image3']);
            $content_tag = pc_base::load_app_class("content_tag", "content");
            if (method_exists($content_tag, 'relation')) {
                $data = $content_tag->relation(array(
                        'relation'=>$infos['relation'],
                        'id'=>$id,
                        'catid'=>$catid,
                        'limit'=>'5',
                    )
                );
                unset($infos['relation']);
               /* foreach($data as $re){
                    $infos['relation'][] = $re;
                }*/

                if($data){
                    foreach($data as $re){
                        $this->db_content->table_name = $this->db_content->table_name.'_data';
                        $re2 = $this->db_content->get_one(array('id'=>$re['id']));
                        $re = $re2 ? array_merge($re,$re2) : $re;
                        $re['hits_count'] = relation_hits_count($categorys[$re['catid']]['modelid'],$re['id']);
                        $re['commnet_count'] = comment_count($re['catid'],$re['id'],$siteid);
                        $infos['forward_count'] = forward_count($re['catid'],$re['id'],$siteid);
                        $infos['like_count'] = like_count($re['catid'],$re['id'],$siteid);
                        if($_POST['userid']){
                            $infos['is_like'] = is_like($re['catid'],$re['id'],$siteid,$_POST['userid']);
                        }else{
                            $infos['is_like'] = false;
                        }
                        $infos['relation'][] = $re;
                    }
                }else{
                    $infos['relation'][] = "";
                }

            }
        }else{
            showapierror('参数错误！');
        }
        $infos['hits_count'] = hits_count($category['modelid'],$infos['id']);
        $infos['commnet_count'] = comment_count($catid,$infos['id'],$siteid);
        $infos['forward_count'] = forward_count($catid,$infos['id'],$siteid);
        $infos['like_count'] = like_count($catid,$infos['id'],$siteid);
        if($_POST['userid']){
            $infos['is_like'] = is_like($catid,$infos['id'],$siteid,$_POST['userid']);
        }else{
            $infos['is_like'] = "0";
        }
        showapisuccess($infos);
    }


    /**
     * 活动报名
     * @param siteid  必填
     * @param id      必填
     * @param userid  必填
     * @param name    必填
     * @param phone   必填
     * @param num     必填
     */
    public function registre()
    {

        if(empty($_POST['userid']) || empty($_POST['name']) || empty($_POST['phone']) || empty($_POST['num']) || empty($_POST['siteid'])){
            showapierror('参数不全');
        }
        if(!preg_match("/^1[34578]\d{9}$/", $_POST['phone'])){
            showapierror('手机号错误');
        }

        $siteid = $_POST['siteid'];
        $site = $this->db_site->get_one("siteid=$siteid");

        $catid = getApiSiteid(intval($_POST['siteid']),3,1);
        $categorys = getcache('category_content_'.$siteid,'commons');
        $category = $categorys[$catid];

        $modelid = $category['modelid'];
        $this->db_content->set_model($modelid);
        $id = $_POST['id'];
        $r = $this->db_content->get_one("id=$id");
        $this->db_content->table_name = $this->db_content->table_name.'_data';
        $r2 = $this->db_content->get_one(array('id'=>$id));
        $infos = $r2 ? array_merge($r,$r2) : $r;

        $signup_end_time = strtotime($infos['signup_end_time']);
        if($signup_end_time < SYS_TIME){
            showapierror('报名时间已经截止');
        }

        if($_POST['num'] < 1){
            showapierror('报名数小于1');
        }
        if($_POST['num'] > $infos['limit']){
            showapierror('报名人数超过活动限制人数，最多可报名'.$infos['limit']);
        }

        $registre = $this->db_chaopai_registre->get_one(array('siteid'=>$_POST['siteid'],'chaopai_id'=>$_POST['id'],'phone'=>$_POST['phone']));
        if($registre){
            showapierror('该手机号已经报名');
        }
        $member = $this->db_member->get_one(array('userid'=>$_POST['userid']));
        if($member['vip'] != 1){
            showapierror('只有黑精会员才能获取席位');
        }

        if($member['overduedate'] < time()){
            showapierror('该黑精会员已经到期');
        }

        $data = array();
        $data['siteid'] = $_POST['siteid'];
        $data['sitename'] = $site['name'];
        $data['catid'] = $catid;
        $data['catname'] = $category['catname'];
        $data['chaopai_id'] = $_POST['id'];
        $data['chaopai_name'] = $infos['title'];
        $data['userid'] = $_POST['userid'];
        $data['nickname'] = $member['nickname'];
        $data['name'] = $_POST['name'];
        $data['phone'] = $_POST['phone'];
        $data['num'] = $_POST['num'];
        $data['addtime'] = SYS_TIME;
        $data['addtimes'] = SYS_TIME_FORMAT;
        if($this->db_chaopai_registre->insert($data)){
            $username= "系统";
            $tousername = $member['username'];
            $subject = $infos['title']."活动报名成功";
            $content = "您已报名".$infos['title']."活动，请准时参加，感谢您对黑精潮派活动的支持，祝您生活愉快";
            if($this->db_message->add_message($tousername,$username,$subject,$content,true)){
                showapisuccess('活动报名成功');
            }else{
                showapierror('活动报名失败');
            }
        }else{
            showapierror('活动报名失败');
        }
    }


}