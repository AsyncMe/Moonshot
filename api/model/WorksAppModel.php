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
 * @name 业务应用模型类
 */
class WorksAppModel extends ApiModel
{

    protected $app_table = 'works_app';
    protected $app_datas_table = 'works_app_datas';

    /**
     * @name 应用列表
     * @param array $where
     * @param array $order
     * @param int $page
     * @param int $per_page
     * @param bool $raw
     * @return mixed
     */
    public function worksAppLists($where=[],$order=[],$page=1,$per_page=20,$raw=false)
    {
        return $this->tableLists($this->app_table,$where,$order,$page,$per_page,$raw);
    }

    /**
     * @name 应用数量
     * @param array $where
     * @param bool $raw
     * @return mixed
     */
    public function worksAppCount($where=[],$raw=false)
    {
        return $this->tableCount($this->app_table,$where,$raw);
    }

    /**
     * @name 应用信息
     * @param array $where
     * @return array
     */
    public function worksAppInfo($where=[])
    {
        $res = $this->db->table($this->app_table)->where($where)->first();
        if ($res) {
            $res = (array) $res;
            if($res['config']) {
                $res['config'] = ng_mysql_json_safe_decode($res['config']);
            }
            if($res['project_config']) {
                $res['project_config'] = ng_mysql_json_safe_decode($res['project_config']);
            }
        }
        return $res;
    }

    /**
     * @name 添加应用
     * @param $map
     * @return mixed
     */
    public function addWorksApp($map)
    {
        if (!$map['app_sid']) {
            $map['app_sid'] = substr(md5(getRandomStr().microtime(true)),8,16);
        }
        if (!$map['status']) $map['status'] = 0;
        if (!$map['mtime']) $map['mtime'] = time();
        if (!$map['ctime']) $map['ctime'] = time();

        $flag = $this->db->table($this->app_table)->insertGetId($map);
        if ($flag) {
            $data_map = [
                'app_sid'=>$map['app_sid'],
                'work_id'=>$map['work_id'],
                'company_id'=>$map['company_id'],
                'account_id'=>$map['account_id'],
            ];
            $data_map['mtime'] = time();
            $data_map['ctime'] = time();
            $this->db->table($this->app_datas_table)->insertGetId($data_map);
        }

        return $flag;
    }

    /**
     * @name 保存应用
     * @param array $where
     * @param $map
     * @return mixed
     * @throws \Exception
     */
    public function saveWorksApp($where=[],$map)
    {
        if (!$where['app_sid']) {
            throw new \Exception('保存用户必须有app_sid');
        }
        if (!$map['mtime']) $map['mtime'] = time();
        $flag = $this->db->table($this->app_table)->where($where)->update($map);

        return $flag;
    }

    /**
     * @name 修改状态
     * @param $app_sid
     * @param $status
     * @return mixed
     */
    public function changeStatusWorksApp($app_sid,$status)
    {
        $map = [
            'status'=>$status,
            'mtime'=>time(),
        ];
        $where = ['app_sid'=>$app_sid];
        $flag = $this->db->table($this->app_table)->where($where)->update($map);
        return $flag;
    }


    /**
     * @name 物理删除
     * @param $where
     * @param bool $raw
     * @return mixed
     * @throws \Exception
     */
    public function deleteWorksApp($where,$raw=false)
    {

        $obj = $this->db->table($this->app_table);
        $data_obj = $this->db->table($this->app_datas_table);
        if (!$raw) {
            if (!$where['app_sid']) {
                throw new \Exception('保存用户必须有app_sid');
            }
            $obj=$obj->where($where);
            $data_obj=$data_obj->where($where);
        } else {
            if (!preg_match('/app_sid/is',$where[0])) {
                throw new \Exception('保存用户必须有app_sid');
            }
            $obj=$obj->whereRaw($where[0],$where[1]);
            $data_obj=$data_obj->whereRaw($where[0],$where[1]);
        }
        $data_obj->delete();
        return $obj->delete();
    }

    /**
     * @name 应用详细信息
     * @param array $where
     * @return array
     */
    public function worksDatasAppInfo($where=[])
    {
        $res = $this->db->table($this->app_datas_table)->where($where)->first();
        if ($res) {
            $res = (array) $res;
            if($res['datas']) {
                $res['datas'] = ng_mysql_json_safe_decode($res['datas']);
            }
        }
        return $res;
    }

    /**
     * @name 保存应用详细
     * @param array $where
     * @param $map
     * @return mixed
     * @throws \Exception
     */
    public function saveWorksDatasApp($where=[],$map)
    {
        if (!$where['app_sid']) {
            throw new \Exception('保存用户必须有app_sid');
        }
        if (!$map['mtime']) $map['mtime'] = time();
        $flag = $this->db->table($this->app_datas_table)->where($where)->update($map);

        return $flag;
    }

}