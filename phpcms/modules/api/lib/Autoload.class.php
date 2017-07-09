<?php
/**
 * Created by PhpStorm.
 * User: ElvisLee
 * Date: 2016/10/21
 * Time: 14:25
 */

class Autoload {

    private function __construct(){}

    /**
     * 自动加载类
     * @param $class 类名称
     */
    public static function autoload($class) {
        require_once $class.CLASS_EXT;
    }
}