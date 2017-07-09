<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/19
 * Time: 11:13
 */

defined('IN_PHPCMS') or exit('No permission resources.');

class mytest{
    function __construct(){

    }
    public function init(){
        $myvar = "hello word";
        echo $myvar;
    }
    public function mylist(){
        $myvar = 'hello world!this is a example!';
        echo $myvar;
    }

}
?>