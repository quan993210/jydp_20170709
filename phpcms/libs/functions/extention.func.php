<?php
/**
 *  extention.func.php 用户自定义函数库
 *
 * @copyright			(C) 2005-2010 PHPCMS
 * @license				http://www.phpcms.cn/license/
 * @lastmodify			2010-10-27
 */

//取精彩推荐位id
function getMarvellousPosid($siteid){
    $posit = array(
        '1'=>18,
    );
    return $posit[$siteid];
}

//取精彩栏目id
function getMarvellousCatid($siteid){
    $cat = array(
        '1'=>9,
    );
    return $cat[$siteid];
}

//取潮派推荐位id
function getChaopaiPosid($siteid){
    $posit = array(
        '1'=>20,
    );
    return $posit[$siteid];
}


//取潮派栏目id
function getChaopaiCatid($siteid){
    $cat = array(
        '1'=>11,
    );
    return $cat[$siteid];
}


/**
 * 获取点击数量
 * @param $modelid
 * @param $id
 */
function hits_count($modelid,$id){
    $hitsid = 'c-'.$modelid.'-'.$id;
    $r = get_hits_count($hitsid);
    if(!$r) exit;
    extract($r);
    update_hits($hitsid);
    return $r['views'];

}

/**
 * 获取相关点击数量
 * @param $modelid
 * @param $id
 */
function relation_hits_count($modelid,$id){
    $hitsid = 'c-'.$modelid.'-'.$id;
    $r = get_hits_count($hitsid);
    if(!$r) exit;
    extract($r);
   // update_hits($hitsid);
    return $r['views'];

}


/**
 * 获取点击数量
 * @param $hitsid
 */
function get_hits_count($hitsid) {
    $hits_db = pc_base::load_model('hits_model');
    $r = $hits_db->get_one(array('hitsid'=>$hitsid));
    if(!$r) return false;
    return $r;
}

/**
 * 点击次数统计
 * @param $hitsid
 */
function update_hits($hitsid) {
    $hits_db = pc_base::load_model('hits_model');
    $r = $hits_db->get_one(array('hitsid'=>$hitsid));
    if(!$r) return false;
    $views = $r['views'] + 1;
    $yesterdayviews = (date('Ymd', $r['updatetime']) == date('Ymd', strtotime('-1 day'))) ? $r['dayviews'] : $r['yesterdayviews'];
    $dayviews = (date('Ymd', $r['updatetime']) == date('Ymd', SYS_TIME)) ? ($r['dayviews'] + 1) : 1;
    $weekviews = (date('YW', $r['updatetime']) == date('YW', SYS_TIME)) ? ($r['weekviews'] + 1) : 1;
    $monthviews = (date('Ym', $r['updatetime']) == date('Ym', SYS_TIME)) ? ($r['monthviews'] + 1) : 1;
    $sql = array('views'=>$views,'yesterdayviews'=>$yesterdayviews,'dayviews'=>$dayviews,'weekviews'=>$weekviews,'monthviews'=>$monthviews,'updatetime'=>SYS_TIME);
    return $hits_db->update($sql, array('hitsid'=>$hitsid));
}

/**
 * 获取评论数量
 * @param $siteid
 * @param $catid
 * @param $id
 */
function comment_count($catid,$id,$siteid){
    $comment_db = pc_base::load_model('comment_model');
    $commentid = id_encode("content_$catid",$id,$siteid);
    $comment = $comment_db->get_one(array('commentid'=>$commentid, 'siteid'=>$siteid));
    if(!$comment['total']){
        $comment['total'] = "0";
    }
    return $comment['total'];

}

/**
 * 获取点赞数量
 * @param $siteid
 * @param $catid
 * @param $id
 */
function like_count($catid,$id,$siteid){
    $like_db = pc_base::load_model('like_model');
    $likeid = id_encode("like_$catid",$id,$siteid);
    $like = $like_db->get_one(array('likeid'=>$likeid, 'siteid'=>$siteid));
    if(!$like['total']){
        $like['total'] = "0";
    }
    return $like['total'];
}


/**
 * 获取点赞数量
 * @param $siteid
 * @param $catid
 * @param $id
 * @param $userid
 */
function is_like($catid,$id,$siteid,$userid){
    $like_db = pc_base::load_model('like_model');
    $like_data_db = pc_base::load_model('like_data_model');
    $like_table_db = pc_base::load_model('like_table_model');

    $likeid = id_encode("like_$catid",$id,$siteid);
    $r = $like_table_db->get_one('', 'tableid, total', 'tableid desc');

    $like_data_db->table_name($r['tableid']);

    $like = $like_data_db->get_one(array('likeid'=>$likeid, 'siteid'=>$siteid,'userid'=>$userid));
    if($like){
        return "1";
    }else{
        return "0";
    }
}

/**
 * 获取转发数量
 * @param $siteid
 * @param $catid
 * @param $id
 */
function forward_count($catid,$id,$siteid){
    $forward_db = pc_base::load_model('forward_model');
    $forwardid = id_encode("forward_$catid",$id,$siteid);
    $forward = $forward_db->get_one(array('forwardid'=>$forwardid, 'siteid'=>$siteid));
    if(!$forward['total']){
        $forward['total'] = "0";
    }
    return $forward['total'];
}


/**
 * 获取配置id
 * @param $siteid
 * @param $type  1精彩2精选3潮派
 * @param $model 栏目2广告
 */
function getApiSiteid($siteid,$type,$model){
    $api_site_db = pc_base::load_model('api_site_model');
    $api_site = $api_site_db->get_one(array('siteid'=>$siteid,'type'=>$type,'model'=>$model));
    if($api_site){
        return $api_site['api_site_id'];
    }else{
        return false;
    }
}

/**
 * 添加配置id
 * @param $data
 */
function insertApiSiteid($data){
    if(empty($data['siteid']) || empty($data['type']) || empty($data['model']) || empty($data['api_site_id'])){
        return false;
    }
    $api_site_db = pc_base::load_model('api_site_model');
    $id = $api_site_db->insert($data,1);
    if($id){
        return $id;
    }else{
        return false;
    }
}


function get_sitemodel(){
    $model = array(
        array('tablename'=>'news', 'id'=>1,'modelname'=>'文章模型'),
        array('tablename'=>'download', 'id'=>2,'modelname'=>'下载模型'),
        array('tablename'=>'picture', 'id'=>3,'modelname'=>'图片模型'),
        array('tablename'=>'video', 'id'=>4,'modelname'=>'视频模型'),
        array('tablename'=>'jingxuan', 'id'=>12,'modelname'=>'精选模型'),
        array('tablename'=>'chaopai', 'id'=>13,'modelname'=>'潮派模型'),
        array('tablename'=>'haowu', 'id'=>14,'modelname'=>'好物模型'),
        array('tablename'=>'zhibo', 'id'=>15,'modelname'=>'直播模型'),
    );
    return $model;
}

function get_setting(){
    $setting = array(
        'workflowid' => "",
        'ishtml' => 0,
        'content_ishtml' => 0,
        'create_to_html_root' => 0,
        'template_list' => "default",
        'category_template' => "category",
        'list_template' => "list",
        'show_template' => "show",
        'meta_title]' =>"",
        'meta_keywords' =>"",
        'meta_description' =>"",
        'presentpoint' => 1,
        'defaultchargepoint' => 0,
        'paytype' => 0,
        'repeatchargedays' => 1,
        'category_ruleid'=>30,
        'show_ruleid'=>16,

    );
    return $setting;
}






?>