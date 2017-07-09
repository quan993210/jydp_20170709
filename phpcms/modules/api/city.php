<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/22
 * Time: 18:16
 */
defined('IN_PHPCMS') or exit('No permission resources.');

class City{
    private $db;
    function __construct(){
        $this->db = pc_base::load_model('site_model');
//        register_shutdown_function(function(){ var_dump(error_get_last()); });
    }

    /**
     * 获取城市列表
     */
    public function get_city_list(){
        $page = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
        $pagesize = 20;
        $offset = ($page - 1) * $pagesize;
        $list = $this->db->select('', '*', $offset.','.$pagesize);
        showapisuccess($list);
    }

    /**
     * 获取城市
     * @param $cityname
     */
    public function get_city(){
        $cityname = isset($_POST['cityname']) ? trim($_POST['cityname']) : showapierror('cityname_error');
//        $cityname = '贵阳';
        $info = $this->db->get_one(array('name'=>$cityname));
//        var_dump($info);
        if ($info){
            showapisuccess($info);
        }else{
            $info = $this->db->get_one(array('siteid'=>'1'));
            showapisuccess($info);
        }

    }

}