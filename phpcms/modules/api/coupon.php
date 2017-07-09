<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/23
 * Time: 18:12
 */

defined('IN_PHPCMS') or exit('No permission resources.');
class Coupon{
    private $db;
    function __construct(){
//        $this->db = pc_base::load_model('site_model');
//        register_shutdown_function(function(){ var_dump(error_get_last()); });
    }

    /**
     * 获取优惠券类型（此接口作为以后扩展用，暂时可不用）
     * @param siteid 站点（城市）id，通过get_city接口获得，默认值1
     * @return list 类型列表数组
     */
    public function get_coupon_type(){
        $array = array(
            array(
                'siteid' => '1',
                'typeid' => '1',
                'name' => '5折'
            ),
            array(
                'siteid' => '1',
                'typeid' => '2',
                'name' => '免费'
            )
        );
//        $json = json_encode($array);
//        echo $json;
        showapisuccess($array);
    }

    /**
     * 获取优惠券列表
     * @param $siteid 站点id，默认值1
     * @param typeid  类型id，1——5折券，2——免费券，不传或者为0，则获取全部的优惠券
     * @param page 可选
     * @return list 优惠券列表数组
     */
    public function get_coupon_list(){
        $siteid = isset($_POST['siteid']) ? trim($_POST['siteid']) : showapierror('siteid_error');
        $typeid = $page = isset($_POST['typeid']) && intval($_POST['typeid']) ? intval($_POST['typeid']) : 0;
        $page = isset($_POST['page']) && intval($_POST['page']) ? intval($_POST['page']) : 1;

        $array1 = array(
            array(
                'couponid' => '1',
                'siteid' => '1',
                'typeid' => '1',
                'shopid' => '1',
                'title' => '星巴克精品咖啡',
                'subtitle'=> '饮品半价',
                'thumb' => 'http://101.37.168.200/uploadfile/images/coupon.png',
                'amount' => '100',
                'sold' => '20',
                'available_time' => '3',
                'available_time_format' => '今日可用',
                'add_time' => '1495614876',
                'add_time_format' => '2017-05-24 16:34:36',
                'content' => '优惠券内容',
                'price' => '100',
                'status' => '0',
                'memo' => ''
            ),
            array(
                'couponid' => '2',
                'siteid' => '1',
                'typeid' => '1',
                'shopid' => '2',
                'title' => '星巴克精品咖啡',
                'subtitle'=> '饮品半价',
                'thumb' => 'http://101.37.168.200/uploadfile/images/coupon.png',
                'amount' => '100',
                'sold' => '20',
                'available_time' => '4',
                'available_time_format' => '明日可用',
                'add_time' => '1495614876',
                'add_time_format' => '2017-05-24 16:34:36',
                'content' => '优惠券内容',
                'price' => '100',
                'status' => '0',
                'memo' => ''
            )
        );

        $array2 = array(
            array(
                'couponid' => '3',
                'siteid' => '1',
                'typeid' => '2',
                'shopid' => '1',
                'title' => '星巴克精品咖啡',
                'subtitle'=> '饮品半价',
                'thumb' => 'http://101.37.168.200/uploadfile/images/coupon.png',
                'amount' => '100',
                'sold' => '20',
                'available_time' => '3',
                'available_time_format' => '今日可用',
                'add_time' => '1495614876',
                'add_time_format' => '2017-05-24 16:34:36',
                'content' => '优惠券内容',
                'price' => '100',
                'status' => '0',
                'memo' => ''
            ),
            array(
                'couponid' => '4',
                'siteid' => '1',
                'typeid' => '2',
                'shopid' => '2',
                'title' => '星巴克精品咖啡',
                'subtitle'=> '饮品半价',
                'thumb' => 'http://101.37.168.200/uploadfile/images/coupon.png',
                'amount' => '100',
                'sold' => '20',
                'available_time' => '4',
                'available_time_format' => '明日可用',
                'add_time' => '1495614876',
                'add_time_format' => '2017-05-24 16:34:36',
                'content' => '优惠券内容',
                'price' => '100',
                'status' => '0',
                'memo' => ''
            )
        );
        if ($typeid == 1){
            $array = $array1;
        }elseif ($typeid == 2){
            $array = $array2;
        }else{
            $array = array_merge($array1,$array2);
        }
//        $json = json_encode($array);
//        echo $json;
        showapisuccess($array);
    }

    /**
     * 使用优惠券
     * @param userid 用户id
     * @param couponid 优惠券id
     * @param shopid  店铺id 默认1
     * @return bool
     */
    public function use_coupon(){
        $userid = isset($_POST['userid']) ? trim($_POST['userid']) : showapierror('userid_error');
        $couponid = isset($_POST['couponid']) ? trim($_POST['couponid']) : showapierror('couponid_error');
        showapisuccess();

    }

    /**
     * 领取优惠券
     * @param userid 用户id
     * @param couponid 优惠券id
     * @return couponmode 优惠券模型
     */
    public function receive_coupon(){
        $userid = isset($_POST['userid']) ? trim($_POST['userid']) : showapierror('userid_error');
        $couponid = isset($_POST['couponid']) ? trim($_POST['couponid']) : showapierror('couponid_error');

        $array = array(
            'couponid' => $couponid,
            'siteid' => '1',
            'typeid' => '1',
            'shopid' => '1',
            'title' => '星巴克精品咖啡',
            'subtitle'=> '饮品半价',
            'thumb' => 'http://101.37.168.200/uploadfile/images/coupon.png',
            'amount' => '100',
            'sold' => '20',
            'available_time' => '3',
            'available_time_format' => '今日可用',
            'add_time' => '1495614876',
            'add_time_format' => '2017-05-24 16:34:36',
            'content' => '优惠券内容',
            'price' => '100',
            'status' => '0',
            'memo' => ''
        );
        showapisuccess($array);
    }

}