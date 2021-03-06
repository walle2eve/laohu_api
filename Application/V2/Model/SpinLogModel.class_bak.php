<?php
namespace V2\Model;
use Think\Model;

class SpinLogModel extends Model
{
    protected $connection = 'DB_LAOHU_LOG_CONFIG';
	//protected $trueTableName;
	protected $tablePrefix = '';
	// 获取游戏记录
	public function get_all_spindata($operator_id,$begin_time,$end_time,$page_num = 1,$per_page = 500){

		$where = ' 1=1 ';
		// 数据表中存储的是java类型的时间戳，包含毫秒，需要转换
		$where .= ' AND (createTime BETWEEN ' . ($begin_time * 1000) . ' AND ' . (($end_time) * 1000) . ') ';

		if($operator_id != ''){
			$where .= " AND operator_id = " . $operator_id . " ";
		}else{
			$where .= " AND operator_id <> 0 ";
		}
		if($account_id != ''){
			$where .= " AND account_id = '" . $account_id . "' ";
		}else{
			$where .= " AND account_id <> '' ";
		}

		$count = $this->where($where)->count();

		$page = page($count,$per_page,$page_num);

		$page->show();


		$result = $this->alias('t')->where($where)->order('account_id,createTime')->limit($page->firstRow.','.$page->listRows)->select();
	//	echo $this->getlastsql();
		$list = array();
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
