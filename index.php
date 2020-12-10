<?php
@header('Content-Type: application/json; charset=UTF-8'); //Json形式
//定义当前目录绝对路径
define('DIR',dirname(__FILE__));
//加载这个文件
require DIR . '/oreo/loading.php';
spl_autoload_register("\\oreo\\loading::autoload");
//下面开始用类里面的函数了
$db = new \oreo\conn\OreoPdo(); //实例化
$a = $db->table('oreo_user')->where('id','=',2)->select();
echo $a;






