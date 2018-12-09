<?php 
require_once "BaseEntity.php";
class UsersEntity  extends BaseEntity {

    protected $tableName = "tx_users";
    protected $_fields = " id,account,password,salt,created ";
    
    
    
    public function getByAccount($acc) {
        $dbh = $this->getReadDb();
        $sql = "select {$this->_fields} from {$this->tableName} where account = :account";
        $row = $dbh->fetchRow($sql, [":account" => $acc] );
        $this->row = $row;
        $this->isNewRecord = false;
        return $this;
    }
}