<?php
/**
 * Created by PhpStorm.
 * User: xkq
 * Date: 2017/6/11 0011
 * Time: 16:01
 * 精彩api
 */
defined('IN_PHPCMS') or exit('No permission resources.');

class Marvellous{
    function __construct(){
        $this->db_data = pc_base::load_model('position_data_model');
        $this->db_category = pc_base::load_model('category_model');
        $this->db_content = pc_base::load_model('content_model');
        $this->db_poster = pc_base::load_model('poster_model');
    }



    /**
     * 精彩首页推荐广告
     * @param siteid  必填
     */
    public function init(){
        $siteid = intval($_POST['siteid']);
        $posid =getMarvellousPosid(intval($_POST['siteid']));
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
     * 精彩首页广告版位列表
     * @param siteid  必填
     * @param page  选填
     */
    public function spaceList(){
        $siteid = intval($_POST['siteid']);
        $spaceid = getApiSiteid(intval($_POST['siteid']),1,2);
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


    function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }
    /**
     * 将参数变为字符串
     * @param $array_query
     * @return string string 'm=content&c=index&a=lists&catid=6&area=0&author=0&h=0®ion=0&s=1&page=1' (length=73)
     */
    function getUrlQuery($array_query)
    {
        $tmp = array();
        foreach($array_query as $k=>$param)
        {
            $tmp[] = $k.'='.$param;
        }
        $params = implode('&',$tmp);
        return $params;
    }


    /**
     * 精彩子栏目列表
     * @param siteid  必填
     */
    public function columnList(){
        $siteid = intval($_POST['siteid']);
        //$catid = getMarvellousCatid(intval($_POST['siteid']));
        $catid = getApiSiteid(intval($_POST['siteid']),1,1);
        if($catid && $siteid ){
            $CATEGORY = getcache('category_content_'.$siteid,'commons');
            $page = $_POST['page'] ? $_POST['page'] : '1';
            $cate_list = $this->db_category->get_one(array('catid'=>$catid,'siteid'=>$siteid));
            $cate_list['arrchildid'] = explode(',',$cate_list['arrchildid']);
            $infos = array();
            if(count($cate_list['arrchildid'])>1){
                foreach($cate_list['arrchildid'] as $childid){
                    $temp = $this->db_category->get_one(array('catid'=>$childid,'siteid'=>$siteid,'parentid'=>$cate_list['catid'],),'catid,catname');
                    if($temp){
                        $infos[] = $temp;
                    }
                }
            }
        }else{
            showapierror('catid参数错误！');
        }

        showapisuccess($infos);
    }

    /**
     * 精彩子栏目文章列表
     * @param siteid  必填
     * @param catid   必填
     * @param page    选填
     */
    public function articleList(){
        $page = $_POST['page'] ? $_POST['page'] : 1;
        $catid = intval($_POST['catid']);
        $siteid = intval($_POST['siteid']);
        $categorys = getcache('category_content_'.$siteid,'commons');
        if($catid && $categorys[$_POST['catid']]['siteid']==$siteid) {
            $catid = intval($_POST['catid']);
            $category = $categorys[$catid];
            $modelid = $category['modelid'];
            $this->db_content->set_model($modelid);
            $infos = array();
            $where = 'catid='.$catid.' AND status=99';
            $infos = $this->db_content->listinfo($where,'id desc',$page);
        }else{
            showapierror('参数错误！');
        }
        showapisuccess($infos);
    }


    /**
     * 精彩子栏目文章详情与相关文章
     * @param siteid  必填
     * @param catid   必填
     * @param id      必填
     * @param userid   选填
     */
    public function article_details()
    {
        $catid = intval($_POST['catid']);
        $siteid = intval($_POST['siteid']);
        $id = intval($_POST['id']);
        $categorys = getcache('category_content_'.$siteid,'commons');
        if($catid && $id && $categorys[$_POST['catid']]['siteid']==$siteid) {
            $catid = intval($_POST['catid']);
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
            $infos['author'] = $infos['author'] ? $infos['author'] : '精英点评';
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


}