<?php 
namespace V2\Model;
use Think\Model;

class SysUserModel extends Model
{
	public function get_operator_info($uid){
		
		$where['uid'] = $uid;
		
		//$where['status'] = 1;
		
		$where['user_role'] = array(
						'IN',
						array(
							SysDictModel::USER_ROLE_OPERATOR,
							SysDictModel::USER_ROLE_AGENT
						)
					);
		
		return $this->where($where)->find();
	}
	
	// 修改运营商剩余金币余额
	public function update_gold($uid,$gold){
		return $this->where('uid = %d',array($uid))->setField('gold',$gold);
	}
}