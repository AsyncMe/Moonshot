<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/8/2
 * Time: 下午1:55
 */

namespace admin\model;


class Account extends AdminModel
{
    protected $admin_account_table = 'sys_admin_account';
    protected $admin_account_faillog_table = 'sys_admin_account_faillog';
    /*
     * 通过用户名获取用户
     */
    public function getAdminWithName($company_id,$accout)
    {
        $map = [
            'company_id'=> $company_id,
            'account' => $accout
        ];
        $res = $this->db->table($this->admin_account_table)->where($map)->first();
        $res = (array)$res;

        return $res;
    }

    /**
     * 检查密码真伪
     * @param $pass
     * @param $sys_pass
     * @param string $sys_slat
     * @return bool
     */
    public function checkPass($pass,$sys_pass,$sys_slat='')
    {
        $check_pass = md5($pass.$sys_slat);
        return $check_pass==$sys_pass;
    }

    /**
     * 获得登陆失败次数
     * @param $admin_uid
     * @return int
     */
    public function getFailLogin($admin_uid)
    {
        $map = [
            'company_id'=> $admin_uid,
        ];
        $res = $this->db->table($this->admin_account_faillog_table)->select('try_count')->where($map)->first();
        $res = (array)$res;
        $try_count = 0;
        if ($res) {
            $try_count = $res['try_count'];
        }
        return $try_count;
    }

    /**
     * 更新登陆失败次数
     * @param $admin_uid
     * @param string $type
     */
    public function updateFailLogin($admin_uid,$type='inc')
    {
        $map = [
            'company_id'=> $admin_uid,
        ];
        $obj = $this->db->table($this->admin_account_faillog_table);
        if($type=='destory') {
            $obj->where($map)->delete();
        } else if($type == 'inc') {
            $obj->where($map)->increment('try_count');
        }
    }

    /**
     * 返回列表
     * @param $where
     * @param array $order
     * @param int $page
     * @return mixed
     */
    public function adminLists($where=[],$order=[],$page=1)
    {
        return $this->tableLists($this->admin_account_table,$where,$order,$page);
    }

    public function adminCount($where=[])
    {
        return $this->tableCount($this->admin_account_table,$where);
    }

    public function getAdminAccount($where=[])
    {
        $res = $this->db->table($this->admin_account_table)->where($where)->first();
        if ($res) {
            $res = (array) $res;
        }
        return $res;
    }

}