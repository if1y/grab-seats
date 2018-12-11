<?php 
class BaseEntity {

    protected $tableName;
    protected $_fields;
    public    $row;
    public    $isNewRecord = true;
    public function get($id) {
        $dbhandler = $this->getReadDb();
        $sql = "select " . $this->_fields . " from ".$this->tableName 
             . " where id = :id";
        $row = $dbhandler->fetchRow($sql, [":id" => $id]);
        $this->row = $row;
        $this->isNewRecord = false;
        return $this;
    }
    
    /**
     * 指定返回列
     **/
    public function fields($fields) {
        $this->_fields = $fields;
        return $this;
    }
    
    public function setRow($row) {
        $this->row = $row;
        return $this;
    }
    
    public function save() {
        if ($this->isNewRecord) { 
            $this->row['created'] = time();
            return $this->getWriteDb()->addTableRow($this->tableName, $this->row);
        } else {
            return $this->getWriteDb()->updateTable($this->tableName, $this->row, $this->row['id']);
        }
    }
    
    /**
     * 获取列表
     * $where 筛选条件 
     * $conds 参数
     **/
    public function fetchList($where, $conds) {
        $sql = "select " . $this->_fields . " from ".$this->tableName ;
        if ($where) {
            $sql .= " where ". $where;
        }
        return $this->getWriteDb()->fetchRowAll($sql, $conds);
    }
    
    public function getReadDb() {
        return DBFactory::getReadDb();
    }
    
    public function getWriteDb() {
        return DBFactory::getWriteDb();
    }
    
};