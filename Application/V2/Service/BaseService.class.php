<?php
namespace V2\Service;

class BaseService{
	// 检测运营商ID
	public static function check_operator($param = array()){

		$operator_id = !isset($param['operatorid']) || !$param['operatorid'] ? '0' : intval($param['operatorid']); 
		
		if(!$operator_id) return false;
		
		$operator_info = D('SysUser')->get_operator_info($operator_id);
	

		if(!$operator_info || $operator_info['status'] != 1){
			return false;
		}
		
		return true;
		
	}
	// 
	public static function check_sign($param = array()){

		if(empty($param) || !$param['operatorid'] || !$param['sign']){
			return false;
		}

		$operator_info = D('SysUser')->get_operator_info($param['operatorid']);

		$post_sign = $param['sign'];

		$param['apiname'] = ACTION_NAME;

		$signature = self::getSign($param,$operator_info['access_key']);

		if($post_sign != $signature){
			return false;
		}
		return true;
	}
	// 返回签名
	private static function getSign($data,$cdKey='') {

		if($data['apiname'] == 'register_player') {

			$sourceSign = sprintf(
				"birthday=%s&email=%s&firstname=%s&lastname=%s&mobilenumber=%s&nickname=%s&operatorid=%s&password=%s&playeraccount=%s&qq=%s&sex=%s&viplevel=%s",
				$data['birthday'], $data['email'], $data['firstname'], $data['lastname'], $data['mobilenumber'], $data['nickname'], $data['operatorid'], $data['password'], $data['playeraccount'], $data['qq'], $data['sex'], $data['viplevel']
			);

		} else if ($data['apiname'] == 'get_info'){

			$sourceSign = sprintf(
				"operatorid=%s&playeraccount=%s",
				$data['operatorid'], $data['playeraccount']
			);

		} else if ($data['apiname'] == 'update_password'){

			$sourceSign = sprintf(
				"newpassword=%s&operatorid=%s&playeraccount=%s",
				$data['operatorid'], $data['operatorid'], $data['playeraccount']
			);


		} else if ($data['apiname'] == 'deposit_gold'){

			$sourceSign = sprintf(
				"adminname=%s&amount=%s&operatorid=%s&operatororderid=%s&playeraccount=%s",
				$data['adminname'], $data['amount'], $data['operatorid'], $data['operatororderid'], $data['playeraccount']
			);


		} else if ($data['apiname'] == 'withdrawal_gold'){

			$sourceSign = sprintf(
				"adminname=%s&amount=%s&operatorid=%s&operatororderid=%s&playeraccount=%s",
				$data['adminname'], $data['amount'], $data['operatorid'], $data['operatororderid'], $data['playeraccount']
			);

		} else if ($data['apiname'] == 'frozen_player'){

			$sourceSign = sprintf(
				"adminname=%s&operatorid=%s&playeraccount=%s&reason=%s",
				$data['adminname'], $data['operatorid'], $data['playeraccount'], $data['reason']
			);

		} else if ($data['apiname'] == 'get_order_status'){

			$sourceSign = sprintf(
				"adminname=%s&operatorid=%s&operatororderid=%s",
				$data['adminname'], $data['operatorid'], $data['operatororderid']
			);

		} else if ($data['apiname'] == 'get_all_spindata'){

			$sourceSign = sprintf(
				"enddate=%s&operatorid=%s&pagenum=%s&startdate=%s",
				$data['enddate'], $data['operatorid'], $data['pagenum'], $data['startdate']
			);

		} else {
			$array = array();

			ksort($data);

			foreach ($data as $key=>$value) {
				array_push($array, $key.'='.$value);
			}

			$sourceSign = implode($array, '&');
		}
		
		$signature = MD5($sourceSign . $cdKey);

		return $signature;
	}
}