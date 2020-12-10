<?php
namespace oreo; //命名空间

//类   //类名
class loading {

    //公共  //静态  //函数    //函数名 //传来的值
    public static function autoload($className){
        //函数替换字符串中的一些字符
        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, DIR . '\\'. $className) . '.php';
        if(is_file($fileName)){
            require $fileName;
        } else {
            echo $fileName . '文件不存在' ;
            die();
        }
    }

}