<?php
namespace oreo\conn; //命名空间

use PDO; //引入

//类   //类名   //继承  //谁
class OreoPdo extends OreoConn{

    //表名
    public $table;
    //查询的字段
    public $field;
    //查询条件
    public $where;
    //升降序
    public $order;
    //查询范围
    public $limit;

    /**
     * 返回json格式的函数
     * @param int $code
     * @param string $data
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


    //公共  //函数   //函数名
    public function pdoSql(){
        try { //尝试连接
            //连接PDO
            $db = new PDO("mysql:host={$this->host};dbname={$this->dbName};port={$this->port}",$this->dbUser,$this->dbPass);
            return $this->db = $db;
        }catch(\Exception $e){ //如果连接有问题
            //输出错误并且推出函数
            return $this->getJson(-1,$e->getMessage());
        }
    }

    //组装表名
    public function table($tableName){
        if (!empty($tableName)) {
            $this->table = $tableName;
        } else {
            return $this->getJson(0,'sql查询失败:没有输入表名');
        }
        return $this;
    }

    //组装查询的字段
    public function field($fieldName = '*'){
        if (!empty($fieldName)) {
            $this->field = $fieldName;
        }
        return $this;
    }

    //组装查询的条件
    public function where($whereName, $op, $in){
        if (!empty($whereName)) {
            $this->where = $whereName.$op.$in;
        }
        return $this;
    }

    //组装排序
    public function order($order){
        if (!empty($order)) {
            $this->order = $order;
        }
        return $this;
    }

    //组装范围
    public function limit($order){
        if (!empty($order)) {
            $this->order = $order;
        }
        return $this;
    }

    //查询单个
    public function find(){
        try {
            //组装SQL语句
            $sql = 'SELECT '; // 组装select关键词
            $sql .= $this->field ?: '*'; //查询字段
            $sql .= ' FROM ' . $this->table; //查询的表
            $sql .= $this->where ? ' WHERE ' . $this->where : ' '; //查询条件
            $sql .= $this->order ? ' ORDER BY ' . $this->order : ' '; //排序
            $sql .= ' LIMIT 0,1'; //查询范围
            $this->db->beginTransaction();  //开启事务处理
            // 返回查询到的结果集 到PDOStatement对象  = 全局$pdo对象 ->query(sql语句)
            $results = $this->db->query($sql);
            if ($results && $results->rowCount()) {
                // 设置读取模式
                $results->setFetchMode(PDO::FETCH_ASSOC);
                // 一次性把结果集保存在 `关联数组` 里面
                $rows = $results->fetch();
                // // 返回关联数组的结果集
                return $this->getJson(200,$rows);
            }
        }catch (\PDOException $e) {
            $this->db ->rollBack();  //回滚事务处理
            //抛出错误
            return $this->getJson(0,'操作失败：'.$e->getMessage());
        }
        return $this->getJson(0,$sql);
    }

    //查询所有
    public function select(){
        try {
            //组装SQL语句
            $sql = 'SELECT '; // 组装select关键词
            $sql .= $this->field ?: '*'; //查询字段
            $sql .= ' FROM ' . $this->table; //查询的表
            $sql .= $this->where ? ' WHERE ' . $this->where : ' '; //查询条件
            $sql .= $this->order ? ' ORDER BY ' . $this->order : ' '; //排序
            $sql .= $this->limit ? ' LIMIT ' . $this->limit : ' '; //查询范围
            $this->db->beginTransaction();  //开启事务处理
            // 返回查询到的结果集 到PDOStatement对象  = 全局$pdo对象 ->query(sql语句)
            $results = $this->db->query($sql);
            if ($results && $results->rowCount()) {
                // 设置读取模式
                $results->setFetchMode(PDO::FETCH_ASSOC);
                // 一次性把结果集保存在 `关联数组` 里面
                $rows = $results->fetchAll();
                // // 返回关联数组的结果集
                return $this->getJson(200,$rows);
            }
        }catch (\PDOException $e) {
            $this->db ->rollBack();  //回滚事务处理
            //抛出错误
            return $this->getJson(0,'操作失败：'.$e->getMessage());
        }
        return $this->getJson(0,$sql);
    }


    //查询总数
    public function count(){
        try {
            //组装SQL语句
            $sql = 'SELECT '; // 组装select关键词
            $sql .= $this->field ?: '*'; //查询字段
            $sql .= ' FROM ' . $this->table; //查询的表
            $sql .= $this->where ? ' WHERE ' . $this->where : ' '; //查询条件
            $sql .= $this->order ? ' ORDER BY ' . $this->order : ' '; //排序
            $sql .= $this->limit ? ' LIMIT ' . $this->limit : ' '; //查询范围
            $this->db->beginTransaction();  //开启事务处理
            // 返回查询到的结果集 到PDOStatement对象  = 全局$pdo对象 ->query(sql语句)
            $results = $this->db->query($sql);
            if ($results && $results->rowCount()) {
                // 设置读取模式
                $results->setFetchMode(PDO::FETCH_ASSOC);
                // 统计结果集的总数
                $rowsCount = $results->rowCount();
                // // 返回关联数组的结果集
                return $this->getJson(200,['count'=>$rowsCount]);
            }
        }catch (\PDOException $e) {
            $this->db ->rollBack();  //回滚事务处理
            //抛出错误
            return $this->getJson(0,'操作失败：'.$e->getMessage());
        }
        return $this->getJson(0,$sql);
    }

}
