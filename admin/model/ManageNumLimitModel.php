<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/27
 * Time: 下午7:56
 */

namespace admin\model;


class ManageNumLimitModel extends AdminModel
{
    protected $func_privs_table = 'manage_num_limit';



    public function numLimitLists($where=[],$order=[],$page=1,$per_page=20,$raw=false)
    {
        return $this->tableLists($this->func_privs_table,$where,$order,$page,$per_page,$raw);
    }

    public function numLimitCount($where=[],$raw=false)
    {
        return $this->tableCount($this->func_privs_table,$where,$raw);
    }

    public function numLimitInfo($where=[])
    {
        $res = $this->db->table($this->func_privs_table)->where($where)->first();
        if ($res) {
            $res = (array) $res;
        }
        return $res;
    }

    public function addNumLimit($map)
    {
        $flag = $this->db->table($this->func_privs_table)->insertGetId($map);
        return $flag;
    }

    public function saveNumLimit($where=[],$map)
    {
        $flag = $this->db->table($this->func_privs_table)->where($where)->update($map);
        return $flag;
    }

    public function deleteNumLimit($where,$raw=false)
    {
        $obj = $this->db->table($this->func_privs_table);
        if (!$raw) {
            $obj=$obj->where($where);
        } else {
            $obj=$obj->whereRaw($where[0],$where[1]);
        }
        return $obj->delete();
    }

}