<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/27
 * Time: 上午12:20
 */

namespace api\model;


use libs\asyncme\Service;

class ApiModel
{
    public $service = null;

    public $db = null;

    public $cache = null;

    public $redis = null;

    public function __construct(Service $service)
    {
        $this->service = $service;
        $this->db = $service->getDb();
        $this->cache = $service->getCache();
        $this->redis = $service->getRedis();
        if(method_exists($this,'init')) {
            call_user_func([$this,'init']);
        }
    }

    /**
     * 获得配置
     * @param $name
     * @return array
     */
    public function getConfig($name)
    {
        $map = [
            'name'=> $name
        ];
        $res = $this->db->table('sys_config')->where($map)->first();
        if ($res && isset($res->config)) {
            $res = ng_mysql_json_safe_decode($res->config);

        }
        return $res;
    }

    /**
     * 添加系统日志
     * @param $bid
     * @param $loginfo
     * @param string $type
     */
    public function sysLog($bid,$loginfo,$type='sys')
    {
        if($bid && $loginfo) {
            $info = ng_mysql_json_safe_encode($loginfo);
            $map = [
                'company_id'=>$bid,
                'info'=>$info,
                'type'=>$type,
                'ctime'=>time()
            ];
            $this->db->table('sys_logs')->insertGetId($map);
        }
    }

    public function str()
    {
        echo __CLASS__;
    }

    /**
     * 获得表前缀
     * @return mixed
     */
    public function get_table_prefix()
    {
        return $this->db->getConnection()->getTablePrefix();
    }

    /**
     * 返回数据表的数据
     * @param $table
     * @param $where
     * @param array $order
     * @param int $page
     * @return mixed
     */
    protected function tableLists($table,$where=[],$order=[],$page=1,$per_page=20,$raw=false)
    {
        $obj = $this->db->table($table);
        if (!$raw) {
            $obj=$obj->where($where);
        } else {
            $obj=$obj->whereRaw($where[0],$where[1]);
        }
        if ($order) {
            if(is_array($order[0])) {
                foreach($order as $val) {
                    $obj = $obj->orderBy($val[0],$val[1]);
                }
            } else {
                $obj = $obj->orderBy($order[0],$order[1]);
            }
        }
        if ($page) {
            $obj = $obj->forPage($page,$per_page);
        }
        $res = $obj->get();
        if ($res) {
            reset($res);
            $res = json_decode(json_encode($res),true);
        }
        return $res;
    }

    /**
     * 返回条数
     * @param $table
     * @param array $where
     * @return mixed
     */
    protected function tableCount($table,$where=[],$raw=false)
    {
        $obj = $this->db->table($table);
        if ($where) {
            if (!$raw) {
                $obj=$obj->where($where);
            } else {
                $obj=$obj->whereRaw($where[0],$where[1]);
            }
        }
        $count = $obj->count();
        return $count;
    }

}