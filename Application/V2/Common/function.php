<?php
	// 获取错误信息
	function get_err_msg($err_code){
		$param = 'API_ERR_MSG_' . $err_code;
		return L($param);
	}
	// 获取日志内容
	function get_log_content($log_code,$replace_arr = array()){
		
		$param = 'LOG_CONTENT_' . $log_code;
		
		if(!empty($replace_arr))
		
			return L($param,$replace_arr);
		
		else 
			return L($param);
	}
	
