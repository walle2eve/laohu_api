<?php
namespace V2\Model;
use Think\Model\MongoModel;

class SpinLogModel extends MongoModel
{
    protected $connection 	= 	'DB_TYPE_MONGO_CONFIG';
    protected $dbName		=	'laohu_log';
	protected $tablePrefix 	= 	'';

	public function _initialize(){
		parent::_initialize();

		ini_set('mongo.long_as_object', 1);
	}
	
	// 获取游戏记录
	public function get_all_spindata($operator_id,$begin_time,$end_time,$page_num = 1,$per_page = 500){

		$where =  array();
		// 数据表中存储的是java类型的时间戳，包含毫秒，需要转换	
		$where['createTime']	=	array('between', array($begin_time * 1000, $end_time * 1000 + 999));

		if($operator_id != ''){
			$where['operator_id'] = intval($operator_id);
		}
		if($account_id != ''){
			$where['account_id'] = $account_id;
		}else{
			//$where['account_id'] = array('all','');
		}

		$count = $this->where($where)->count();

		$page = page($count,$per_page,$page_num);

		$page->show();


		$result = $this->field("log_type,log_time,region_id,server_id,operator_id,theme_id,theme_name,game_sort,account_id,nick_name,user_id,bet,total_bet,win,wheel,is_sactter,reason,param,createTime")->where($where)->order('account_id,id')->limit($page->firstRow.','.$page->listRows)->select();
	//	echo $this->getlastsql();
		$list = array();
		$player = '';
		$spin_num = 1;

		foreach($result as &$row){
			foreach($row as $key=>$val){
				if(is_object($row[$key])){
					$row[$key] = $val->value;
				}
			}
			if($player != $row['account_id']){
				$player = $row['account_id'];
				$spin_num = 1;
			}
			// 格式化附加参数
			//$json_data = (array)json_decode($row['param']);
			//$list['line'] = count($json_data);
			
			$createTime = is_object($row['createTime']) ? (array)$row['createTime'] : $row['createTime'];
			$createTime = isset($createTime['value']) ? $createTime['value'] : $createTime;
			$createTime = ceil($createTime / 1000);
			
			$list[$player][] = array(
				'round' => $spin_num,
				'gameid'	=>	intval($row['theme_id']),
				'gamename' => $row['theme_name'],
				'bet'	=>	floatval($row['total_bet']),
				'win'	=> 	floatval($row['win']),
				'type'	=> 	intval($row['reason']),
				'spintime'	=> 	date('Y-m-d H:i:s',$createTime),
			);
			$spin_num ++;
		}

		$data['players'] = $list;

		$totalPages = $page->totalPages ? $page->totalPages : 0;
		
		$data['pagination'] = array(
			"itemspage" => 500,
			"totalpages" => $totalPages,
			"currentpage" => $page_num,
			"totalcount" => $count
		);
		return $data;
    }

}
