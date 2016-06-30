<?php
namespace V1\Service;

class BaseService{
	// 检测运营商ID和KEY
	public static function check_operator($operator_id,$operator_key){
		
		if(!$operator_id || !$operator_key)return false;
		
		$operator_info = D('SysUser')->get_operator_info($operator_id);
		
		if(!$operator_info || $operator_info['access_key'] != $operator_key || $operator_info['status'] != 1){
			return false;
		}
		
		return true;
		
	}
}