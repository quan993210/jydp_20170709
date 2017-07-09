<?php
/**
 * Created by PhpStorm.
 * User: xkq
 * Date: 2017/6/30 0030
 * Time: 21:46
 * 提现
 */
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_class('admin','admin',0);
class withdrawals extends admin {
    function __construct() {
        $admin_username = param::get_cookie('admin_username');
        $userid = $_SESSION['userid'];
        $this->db = pc_base::load_model('withdrawals_model');
        parent::__construct();
    }

    function init () {
        $page = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
        $where = "1=1";
        if($_GET['keyword']){
            $keyword = $_GET['keyword'];
            $where.=" and (nickname like '%$keyword%' or ali_account like '%$keyword%' or ali_username like '%$keyword%')";
        }
        $infos = $pages = '';
        $infos = $this->db->listinfo($where,$order = 'id DESC',$page, $pages = '30');
        $pages = $this->db->pages;
        include $this->admin_tpl('withdrawals');
    }
}
?>