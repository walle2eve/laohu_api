<?php
namespace V1\Model;
use Think\Model;
use \V1\Model\SysLogModel;
class UserOrderInfoModel extends Model
{
	const DEPOSIT_ORDER_TYPE = 210100;
	const WITHDRAWAL_ORDER_TYPE = 210200;

	// 充值
	public function deposit($operator_info,$user_info,$adminname,$operatororderid,$amount,$userinfoModel,$remark=''){

        $admin_names = D('SysUser')->get_admin_info($operator_info['id']);

		if(!in_array($adminname,$admin_names)){
			// 运营商用户名错误
			return array('err_code'=>1005);
		}

		if($amount <= 0){
			$err_code = 1015;
			$status = -1;
			$remark = get_log_content($err_code);

			return array('err_code'=>$err_code);
		}

		$amount = abs($amount);

		// 查看运营商订单是否重复
		$operator_order = $this->get_operator_order($operator_info['id'],$adminname,$operatororderid);
		if(!isset($operator_order['err_code']) || $operator_order['err_code'] != 1010){
			return array('err_code'=>1013);
		}
		// 充值操作
		$this->startTrans();

		$status = 1;

	    // 检测玩家账号是否被冻结
		if($user_info['status'] != 1){
			$err_code = 1014;
			$status = -1;
			$remark = get_log_content($err_code);

			return array('err_code'=>$err_code);
		}
		// 检测运营商金币余额是否足以抵扣本次用户充值
		if($operator_info['gold'] < $amount){
			$err_code = 1008;
			$status = -1;
			$remark = get_log_content($err_code);

			return array('err_code'=>$err_code);
		}

		// 金币
		$gold = $amount;
		$balance_gold = $user_info['gold'] + $amount;

		// 用户余额增加$amount
		$user_amount_inc = $userinfoModel->where('user_id = %d',array($user_info['user_id']))->setInc('gold',$amount);
		////echo M()->getlastsql();exit();
		if($user_amount_inc == false){
			$this->rollback();
			return array('err_code'=>1099);
		}

		$sn_info = $this->add_order_info($operator_info['id'],$user_info,$adminname,$operatororderid,$amount,$status,self::DEPOSIT_ORDER_TYPE,$gold,$balance_gold,$remark);

		if($sn_info === false){
			$this->rollback();
			return array('err_code'=>1099);
		}


		// 运营商金币余额减去$amount
		$operator_amount_dec = D('Operator')->where('id = %d',array($operator_info['id']))->setDec('gold',$amount);

		if($operator_amount_dec == false){
			$this->rollback();
			return array('err_code'=>1099);
		}

		$this->commit();

		$log_content = get_log_content(SysLogModel::PLAYER_DEPOSIT,array('amount'=>$amount));

		D('SysLog')->add_log(SysLogModel::API_DO_LOG,$log_content,SysLogModel::PLAYER_DEPOSIT,$operator_info['id'],$user_info['user_id']);

		return $sn_info;

	}
	// 取现

    /**
     * @param $operatorid
     * @param $user_info
     * @param $adminname
     * @param $operatororderid
     * @param $amount
     * @param $userinfoModel    userinfoModel 信息
     * @param string $remark
     * @return array|bool
     */
    public function withdrawal($operator_info, $user_info, $adminname, $operatororderid, $amount, $userinfoModel, $remark=''){

        $admin_names = D('SysUser')->get_admin_info($operator_info['id']);

        if(!in_array($adminname,$admin_names)){
            // 运营商用户名错误
            return array('err_code'=>1005);
        }

		if($amount <= 0){
			$err_code = 1015;
			$status = -1;
			$remark = get_log_content($err_code);

			return array('err_code'=>$err_code);
		}

		$amount = abs($amount);

		// 查看运营商订单是否重复
		$operator_order = $this->get_operator_order($operator_info['id'],$adminname,$operatororderid);
		if(!isset($operator_order['err_code']) || $operator_order['err_code'] != 1010){
			return array('err_code'=>1013);
		}
		// 取现操作
		$this->startTrans();

		$status = 1;
	    // 检测玩家账号是否被冻结
		if($user_info['status'] != 1){
			$err_code = 1014;
			$status = -1;
			$remark = get_log_content($err_code);

			return array('err_code'=>$err_code);
		}
		// 检测用户金币余额是否足以抵扣本次用户充值
		if($user_info['gold'] < $amount){
			$err_code = 1008;
			$status = -1;
			$remark = get_log_content($err_code);

			return array('err_code'=>$err_code);
		}
		// 金币
		$gold = $amount;
		$balance_gold = $user_info['gold'] - $amount;

		$sn_info = $this->add_order_info($operator_info['id'],$user_info,$adminname,$operatororderid,$amount,$status,self::WITHDRAWAL_ORDER_TYPE,$gold,$balance_gold,$remark);

		// 生成订单
		if($sn_info === false){
			$this->rollback();
			return array('err_code'=>1099);
		}

		// 用户余额减去$amount
		$user_amount_dec = $userinfoModel->where('user_id = %d',array($user_info['user_id']))->setDec('gold',$amount);

		if($user_amount_dec === false){
			$this->rollback();
			return array('err_code'=>1099);
		}
		// 运营商金币余额增加$amount
		$operator_amount_inc = D('Operator')->where('id = %d',array($operator_info['id']))->setInc('gold',$amount);

		if($operator_amount_inc === false){
			$this->rollback();
			return array('err_code'=>1099);
		}

		$this->commit();

		$log_content = get_log_content(SysLogModel::PLAYER_WITHDRAW,array('amount'=>$amount));

		D('SysLog')->add_log(SysLogModel::API_DO_LOG,$log_content,SysLogModel::PLAYER_WITHDRAW,$operator_info['id'],$user_info['user_id']);
		return $sn_info;
	}

	// 查询订单信息
	public function get_operator_order($operatorid,$adminname,$operatororderid){

        $admin_names = D('SysUser')->get_admin_info($operatorid);

        if(!in_array($adminname,$admin_names)){
            // 运营商用户名错误
            return array('err_code'=>1005);
        }

		$order_info = $this->where("operator_id = %d AND operator_sn = '%s'",array($operatorid,$operatororderid))->find();

		if(empty($order_info)){
			// 订单号错误，查不到该订单
			return array('err_code'=>1010);
		}
		return $order_info;
	}

	// 增加订单信息
	private function add_order_info($operatorid,$user_info,$adminname,$operatororderid,$amount,$status,$order_type,$gold,$balance_gold,$remark=''){

		$data['sn'] = get_sn();
		$data['operator_id'] = $operatorid;
		$data['admin_name'] = $adminname;
		$data['operator_sn'] = $operatororderid;

		$data['player_id']	= $user_info['user_id'];
		$data['amount'] = $amount;
		$data['create_time'] = date('Y-m-d H:i:s');
		$data['status'] = $status;

		$data['gold'] = $gold;
		$data['balance_gold'] = $balance_gold;
		$data['remark'] = $remark;

		$data['order_type'] = $order_type;

		if($id = $this->add($data)){
			return array_merge(
				array('status'=>true,'id'=>$id),
				$data
			);
		}else{
			return false;
		}

	}
}
