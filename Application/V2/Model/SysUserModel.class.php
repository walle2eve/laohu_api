<?php 
namespace V2\Model;
use Think\Model;

class SysUserModel extends Model
{
    public function get_admin_info($operator_id){
        $admins =  $this->alias('admin')
            ->join('LEFT Join __SYS_ROLE__ ro on ro.id = admin.user_role')
            ->join('LEFT JOIN __SYS_ROLE_OPERATOR__ sro ON sro.role_id = ro.id')
            ->join('LEFT JOIN __OPERATOR__ op ON op.id = sro.operator_id')
            ->where('op.id = %d AND admin.status = 1 AND op.status = 1',array($operator_id))
            ->getField('login_name',true);

        return $admins;
    }
}