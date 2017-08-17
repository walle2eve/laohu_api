<?php
namespace V1\Service;

class BaseService{

    protected static $operator_info;

	// 检测运营商ID和KEY
	public static function check_operator($operator_id,$operator_key){
		
		if(!$operator_id || !$operator_key)return false;
		
		$operator_info = D('Operator')->get_operator_info($operator_id);

		if(!$operator_info || $operator_info['access_key'] != $operator_key || $operator_info['status'] != 1){
			return false;
		}

		self::$operator_info = $operator_info;

		return true;
	}
}