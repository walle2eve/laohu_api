<?php
namespace V2\Controller;

use Think\Controller\RestController;

use V2\Service\PlayerService;

class BaseController extends RestController{

	public function _initialize(){

		$this->check_method();

		$post_data = I('post.','');

		$this->check_operator($post_data);

		$this->check_sign($post_data);
	}

	private function check_method(){
		if(strtolower($this->_method) != 'post')
			exit('error method');
	}

	// 检测提交的运营商Key
	private function check_operator($data){

		$return = PlayerService::check_operator($data);

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

	private function check_sign($data){

		$return = PlayerService::check_sign($data);

		if($return === false){
			$err_code = 1016;
			$result = array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code)
			);
			$this->response($result,'json');
			exit();
		}
	}
}
