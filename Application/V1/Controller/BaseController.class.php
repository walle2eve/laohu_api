<?php
namespace V1\Controller;

use Think\Controller\RestController;

use V1\Service\PlayerService;

class BaseController extends RestController{

	public function _initialize(){

		$this->check_method();

		$this->check_operator();

	}

	private function check_method(){
		echo $this->_method;
		print_r($_SERVER);
		if(strtolower($this->_method) != 'post')exit('error method');

	}

	// 检测提交的运营商Key
	private function check_operator(){
		$operator_id = I('post.operatorid','');
		$operator_key = I('post.operatorkey','');

		$return = PlayerService::check_operator($operator_id,$operator_key);

		if($return === false){
			$err_code = 1002;
			$result = array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code)
			);
			$this->response($result,'json');
			exit();
		}
	}
}
