<?php
namespace V1\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index(){
      halt('error page');
		echo "当前模块名:" . MODULE_NAME;
		echo "<br />";
		echo "当前模块路径:" . MODULE_PATH;
		echo "<br />";
		echo "当前控制器名:" . CONTROLLER_NAME;
		echo "<br />";
		echo "当前控制器路径:" . CONTROLLER_PATH;
		echo "<br />";
		echo "当前操作名:" . ACTION_NAME;
		echo "<br />";
    }
}
