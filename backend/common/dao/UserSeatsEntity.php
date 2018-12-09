<?php 
require_once "BaseEntity.php";
class UserSeatsEntity  extends BaseEntity {

    protected $tableName = "tx_user_seats";
    protected $_fields = " id,account,event_id,seat_info,created ";
}