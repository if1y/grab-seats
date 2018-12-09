<?php 
require_once "BaseEntity.php";
class EventsEntity  extends BaseEntity {

    protected $tableName = "tx_events";
    protected $_fields = " id,stage_id,name,seats_per_persion,start_time,end_time,opt,created ";
}