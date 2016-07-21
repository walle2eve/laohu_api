<?php
namespace V1\Service;

use \V1\Model\SpinLogModel;
use \V1\Model\UserOrderInfoModel;
use \V1\Model\SysLogModel;

class PlayerService extends BaseService{
	/**
	 * @function register 		玩家注册
	 * @param $operator_id		运营商ID
	 * @param $operator_key		运营商Key
	 * @param $player_account	玩家登录名
	 * @param $password			玩家登录密码
	 * @param $sex				性别
	 * @param $first_name		名
	 * @param $last_name  		姓
	 * @param $birthdy			出生日期
	 * @param $nick_name		昵称
	 * @param $mobile_number	手机
	 * @param $country			国家
	 * @param $city				城市
	 * @param $email			email
	 * @param $qq				qq
	 * @param $vip_level		vip等级
	 * @param $param1			扩展参数1
	 * @param $param2			扩展参数2
	 * @return array
	 */
  public static function register($param = array()){

		// 必填项不能为空
		if((!isset($param['playeraccount']) || $param['playeraccount'] == '') || (!isset($param['password']) || $param['password']== '')){

			$err_code = 1007;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		$user_info = D('UserInfo')->get_user_by_accountid($param['operatorid'],$param['playeraccount']);

		if(!empty($user_info)){

			$err_code = 1003;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		// 验证参数
		$data = array();
		$param_err = 0;

		// 字符串长度检测

		if(strlen($param['playeraccount']) > 60){
			$param_err = 1;
		}else{
			$data['account_id'] = trim($param['playeraccount']);
		}

		$data['operator_id'] = $param['operatorid'];

		$data['uniquekey'] = to_guid_string($data['operator_id'] . $data['account_id']);

		$data['password'] = get_pwd($param['password'],$data['uniquekey']);

		if($param['sex'] != ''){
			$data['sex'] = !in_array($param['sex'],array('男','女')) ? 0 :  ($param['sex'] == '男' ? 1 : 2);
		}

		if($param['firstname']!=''){
			if(strlen($param['firstname']) > 60){
				$param_err = 1;
			}else{
				$data['first_name'] = trim($param['firstname']);
			}
		}

		if($param['lastname']!=''){
			if(strlen($param['lastname']) > 60){
				$param_err = 1;
			}else{
				$data['last_name'] = trim($param['lastname']);
			}
		}

		if($param['birthday']!=''){
			if(!is_datetime($param['birthday'])){
				$param_err = 1;
			}else{
				$data['birthday'] = date('Y-m-d',strtotime($param['birthday']));
			}
		}

		if($param['nickname']!=''){
			if(strlen($param['nickname']) > 60){
				$param_err = 1;
			}else{
				$data['nick_name'] = trim($param['nickname']);
			}
		}
		//$operator_id,$operator_key,$player_account,$password,$sex='',$first_name='',$last_name='',$birthday='',$nick_name='',$mobile_number='',$country='',$city='',$email='',$qq='',$vip_level='',$param1='',$param2=''
		if($param['mobilenumber']!=''){
			if(strlen($param['mobilenumber']) > 20){
				$param_err = 1;
			}else{
				$data['mobile_number'] = trim($param['mobilenumber']);
			}
		}

		if($param['country']!=''){
			if(strlen($param['country']) > 10){
				$param_err = 1;
			}else{
				$data['country'] = trim($param['country']);
			}
		}

		if($param['city']!=''){
			if(strlen($param['city']) > 60){
				$param_err = 1;
			}else{
				$data['city'] = trim($param['city']);
			}
		}

		if($param['email']!=''){
			if(strlen($param['email']) > 60){
				$param_err = 1;
			}else{
				$data['email'] = trim($param['email']);
			}
		}

		if($param['qq']!=''){
			if(strlen($param['email']) > 20){
				$param_err = 1;
			}else{
				$data['qq'] = trim($param['qq']);
			}
		}

		if($param['vip_level']!=''){
			$data['vip_level'] = intval($param['vip_level']);
		}

		if($param['param1']!=''){
			if(strlen($param['param1']) > 60){
				$param_err = 1;
			}else{
				$data['param1'] = trim($param['param1']);
			}
		}

		if($param['param2']!=''){
			if(strlen($param['param2']) > 60){
				$param_err = 1;
			}else{
				$data['param2'] = trim($param['param2']);
			}
		}

		// 为防止数据库gold字段默认值和是否为空发生变化，这里生成用户时默认gold值为0
		$data['gold'] = 0;

		$data['create_time'] = date('Y-m-d H:i:s');

		// 创建玩家账号

		$user_id = D('UserInfo')->add_player($data);

		if($user_id){
			$err_code = 0;
			$return = array(
				'ret' => 0,
				'playeraccount' => $param['playeraccount'],
				'password' => $param['password'],
			);
		}else{

			$user_id = 0;
			$err_code = 1099;

			$return = array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		$content =  get_log_content(SysLogModel::PLAYER_REGISTER,array('player_account'=>$data['account_id'])) . ($err_code == 0 ? '成功' : '失败');

		$log_result = D('SysLog')->add_log(SysLogModel::API_DO_LOG,$content,SysLogModel::PLAYER_REGISTER,$data['operator_id'],$user_id,$reason);

		return $return;
  }

	/**
	 * @function get_info	获取玩家信息
	 * @param $operator_id		运营商ID
	 * @param $operator_key		运营商Key
	 * @param $player_account	玩家登录名
	 * @return array
	 */
	public static function get_info($param = array()){
		// 必填项不能为空
		if(!isset($param['playeraccount']) || $param['playeraccount'] == ''){

			$err_code = 1007;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		$user_info = D('UserInfo')->get_user_by_accountid($param['operatorid'],$param['playeraccount']);

		if(!$user_info){

			$err_code = 1004;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		return array(
			'ret' => 0,
			'playeraccount' => $user_info['account_id'],
			'password' => $user_info['password'],
			'sex'	=> $user_info['sex'] ? $user_info['sex'] : '',
			'firstname' => $user_info['first_name'] ? $user_info['first_name'] : '',
			'lastname' => $user_info['last_name'] ? $user_info['last_name'] : '',
			'birthday' => $user_info['birthday'] ? $user_info['birthday'] : '',
			'nickname' => $user_info['nick_name'] ? $user_info['nick_name'] : '',
			'mobilenumber' => $user_info['mobile_number'] ? $user_info['mobile_number'] : '',
			'country' => $user_info['country'] ? $user_info['country'] : '',
			'city' => $user_info['city'] ? $user_info['city'] : '',
			'email' => $user_info['email'] ? $user_info['email'] : '',
			'qq' => $user_info['qq'] ? $user_info['qq'] : '',
			'viplevel' => $user_info['vip_level'] ? $user_info['vip_level'] : '',
			'playertype' => $user_info['player_type'] ? $user_info['player_type'] : '',
			'playerstatus' => $user_info['status'] ? $user_info['status'] : '',
			'gold'	=> $user_info['gold'] ? $user_info['gold'] : '',
			'registerdate' => $user_info['create_time'] ? $user_info['create_time'] : '',
			'lastlogintime' => $user_info['last_login_time'] ? $user_info['last_login_time'] : '',
			'lastloginip' => $user_info['last_login_ip'] ? $user_info['last_login_ip'] : '',
		);
	}

	/**
	 * @function update_pwd
	 * @param $operator_id		运营商ID
	 * @param $operator_key		运营商Key
	 * @param $player_account	玩家登录名
	 * @param $newpassword		新密码
	 * @return array
	 */
	public static function update_pwd($param = array()){
		// 必填项不能为空
		if((!isset($param['playeraccount']) || $param['playeraccount'] == '') || (!isset($param['newpassword']) || $param['newpassword']== '')){

			$err_code = 1007;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		$user_info = D('UserInfo')->get_user_by_accountid($param['operatorid'],$param['playeraccount']);

		if(!$user_info){

			$err_code = 1004;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		$password = get_pwd($param['newpassword'],$user_info['uniquekey']);

		$return = D('UserInfo')->update_pwd($param['operatorid'],$user_info['user_id'],$password);

		if($return === false){

			$err_code = 1098;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		return array(
			'ret' => 0,
			'playeraccount' => $param['playeraccount']
		);
	}

	/**
	 * @function frozen			冻结玩家账号
	 * @param $operator_id		运营商ID
	 * @param $operator_key		运营商Key
	 * @param $player_account	玩家登录名
	 * @param $reason			冻结原因
	 * @return array
	 */
	public static function frozen($param = array()){
		// 必填项不能为空
		if((!isset($param['playeraccount']) || $param['playeraccount'] == '') || (!isset($param['reason']) || trim($param['reason']) == '')){

			$err_code = 1007;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		$user_info = D('UserInfo')->get_user_by_accountid($param['operatorid'],$param['playeraccount']);

		if(!$user_info){

			$err_code = 1004;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		// 冻结操作

		$return = D('UserInfo')->frozen($user_info,$param['reason']);
		if($return === false){
			$err_code = 1099;
			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}
		return array(
			'ret' => 0,
			'playeraccount' => $param['playeraccount'],
			'playerstatus'	=> -1,
		);
	}
	/**
	 * @function set_viplevel			设置玩家vip等级
	 * @param $operator_id		运营商ID
	 * @param $operator_key		运营商Key
	 * @param $player_account	玩家登录名
	 * @param $viplev					vip等级
	 * @return array
	 */
	public static function set_viplevel($param = array()){
		// 必填项不能为空
		if((!isset($param['playeraccount']) || $param['playeraccount'] == '') || (!isset($param['viplev']) || trim($param['viplev']) == '')){

			$err_code = 1007;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		$user_info = D('UserInfo')->get_user_by_accountid($param['operatorid'],$param['playeraccount']);

		if(!$user_info){

			$err_code = 1004;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		// 设置vip等级

		$return = D('UserInfo')->set_viplevel($user_info,$param['viplev']);
		if($return === false){
			$err_code = 1099;
			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		$log_content = get_log_content(SysLogModel::SET_VIP_LEVEL,array('vip_level'=>$param['viplev']));

		D('SysLog')->add_log(SysLogModel::API_DO_LOG,$log_content,SysLogModel::SET_VIP_LEVEL,$param['operatorid'],$user_info['user_id']);

		return array(
			'ret' => 0,
			'playeraccount' => $param['playeraccount'],
			'playerstatus'	=> $param['viplev'],
		);
	}
	/**
	 * @function get_all_spindata			获取玩家投注信息
	 */
	public static function get_all_spindata($param = array()){
		// 必填项不能为空
		if((!isset($param['startdate']) || trim($param['startdate'] == '')) || (!isset($param['enddate']) || trim($param['enddate']) == '')){

			$err_code = 1007;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}
		// 是否时间类型
		if(!is_datetime($param['startdate']) || !is_datetime($param['enddate'])){

			$err_code = 1011;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		$param['startdate'] = strtotime($param['startdate']);
		$param['enddate'] = strtotime($param['enddate']);

		$param['pagenum'] = intval($param['pagenum']) ? intval($param['pagenum']) : 1;

		// 开始时间不能大于结束时间
		if($param['startdate'] > $param['enddate'] || (strtotime('-2 day',date($param['enddate'],'Y-m-d H:i:s')) > $param['startdate'])){

			$err_code = 1012;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		// 调取数据

		$result = D('SpinLog')->get_all_spindata($param['operator_id'],$param['startdate'],$param['enddate'],$param['pagenum']);

		return array(
			'ret' => 0,
			'operatorid' => $param['operatorid'],
			'players' => $result['players'],
			'pagination' => $result['pagination']
		);
	}
	/**
	 * @function deposit		玩家充值
	 * @param $operator_id		运营商ID
	 * @param $operator_key		运营商Key
	 * @param $adminname		运营商管理员账号
	 * @param $operatororderid	运营商订单ID
	 * @param $playeraccount	用户账号
	 * @param $amount			转入金额
	 * @return array
	 */
	public static function deposit($param = array()){
		// 必填项不能为空
		if((!isset($param['playeraccount']) || $param['playeraccount'] == '') ||
			(!isset($param['adminname']) || $param['adminname'] == '') ||
			(!isset($param['operatororderid']) || $param['operatororderid'] == '') ||
			(!isset($param['amount']) || $param['amount'] == '')){

			$err_code = 1007;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}
		// 格式化参数

		$param['amount'] = (float)$param['amount'];

		$user_info = D('UserInfo')->get_user_by_accountid($param['operatorid'],$param['playeraccount']);

		if(!$user_info){

			$err_code = 1004;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}
		// 充值
		$return  =  D('UserOrderInfo')->deposit($param['operatorid'],$user_info,$param['adminname'],$param['operatororderid'],$param['amount']);

		if($return['err_code'] > 0){
			$err_code = $return['err_code'];
			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		return array(
			'ret' => 0,
			'playeraccount' => $param['playeraccount'],
			'amount' => $param['amount'],
			'operatorid' => $param['operatorid'],
			'operatororderid' => $param['operatororderid'],
			'kioskorderid' => $return['sn'],
			'executiontime' => get_runtime(),
			'kiosktransactiontime' => $return['create_time'],
		);
	}

	/**
	 * @function deposit		玩家取现
	 * @param $operator_id		运营商ID
	 * @param $operator_key		运营商Key
	 * @param $adminname		运营商管理员账号
	 * @param $operatororderid	运营商订单ID
	 * @param $playeraccount	用户账号
	 * @param $amount			取现金额
	 * @return array
	 */
	public static function withdrawal($param = array()){
		// 必填项不能为空
		if((!isset($param['playeraccount']) || $param['playeraccount'] == '') ||
			(!isset($param['adminname']) || $param['adminname'] == '') ||
			(!isset($param['operatororderid']) || $param['operatororderid'] == '') ||
			(!isset($param['amount']) || $param['amount'] == '')){

			$err_code = 1007;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}
		// 格式化参数

		$param['amount'] = (float)$param['amount'];

		$user_info = D('UserInfo')->get_user_by_accountid($param['operatorid'],$param['playeraccount']);

		if(!$user_info){

			$err_code = 1004;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}
		// 取现
		$return  =  D('UserOrderInfo')->withdrawal($param['operatorid'],$user_info,$param['adminname'],$param['operatororderid'],$param['amount']);

		if($return['err_code'] > 0){
			$err_code = $return['err_code'];
			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		return array(
			'ret' => 0,
			'playeraccount' => $param['playeraccount'],
			'amount' => $param['amount'],
			'operatorid' => $param['operatorid'],
			'operatororderid' => $param['operatororderid'],
			'kioskorderid' => $return['sn'],
			'executiontime' => get_runtime(),
			'kiosktransactiontime' => $return['create_time'],
		);
	}
	/**
	 * @function deposit		玩家取现
	 * @param $operator_id		运营商ID
	 * @param $operator_key		运营商Key
	 * @param $adminname		运营商管理员账号
	 * @param $operatororderid	运营商订单ID
	 * @param $playeraccount	用户账号
	 * @param $amount			取现金额
	 * @return array
	 */
	public static function get_order_status($param = array()){
		// 必填项不能为空
		if((!isset($param['adminname']) || $param['adminname'] == '') ||
			(!isset($param['operatororderid']) || $param['operatororderid'] == '')){

			$err_code = 1007;

			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		// 取现
		$return  =  D('UserOrderInfo')->get_operator_order($param['operatorid'],$param['adminname'],$param['operatororderid']);

		if($return['err_code'] > 0){
			$err_code = $return['err_code'];
			return array(
				'ret' => $err_code,
				'msg' => get_err_msg($err_code),
			);
		}

		$user_info = D('UserInfo')->where('user_id = %d',array($return['player_id']))->find();

		return array(
			'ret' => 0,
			'playeraccount' => $user_info['account_id'],
			'amount' => $return['amount'],
			'operatorid' => $param['operatorid'],
			'operatororderid' => $param['operatororderid'],
			'kioskorderid' => $return['sn'],
			'executiontime' => get_runtime(),
			'kiosktransactiontime' => $return['create_time'],
			'orderstatus'	=> $return['status'],
			'ordertype' => $return['order_type'] == UserOrderInfoModel::DEPOSIT_ORDER_TYPE ? 1 : ($return['order_type'] == UserOrderInfoModel::WITHDRAWAL_ORDER_TYPE ? 2 : 0),
		);
	}
}
