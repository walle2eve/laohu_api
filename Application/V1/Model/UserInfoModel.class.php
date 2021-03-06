<?php
namespace V1\Model;

use Think\Model;

use V1\Model\SysLogModel;

class UserInfoModel extends Model
{
    protected $dbName =	'laohu';

    public function __construct($dbName=''){
        parent::__construct();
        if($dbName != '')$this->dbName = $dbName;
    }

    public function get_user_by_accountid($operator_id,$account_id){
		$account_id = trim($account_id);
		return $this->where("operator_id = %d AND account_id = '%s'",array($operator_id,$account_id))->find();
	}

	// 添加玩家账号
	public function add_player($data){
		return $this->add($data);
	}

	// 修改玩家密码
	public function update_pwd($operator_id,$user_id,$password){

		$log_content = get_log_content(SysLogModel::PLAYER_UPDATE_PWD);
		D('SysLog')->add_log(SysLogModel::API_DO_LOG,$log_content,SysLogModel::PLAYER_UPDATE_PWD,$operator_id,$user_id);
		
		return $this->where('user_id = %d',array($user_id))->setField('password',$password);
	}

	// 冻结用户
	public function frozen($user_info,$reason){

		$this->startTrans();

		$return = $this->where('user_id = %d',array($user_info['user_id']))->setField('status',-1);


		if($return === false) {
			$this->rollback();
			return $return;
		}

		$content = get_log_content(SysLogModel::FROZEN_PLAYER);

		$log_result = D('SysLog')->add_log(SysLogModel::API_DO_LOG,$content,SysLogModel::FROZEN_PLAYER,$user_info['operator_id'],$user_info['user_id'],$reason);

		if(!$log_result){
			$this->rollback();
			return $log_result;
		}

		$this->commit();
		return true;
	}

	// 设置玩家vip等级
	public function set_viplevel($user_info,$vip_level){

		$this->startTrans();

		$vip_level = intval($vip_level);

		$return = $this->where('user_id = %d',array($user_info['user_id']))->setField('vip_level',$vip_level);

		if($return === false) {
			$this->rollback();
			return $return;
		}

        $content = get_log_content(SysLogModel::SET_VIP_LEVEL);

        $log_result = D('SysLog')->add_log(SysLogModel::API_DO_LOG,$content,SysLogModel::SET_VIP_LEVEL,$user_info['operator_id'],$user_info['user_id'],$vip_level);

		if(!$log_result){
			$this->rollback();
			return $log_result;
		}

		$this->commit();

		return true;
	}
}
