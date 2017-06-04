<?php
namespace V2\Controller;

use V2\Service\PlayerService;

class PlayerController extends BaseController{

	// 玩家账号注册
    public function register_player(){

		$param = I('post.');

		$result = PlayerService::register($param);

		$this->response($result,'json');

    }

	// 获取玩家信息
	public function get_info(){

		$param = I('post.');

		$result = PlayerService::get_info($param);

		$this->response($result,'json');
	}

	// 修改玩家登录密码
	public function update_password(){

		$param = I('post.');

		$result = PlayerService::update_pwd($param);

		$this->response($result,'json');
	}

	// 玩家账号冻结
	public function frozen_player(){

		$param = I('post.');

		$result = PlayerService::frozen($param);

		$this->response($result,'json');

	}
	//设置玩家vip等级
	public function set_viplev(){

		$param = I('post.');

		$result = PlayerService::set_viplevel($param);

		$this->response($result,'json');

	}

	// 获取用户投注记录
	public function get_all_spindata(){

		$param = I('post.');

		$result = PlayerService::get_all_spindata($param);

		$this->response($result,'json');
	}

	// 玩家充值
	public function deposit_gold(){

		$param = I('post.');

		$result = PlayerService::deposit($param);

		$this->response($result,'json');
	}

	// 玩家取现
	public function withdrawal_gold(){

		$param = I('post.');

		$result = PlayerService::withdrawal($param);

		$this->response($result,'json');
	}

	// 获取玩家订单状态
	public function get_order_status(){

		$param = I('post.');

		$result = PlayerService::get_order_status($param);

		$this->response($result,'json');
	}
}
