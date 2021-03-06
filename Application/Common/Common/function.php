<?php

	/**
	 * @function bootstrap 风格分页
	 * @param $count		总记录数
	 * @param $up_id 		上级菜单ID
	 * @param $level		菜单层级 1,2,3
	 * @return array
	 */
	function page($count,$row=0,$page_num=1){
		if(!$row)$row = C('LIST_ROWS');
		$Page = new \Think\Page($count,$row,array(),$page_num);
		$Page->setConfig('theme',"<ul class='pagination'><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li></ul>");
		return $Page;// 分页显示输出
	}
	// 随机字符串
	function get_rand_char($length=6){
		$str = null;
		$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($strPol)-1;

		for($i=0;$i<$length;$i++){
			$str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
		}

		return $str;
	}
	// login_pwd 加密方式
	function get_pwd($pwd,$salt){
		return md5(md5($pwd) . $salt);
	}
	
	// 检测是否时间格式
	function is_datetime($dateTime){
		$ret = strtotime($dateTime);
		return $ret !== FALSE && $ret != -1;
	}
	
	/**
	*  订单号 
	*/
	function get_sn(){
		$year_code = array('A','B','C','D','E','F','G','H','I','J');
		$order_sn = $year_code[intval(date('Y'))-2016].
		strtoupper(dechex(date('m'))).date('d').
		substr(time(),-5).substr(microtime(),2,5).sprintf('%02d',rand(0,99));
		return $order_sn;
	}
	/**
	 * 获取程序运行时间
	 * return 毫秒数
	 */
	function get_runtime(){
		$ntime=microtime(true);
		$total=$ntime-$GLOBALS['_beginTime'];
		return ceil($total*1000);
	}