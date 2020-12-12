<?php
namespace Oreo\db;

/**
 *mysql配置文件
 *
 * @author   Oreo饼干
 * @version  1.0
 */
class OreoConn {

    /**
     * 数据库实例
     */
    protected static $db = null;

    /**
     * 数据库驱动
     */
    protected $type = 'mysql';

    /**
     * 是否使用长连接
     */
    protected $pconnect = true;

    /**
     * 数据库的地址
     */
    protected $host;

    /**
     * 数据库端口
     */
    protected $port;

    /**
     * 数据库用户名
     */
    protected $dbUser;

    /**
     * 数据库库名
     */
    protected $dbName;

    /**
     * 数据库密码
     */
    protected $dbPass;

    /**
     * @access public
     * OreoConn constructor.
     */
    public function __construct()
    {
        class_exists('PDO') or die("PDO: class not exists.");
        $this->host   = '127.0.0.1'; //数据库地址 （一般localhost或127.0.0.1）
        $this->port   =  55396;//数据库端口 (一般3306)
        $this->dbUser = 'pay_applet_2free';//数据库用户名
        $this->dbName = 'pay_applet_2free';//数据库库名
        $this->dbPass = 'EWs8jJNxBSKc5Ct6';//数据库密码
        //连接数据库
        if ( is_null(self::$db) ) {
            $this->_connect();
        }
    }

}
