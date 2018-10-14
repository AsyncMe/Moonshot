<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/8/2
 * Time: 下午1:55
 */

namespace api\model;


class ConfigModel extends ApiModel
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





}