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
 * @name 资源模型类
 */
class AssetsModel extends ApiModel
{

    protected $assets_table = 'manage_assets';

    /**
     * @name 资料列表
     * @param array $where
     * @param array $order
     * @param int $page
     * @param int $per_page
     * @param bool $raw
     * @return mixed
     */
    public function assetsLists($where=[],$order=[],$page=1,$per_page=20,$raw=false)
    {
        return $this->tableLists($this->assets_table,$where,$order,$page,$per_page,$raw);
    }

    /**
     * @name 资源数量
     * @param array $where
     * @param bool $raw
     * @return mixed
     */
    public function assetsCount($where=[],$raw=false)
    {
        return $this->tableCount($this->assets_table,$where,$raw);
    }

    /**
     * @name 资源信息
     * @param array $where
     * @return array
     */
    public function assetsInfo($where=[])
    {
        $res = $this->db->table($this->assets_table)->where($where)->first();
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
     * @name 添加资源
     * @param $map
     * @return mixed
     */
    public function addAssets($map)
    {
        if (!$map['asset_id']) {
            $map['asset_id'] = substr(md5(getRandomStr().microtime(true)),8,16);
        }
        if (!$map['status']) $map['status'] = 0;
        if (!$map['mtime']) $map['mtime'] = time();
        if (!$map['ctime']) $map['ctime'] = time();

        $flag = $this->db->table($this->assets_table)->insertGetId($map);

        return $flag;
    }

    /**
     * @name 保存资源
     * @param array $where
     * @param $map
     * @return mixed
     * @throws \Exception
     */
    public function saveAssets($where=[],$map)
    {
        if (!$where['asset_id']) {
            throw new \Exception('保存用户必须有asset_id');
        }
        if (!$map['mtime']) $map['mtime'] = time();
        $flag = $this->db->table($this->assets_table)->where($where)->update($map);

        return $flag;
    }

    /**
     * @name 修改状态
     * @param $asset_id
     * @param $status
     * @return mixed
     */
    public function changeStatusAssets($asset_id,$status)
    {
        $map = [
            'status'=>$status,
            'mtime'=>time(),
        ];
        $where = ['asset_id'=>$asset_id];
        $flag = $this->db->table($this->assets_table)->where($where)->update($map);
        return $flag;
    }

    /**
     * @name 审核
     * @param $asset_id
     * @param $is_review
     * @return mixed
     */
    public function changeReviewAssets($asset_id,$is_review)
    {
        $map = [
            'is_review'=>$is_review,
            'mtime'=>time(),
        ];
        $where = ['asset_id'=>$asset_id];
        $flag = $this->db->table($this->assets_table)->where($where)->update($map);
        return $flag;
    }


    /**
     * @name 逻辑删除 默认7天
     * @param $asset_id
     * @param $is_recycle
     * @param int $expire_time
     * @return mixed
     */
    public function removeAssets($asset_id,$is_recycle,$expire_time=3600*24*7)
    {
        if ($is_recycle) {
            $map = [
                'is_recycle'=>1,
                'is_recycle_exipre'=>time()+$expire_time,
            ];
        } else {
            $map = [
                'is_recycle'=>0,
                'is_recycle_exipre'=>0,
            ];
        }
        $where = ['asset_id'=>$asset_id];
        $flag = $this->db->table($this->assets_table)->where($where)->update($map);
        return $flag;
    }

    /**
     * @name 物理删除
     * @param $where
     * @param bool $raw
     * @return mixed
     * @throws \Exception
     */
    public function deleteAssets($where,$raw=false)
    {

        $obj = $this->db->table($this->assets_table);
        if (!$raw) {
            if (!$where['asset_id']) {
                throw new \Exception('保存用户必须有asset_id');
            }
            $obj=$obj->where($where);
        } else {
            if (!preg_match('/asset_id/is',$where[0])) {
                throw new \Exception('保存用户必须有asset_id');
            }
            $obj=$obj->whereRaw($where[0],$where[1]);
        }
        return $obj->delete();
    }

}