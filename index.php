<?php
define('DIR',dirname(__FILE__));
require DIR."/vendor/autoload.php";
$db = new oreo\db\OreoPdo(); //实例化
$data= ['uid'=>1000,'type'=>'测试Test','date'=>date('Y-m-d H:i:s'),'city'=>'南京','data'=>'测试'];
$db->table('oreo_log')->insert($data);
echo $db->getJson(200,'ok');