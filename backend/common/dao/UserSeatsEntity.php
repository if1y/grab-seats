<?php 
require_once "BaseEntity.php";
class UserSeatsEntity  extends BaseEntity {

    protected $tableName = "tx_user_seats";
    protected $_fields = " id,account,event_id,seat_info,created ";

    public function getSeatsByUser($eventId, $account) {
        return $this->fetchList("event_id = ? and account = ?", [$eventId, $account]);
    }
    
    public function getUserSeatCnt($eventId, $account) {
        $sql = "select count(id) cnt from {$this->tableName} where "
             . " event_id = ? and account = ? ";
        return $this->getReadDb()->fetchRow($sql, [$eventId, $account]);
    }
    
    /**
     * 检查这些座位是否已经被分配出去了
     **/
    public function getAllotedSeats($eventId, $seatInfos) {
        if (!$seatInfos) {
            return [];
        }
        $inS = [];
        $conds = [$eventId];
        foreach ($seatInfos as $one) {
            $inS[] = "?";
            $conds[] = $one;
        }
        $sql = "select seat_info from {$this->tableName} where event_id = ? "
             . " and seat_info in (".implode(',', $inS
        
        return $this->getWriteDb->fetchRowAll($sql, $conds);
    }
}