<?php
namespace oreo\db;
use PDO;
/**
 *PDO封装类
 *
 * @author   Oreo饼干
 * @version  1.0
 */
class OreoPdo extends OreoConn{

    /**
     * 表名
     */
    private $table;

    /**
     * 查询的字段
     */
    private $field;

    /**
     * 查询条件
     */
    private $where;

    /**
     * 升降序
     */
    private $order;

    /**
     * 查询范围
     */
    private $limit;

    /**
     * 组装的sql语句
     */
    private $sql;

    /**
     * 返回json格式的函数
     * @param int $code 200为成功则失败
     * @param string $data data参数
     * @return false|string
     */
    public function getJson($code,$data){
        if($code==200){
            $arr['code'] = $code;
            $arr['msg'] = '成功';
            $arr['data'] = $data;
        }else{
            $arr['code'] = -1;
            $arr['msg'] = '失败';
            $arr['data'] = $data;
        }
        return json_encode($arr);
    }


    /**
     * PDO连接函数
     * @return false|string
     */
    protected function _connect(){
        $dsn = $this->type.':host='.$this->host.';port='.$this->port.';dbname='.$this->dbName;
        //持久化连接
        $options = $this->pconnect ? array(PDO::ATTR_PERSISTENT=>true) : array();
        try {
            $_db = @new PDO($dsn, $this->dbUser, $this->dbPass, $options);
            $_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  //设置如果sql语句执行错误则抛出异常，事务会自动回滚
            $_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //禁用prepared statements的仿真效果(防SQL注入)
        } catch (\PDOException $e) {
            return $this->getJson(-1,$e->getMessage());
        }
        $_db->exec('SET NAMES utf8');
        self::$db = $_db;
    }

    /**
     * 组装表名
     * @param  string $tableName 表名
     * @return $this|false|string
     */
    public function table($tableName){
        if (!empty($tableName)) {
            $this->table = $tableName;
        } else {
            return $this->getJson(0,'sql查询失败:没有输入表名');
        }
        return $this;
    }

    /**
     * 设置查询字段
     * @param mixed $field 字段数组
     * @return $this
     */
    public function field($field){
        if (is_string($field)) {
            $field = explode(',', $field);
        }
        $nField = array_map(array($this,'_addChar'), $field);
        $this->field = implode(',', $nField);
        return $this;
    }


    /**
     * @param mixed $option 组合条件的二维数组，例：['id'=>1,'name'=>'oreo']
     * @return $this
     */
    public function where($option) {
        $this->where = ' where ';
        $logic = 'and';
        if (is_string($option)) {
            $this->where .= $option;
        }
        elseif (is_array($option)) {
            foreach($option as $k=>$v) {
                if (is_array($v)) {
                    $relative = isset($v[1]) ? $v[1] : '=';
                    $logic    = isset($v[2]) ? $v[2] : 'and';
                    $condition = ' ('.$this->_addChar($k).' '.$relative.' '.$v[0].') ';
                }
                else {
                    $logic = 'and';
                    $condition = ' ('.$this->_addChar($k).'='.$v.') ';
                }
                $this->where .= isset($mark) ? $logic.$condition : $condition;
                $mark = 1;
            }
        }
        return $this;
    }

    /**
     * 设置排序
     * @param string $option
     * @return $this
     */
    public function order(string $option) {
        $this->order = ' order by ';
        if (is_string($option)) {
            $this->order .= $option;
        }
        return $this;
    }

    /**
     * 设置查询行数及页数
     * @param int $page pageSize不为空时为页数，否则为行数
     * @param int $pageSize 为空则函数设定取出行数，不为空则设定取出行数及页数
     * @return $this
     */
    public function limit($page,$pageSize=null) {
        if ($pageSize===null) {
            $this->limit = "limit ".$page;
        }
        else {
            $pageval = intval( ($page - 1) * $pageSize);
            $this->limit = "limit ".$pageval.",".$pageSize;
        }
        return $this;
    }

    /**
     * 取得数据表的字段信息
     * @param string $tbName 表名
     * @return array
     */
    private function _tableFields($tbName) {
        $sql = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME="'.$tbName.'" AND TABLE_SCHEMA="'.$this->dbName.'"';
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $ret = array();
        foreach ($result as $key=>$value) {
            $ret[$value['COLUMN_NAME']] = 1;
        }
        return $ret;
    }

    /**
     * 过滤并格式化数据表字段
     * @param string $tbName 数据表名
     * @param array $data POST提交数据
     * @return array $data
     */
    private function _dataFormat($tbName,$data){
        if (!is_array($data)) return array();
        $table_column = $this->_tableFields($tbName);
        $ret=array();
        foreach ($data as $key=>$val) {
            if (!is_scalar($val)) continue; //值不是标量则跳过
            if (array_key_exists($key,$table_column)) {
                $key = $this->_addChar($key);
                if (is_int($val)) {
                    $val = intval($val);
                } elseif (is_float($val)) {
                    $val = floatval($val);
                } elseif (preg_match('/^\(\w*(\+|\-|\*|\/)?\w*\)$/i', $val)) {
                    // 支持在字段的值里面直接使用其它字段 ,例如 (score+1) (name) 必须包含括号
                    $val = $val;
                } elseif (is_string($val)) {
                    //将字符串中的单引号（'）、双引号（"）、反斜线（\）与 NUL（NULL 字符转义
                    $val = '"'.addslashes($val).'"';
                }
                $ret[$key] = $val;
            }
        }
        return $ret;
    }

    /**
     * 字段和表名添加 `符号
     * 保证指令中使用关键字不出错 针对mysql
     * @param string $value
     * @return string
     */
    private function _addChar($value) {
        if ('*'==$value || false!==strpos($value,'(') || false!==strpos($value,'.') || false!==strpos($value,'`')) {
            //如果包含* 或者 使用了sql方法 则不作处理
        } elseif (false === strpos($value,'`') ) {
            $value = '`'.trim($value).'`';
        }
        return $value;
    }

    /**
     * @param false $all
     * @return mixed
     */
    public function select($all = false) {
        if(!trim($this->field)){
            $field = '*';
        }else{
            $field = trim($this->field);
        }
        $sql = "select ".$field." from ".$this->table." ".trim($this->where)." ".trim($this->order)." ".trim($this->limit);
        $this->_clear();
        return $this->_doQuery($all, trim($sql));
    }

    /**
     * 插入方法
     * @param array $data 字段-值的一维数组
     * @return int 受影响的行数
     */
    public function insert(array $data){
        $data = $this->_dataFormat($this->table,$data);
        if (!$data) return $this->getJson(-1,'插入数据不能为空');
        $sql = "insert into ".$this->table."(".implode(',',array_keys($data)).") values (".implode(',',array_values($data)).")";
        return $this->_doExec($sql);
    }

    /**
     * 更新函数
     * @param array $data 参数数组
     * @return int 受影响的行数
     */
    public function update(array $data) {
        //安全考虑,阻止全表更新
        if (!trim($this->where)) return $this->getJson(-1,'安全考虑,阻止全表更新，where 条件不能为空');
        $data = $this->_dataFormat($this->table,$data);
        if (!$data) return $this->getJson(-1,'更新内容不能为空');
        $valArr = [];
        foreach($data as $k=>$v){
            $valArr[] = $k.'='.$v;
        }
        $valStr = implode(',', $valArr);
        $sql = "update ".trim($this->table)." set ".trim($valStr)." ".trim($this->where);
        return $this->_doExec($sql);
    }

    /**
     * 执行查询 主要针对 SELECT, SHOW 等指令
     * @param false $all
     * @param string $sql sql指令
     * @return mixed
     */
    private function _doQuery($all, $sql='') {
        $this->sql = $sql;
        $pdostmt = self::$db->prepare($this->sql); //prepare或者query 返回一个PDOStatement
        $pdostmt->execute();
        if(!empty($all)){
            $result = $pdostmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            $result = $pdostmt->fetch(PDO::FETCH_ASSOC);
        }
        $this ->close();
        return $result;
    }

    /**
     * 删除方法
     * @return int 受影响的行数
     */
    public function delete() {
        //安全考虑,阻止全表删除
        if (!trim($this->where)) return $this->getJson(-1,'安全考虑,阻止全表更新，where 条件不能为空');
        $sql = "delete from ".$this->table." ".$this->where;
        $this->_clear();
        return $this->_doExec($sql);
    }

    /**
     * 查询总数
     * @access public
     * @return false|string
     */
    public function count(){
        if(empty($this->table)) return $this->getJson(-1,'表名不能为空');
        $sql = 'SELECT COUNT() FROM '.$this->table; // 组装Count关键词
        $pdostmt = self::$db->prepare($sql); //prepare或者query 返回一个PDOStatement
        $pdostmt->execute();
        $result = $pdostmt->fetchColumn();
        $this ->close();
        return $result;
    }

    /**
     * 执行语句 针对 INSERT, UPDATE 以及DELETE,exec结果返回受影响的行数
     * @param string $sql sql指令
     * @return integer
     */
    private function _doExec($sql='') {
        $this->sql = $sql;
        return self::$db->exec($this->sql);
    }

    /**
     * 清理标记函数
     */
    private function _clear() {
        $this->table = '';
        $this->where = '';
        $this->order = '';
        $this->limit = '';
        $this->field = '*';
    }

    /**
     * 启动事务
     * @return void
     */
    public function startTrans() {
        //数据rollback 支持
        self::$db->beginTransaction();
    }

    /**
     * 事务回滚
     * @return void
     */
    public function rollback() {
        self::$db->rollback();
    }

    /**
     * 提交事务
     * @return void
     */
    public function commit() {
        self::$db->commit();
    }

    /**
     * 关闭连接
     * PHP 在脚本结束时会自动关闭连接。
     */
    private function close() {
        if (!is_null(self::$db)) self::$db = null;
    }
}
