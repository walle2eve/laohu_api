<?php
namespace V1\Model;
use Think\Model;

class SpinLogModel extends Model
{
    protected $connection = 'DB_LAOHU_LOG_CONFIG';
	//protected $trueTableName;
	protected $tablePrefix = '';
	/***
	public function __construct($table_name){
		parent::__construct($table_name);
		$this->trueTableName = $table_name;
	}
	***/
	// 获取游戏记录
	/**
	public function get_all_spindata($operator_id,$begin_time,$end_time,$tables=array()){

		$where = ' 1=1 ';
		if($operator_id != ''){
			$where .= " AND operator_id = " . $operator_id . " ";
		}
		// 数据表中存储的是java类型的时间戳，包含毫秒，需要转换
		$where .= ' AND (createTime BETWEEN ' . ($begin_time * 1000) . ' AND ' . (($end_time) * 1000) . ') ';
		if($account_id != ''){
			$where .= " AND account_id LIKE '%" . $account_id . "%' ";
		}

		if(empty($tables)){
			return false;
		}

		$sqls = array();

		foreach($tables as $table){
			$sqls[] = " SELECT * FROM  " . $table;
		}

		$table = "(". implode(' UNION ALL ',$sqls) .")";

		$list = $this->table($table)->alias('t')->where($where)->select();

		// echo $this->getlastsql();

		foreach($list as &$row){
			// 格式化附加参数
			$json_data = (array)json_decode($row['param']);
			$row['line'] = count($json_data);
		}
		return $list;
	}
	**/
	public function get_all_spindata($operator_id,$begin_time,$end_time,$page_num = 1,$per_page = 500){

		$where = ' 1=1 ';
		if($operator_id != ''){
			$where .= " AND operator_id = " . $operator_id . " ";
		}
		// 数据表中存储的是java类型的时间戳，包含毫秒，需要转换
		$where .= ' AND (createTime BETWEEN ' . ($begin_time * 1000) . ' AND ' . (($end_time) * 1000) . ') ';
		if($account_id != ''){
			$where .= " AND account_id LIKE '%" . $account_id . "%' ";
		}

		$count = $this->where($where)->count();

		$page = page($count,$per_page,$page_num);

    $page->show();

    //print_r($page);exit();

		$result = $this->alias('t')->where($where)->order('account_id,createTime')->limit($page->firstRow.','.$page->listRows)->select();

		$player = '';
		$spin_num = 1;

		foreach($result as $row){
			if($player != $row['account_id']){
				$player = $row['account_id'];
				$spin_num = 1;
			}
			// 格式化附加参数
			//$json_data = (array)json_decode($row['param']);
			//$list['line'] = count($json_data);
			$create_time = ceil($row['createtime'] / 1000);

			$list[$player][] = array(
				'round' => $spin_num,
				'gameid'	=>	intval($row['theme_id']),
        'gamename' => $row['theme_name'],
				'bet'	=>	floatval($row['total_bet']),
				'win'	=> 	floatval($row['win']),
				'type'	=> 	intval($row['reason']),
				'spintime'	=> 	date('Y-m-d H:i:s',$create_time),
			);
			$spin_num ++;
		}

		$data['players'] = $list;

		$data['pagination'] = array(
			"itemspage" => 500,
			"totalpages" => $page->totalPages,
			"currentpage" => $page_num,
			"totalcount" => $count
		);
		return $data;
    }

}
