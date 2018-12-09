<?php 
require_once "BaseEntity.php";
class StageEntity  extends BaseEntity {

    protected $tableName = "tx_stage";
    protected $_fields = " id,name,opt,created ";
}