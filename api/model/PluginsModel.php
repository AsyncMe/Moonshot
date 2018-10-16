<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/27
 * Time: 上午12:20
 */

namespace api\model;


use libs\asyncme\Service;

/**
 * Class AssetsModel
 * @package manager\model
 * @name 业务插件类
 */
class PluginsModel extends ApiModel
{

    protected $plugin_table = 'sys_plugins';
    protected $plugin_rel_table = 'sys_plugins_rel';

    /**
     * @name 插件列表
     * @param array $where
     * @param array $order
     * @param int $page
     * @param int $per_page
     * @param bool $raw
     * @return mixed
     */
    public function pluginsLists($where=[],$order=[],$page=1,$per_page=20,$raw=false)
    {
        return $this->tableLists($this->plugin_table,$where,$order,$page,$per_page,$raw);
    }

    public function pluginsListInValue($where=[],$whereIn=[])
    {
        $obj = $this->db->table($this->plugin_table);
        if (!empty($where)) {
            $obj=$obj->where($where);
        }
        if (!empty($whereIn)) {
            $obj=$obj->whereIn($whereIn[0],$whereIn[1]);
        }
        $res = $obj->get();
        if ($res) {
            reset($res);
            $res = json_decode(json_encode($res),true);
        }
        return $res;
    }

    /**
     * @name 插件数量
     * @param array $where
     * @param bool $raw
     * @return mixed
     */
    public function pluginsCount($where=[],$raw=false)
    {
        return $this->tableCount($this->plugin_table,$where,$raw);
    }

    /**
     * @name 插件信息
     * @param array $where
     * @return array
     */
    public function pluginsInfo($where=[])
    {
        $res = $this->db->table($this->plugin_table)->where($where)->first();
        if ($res) {
            $res = (array) $res;
            if($res['plugin_process']) {
                $res['plugin_process'] = ng_mysql_json_safe_decode($res['plugin_process']);
            }

        }
        return $res;
    }


    /**
     * @name 插件关系列表
     * @param array $where
     * @param array $order
     * @param int $page
     * @param int $per_page
     * @param bool $raw
     * @return mixed
     */
    public function pluginsRelLists($where=[],$order=[],$page=1,$per_page=20,$raw=false)
    {
        return $this->tableLists($this->plugin_rel_table,$where,$order,$page,$per_page,$raw);
    }

    /**
     * @name 插件关系数量
     * @param array $where
     * @param bool $raw
     * @return mixed
     */
    public function pluginsRelCount($where=[],$raw=false)
    {
        return $this->tableCount($this->plugin_rel_table,$where,$raw);
    }

    /**
     * @name 插件关系信息
     * @param array $where
     * @return array
     */
    public function pluginsRelInfo($where=[])
    {
        $res = $this->db->table($this->plugin_rel_table)->where($where)->first();
        if ($res) {
            $res = (array) $res;
            if($res['plugin_process']) {
                $res['plugin_process'] = ng_mysql_json_safe_decode($res['plugin_process']);
            }

        }
        return $res;
    }

}