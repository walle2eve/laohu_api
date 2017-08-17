<?php
/**
 * Created by PhpStorm.
 * User: panda
 * Date: 2017/7/26
 * Time: 15:05
 */

namespace V1\Model;
use Think\Model;

class OperatorModel extends Model
{
    public function get_operator_info($operator_id){

        $where['id'] = $operator_id;

        return $this->where($where)->find();
    }

    // 修改运营商剩余金币余额
    public function update_gold($operator_id,$gold){
        return $this->where('id = %d',array($operator_id))->setField('gold',$gold);
    }
    public function getall(){
        return $this->select();
    }
}