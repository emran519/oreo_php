<?php
namespace oreo\conn;

class OreoConn {

    //数据库的地址
    public $host;

    //数据库端口
    public $port;

    //数据库用户名
    public $dbUser;

    //数据库库名
    public $dbName;

    //数据库密码
    public $dbPass;

    //数据库信息
    public $db;

    //构造函数 （自动运行）
    public function __construct()
    {
        $this->host   = '127.0.0.1'; //数据库地址
        $this->port   =  55396;//数据库端口
        $this->dbUser = 'oreo_php_test';//数据库用户名
        $this->dbName = 'oreo_php_test';//数据库库名
        $this->dbPass = '123456';//数据库密码
        $this->pdoSql();  //执行pdoSql函数
    }

}
