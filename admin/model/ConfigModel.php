<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/8/2
 * Time: 下午1:55
 */

namespace admin\model;


class ConfigModel extends AdminModel
{
    protected $sys_config_table = 'sys_config';


    /**
     * 返回列表
     * @param $where
     * @param array $order
     * @param int $page
     * @return mixed
     */
    public function configLists($where=[],$order=[],$page=1,$per_page=20,$raw=false)
    {
        return $this->tableLists($this->sys_config_table,$where,$order,$page,$per_page,$raw);
    }

    public function configCount($where=[],$raw=false)
    {
        return $this->tableCount($this->sys_config_table,$where,$raw);
    }

    public function getConfigInfo($where=[])
    {
        $res = $this->db->table($this->sys_config_table)->where($where)->first();
        if ($res) {
            $res = (array) $res;
        }
        return $res;
    }

    public function addConfigInfo($map)
    {
        $flag = $this->db->table($this->sys_config_table)->insertGetId($map);
        return $flag;
    }

    public function saveConfigInfo($where=[],$map)
    {
        $flag = $this->db->table($this->sys_config_table)->where($where)->update($map);
        return $flag;
    }

    public function deleteConfigInfo($where,$raw=false)
    {
        $obj = $this->db->table($this->sys_config_table);
        if (!$raw) {
            $obj=$obj->where($where);
        } else {
            $obj=$obj->whereRaw($where[0],$where[1]);
        }
        return $obj->delete();
    }



}