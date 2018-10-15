<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/27
 * Time: 上午12:20
 */

namespace api\model;


use api\model\ApiModel;
use libs\asyncme\Service;

/**
 * Class AssetsModel
 * @package manager\model
 * @name 业务模型类
 */
class WorkModel extends ApiModel
{

    protected $works_table = 'works';

    protected $works_admin_table = 'works_admin';

    /**
     * @name 业务列表
     * @param array $where
     * @param array $order
     * @param int $page
     * @param int $per_page
     * @param bool $raw
     * @return mixed
     */
    public function worksLists($where=[],$order=[],$page=1,$per_page=20,$raw=false)
    {
        return $this->tableLists($this->works_table,$where,$order,$page,$per_page,$raw);
    }

    /**
     * @name 业务数量
     * @param array $where
     * @param bool $raw
     * @return mixed
     */
    public function worksCount($where=[],$raw=false)
    {
        return $this->tableCount($this->works_table,$where,$raw);
    }

    /**
     * @name 业务信息
     * @param array $where
     * @return array
     */
    public function worksInfo($where=[])
    {
        $res = $this->db->table($this->works_table)->where($where)->first();
        if ($res) {
            $res = (array) $res;
            if($res['smeta']) {
                $res['smeta'] = ng_mysql_json_safe_decode($res['smeta']);
            }
            if($res['keyworks']) {
                $res['keyworks'] = ng_mysql_json_safe_decode($res['keyworks']);
            }
        }
        return $res;
    }

    /**
     * @name 添加业务
     * @param $map
     * @return mixed
     */
    public function addWorks($map)
    {
        if (!$map['work_id']) {
            $map['work_id'] = substr(md5(getRandomStr().microtime(true)),8,16);
        }
        if (!$map['status']) $map['status'] = 0;
        if (!$map['mtime']) $map['mtime'] = time();
        if (!$map['ctime']) $map['ctime'] = time();

        $flag = $this->db->table($this->works_table)->insertGetId($map);

        return $flag;
    }

    /**
     * @name 保存业务
     * @param array $where
     * @param $map
     * @return mixed
     * @throws \Exception
     */
    public function saveWorks($where=[],$map)
    {
        if (!$where['work_id']) {
            throw new \Exception('保存用户必须有work_id');
        }
        if (!$map['mtime']) $map['mtime'] = time();
        $flag = $this->db->table($this->works_table)->where($where)->update($map);

        return $flag;
    }

    /**
     * @name 修改状态
     * @param $work_id
     * @param $status
     * @return mixed
     */
    public function changeStatusWorks($work_id,$status)
    {
        $map = [
            'status'=>$status,
            'mtime'=>time(),
        ];
        $where = ['work_id'=>$work_id];
        $flag = $this->db->table($this->works_table)->where($where)->update($map);
        return $flag;
    }

    /**
     * @name 审核
     * @param $work_id
     * @param $is_review
     * @return mixed
     */
    public function changeReviewWorks($work_id,$is_review)
    {
        $map = [
            'is_review'=>$is_review,
            'mtime'=>time(),
        ];
        $where = ['work_id'=>$work_id];
        $flag = $this->db->table($this->works_table)->where($where)->update($map);
        return $flag;
    }



    /**
     * @name 物理删除
     * @param $where
     * @param bool $raw
     * @return mixed
     * @throws \Exception
     */
    public function deleteWorks($where,$raw=false)
    {

        $obj = $this->db->table($this->works_table);
        if (!$raw) {
            if (!$where['work_id']) {
                throw new \Exception('保存用户必须有work_id');
            }
            $obj=$obj->where($where);
        } else {
            if (!preg_match('/work_id/is',$where[0])) {
                throw new \Exception('保存用户必须有work_id');
            }
            $obj=$obj->whereRaw($where[0],$where[1]);
        }
        return $obj->delete();
    }

    /**
     * @name 业务管理员列表
     * @param array $where
     * @param array $order
     * @param int $page
     * @param int $per_page
     * @param bool $raw
     * @return mixed
     */
    public function worksAdminLists($where=[],$order=[],$page=1,$per_page=20,$raw=false)
    {
        return $this->tableLists($this->works_admin_table,$where,$order,$page,$per_page,$raw);
    }

    /**
     * @name 业务管理员数量
     * @param array $where
     * @param bool $raw
     * @return mixed
     */
    public function worksAdminCount($where=[],$raw=false)
    {
        return $this->tableCount($this->works_admin_table,$where,$raw);
    }

    /**
     * @name 添加业务管理员
     * @param $map
     * @return mixed
     */
    public function addWorksAdmin($map)
    {

        if (!$map['status']) $map['status'] = 1;
        if (!$map['mtime']) $map['mtime'] = time();
        if (!$map['ctime']) $map['ctime'] = time();

        $flag = $this->db->table($this->works_admin_table)->insertGetId($map);

        return $flag;
    }

    /**
     * @name 保存业务管理员
     * @param array $where
     * @param $map
     * @return mixed
     * @throws \Exception
     */
    public function saveWorksAdmin($where=[],$map)
    {
        if (!$where['work_id']) {
            throw new \Exception('保存用户必须有 work_id');
        }
        if (!$map['mtime']) $map['mtime'] = time();
        $flag = $this->db->table($this->works_admin_table)->where($where)->update($map);

        return $flag;
    }

    /**
     * @name 删除业务管理员
     * @param $where
     * @param bool $raw
     * @return mixed
     * @throws \Exception
     */
    public function deleteWorksAdmin($where,$raw=false)
    {

        $obj = $this->db->table($this->works_admin_table);
        if (!$raw) {
            if (!$where['work_id']) {
                throw new \Exception('保存用户必须有work_id');
            }
            $obj=$obj->where($where);
        } else {
            if (!preg_match('/work_id/is',$where[0])) {
                throw new \Exception('保存用户必须有work_id');
            }
            $obj=$obj->whereRaw($where[0],$where[1]);
        }
        return $obj->delete();
    }

    /**
     * @name 修改业务管理员状态
     * @param $work_id
     * @param $status
     * @return mixed
     */
    public function changeStatusWorksAdmin($work_id,$status)
    {
        $map = [
            'status'=>$status,
            'mtime'=>time(),
        ];
        $where = ['work_id'=>$work_id];
        $flag = $this->db->table($this->works_admin_table)->where($where)->update($map);
        return $flag;
    }
}