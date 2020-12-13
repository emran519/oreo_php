<?php
/**
 * 入口文件
 */
define('OREO',dirname(__FILE__)); //根路径
define('CORE',OREO.'/vendor/core'); //核心文件路径
define('APP',OREO.'/app'); //项目目录
define('DEBUG',True); //debug

if(DEBUG){
    ini_set('display_errors','On');
}else{
    ini_set('display_errors','Off');
}
require OREO."/vendor/autoload.php";
$db = new oreo\db\OreoPdo(); //实例化
$data= ['uid'=>1000,'type'=>'测试Test','date'=>date('Y-m-d H:i:s'),'city'=>'南京','data'=>'测试'];
$db->table('oreo_log')->insert($data);
echo $db->getJson(200,'ok');