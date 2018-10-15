<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/8/2
 * Time: 下午1:55
 */

namespace api\model;


use api\model\ApiModel;

class AccountModel extends ApiModel
{

    protected $company_account_table = 'sys_company_account';
    /*
     * 通过用户名获取用户
     */
    public function getManagerWithName($company_id,$accout)
    {
        $map = [
            'group_id'=> $company_id,
            'account' => $accout
        ];
        $res = $this->db->table($this->company_account_table)->where($map)->first();
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
     * 运营者管理
     * @param array $where
     * @param array $order
     * @param int $page
     * @param int $per_page
     * @param bool $raw
     * @return mixed
     */
    public function companyLists($where=[],$order=[],$page=1,$per_page=20,$raw=false)
    {
        return $this->tableLists($this->company_account_table,$where,$order,$page,$per_page,$raw);
    }

    public function companyCount($where=[],$raw=false)
    {
        return $this->tableCount($this->company_account_table,$where,$raw);
    }

    public function getCompanyAccount($where=[])
    {
        $res = $this->db->table($this->company_account_table)->where($where)->first();
        if ($res) {
            $res = (array) $res;
        }
        return $res;
    }

    public function addCompanyAccount($map)
    {
        $flag = $this->db->table($this->company_account_table)->insertGetId($map);
        return $flag;
    }

    public function saveCompanyAccount($where=[],$map)
    {
        $flag = $this->db->table($this->company_account_table)->where($where)->update($map);
        return $flag;
    }

    public function deleteCompanyAccount($where,$raw=false)
    {
        $obj = $this->db->table($this->company_account_table);
        if (!$raw) {
            $obj=$obj->where($where);
        } else {
            $obj=$obj->whereRaw($where[0],$where[1]);
        }
        return $obj->delete();
    }

}