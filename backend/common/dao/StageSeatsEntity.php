<?php 
require_once "BaseEntity.php";
class StageSeatsEntity  extends BaseEntity {

    protected $tableName = "tx_stage_seats";
    protected $_fields = " id,stage_id,group_tag,row_idx,col_numbs ";

    /**
     * 获取舞台座位情况
     **/
    public function getStageSeats($stageId) {
       
        return $this->fetchList("stage_id = :stage_id", [":stage_id" => $stageId]);
    }
    
    
}