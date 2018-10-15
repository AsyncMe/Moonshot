<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/27
 * Time: 上午12:20
 */

namespace manager\model;


use libs\asyncme\Service;

/**
 * Class AssetsModel
 * @package manager\model
 * @name 业务应用模型类
 */
class WorksAppTemplateModel extends ManagerModel
{


    protected $app_template_table = 'works_app_template';

    /**
     * @name app模版列表
     * @param array $where
     * @param array $order
     * @param int $page
     * @param int $per_page
     * @param bool $raw
     * @return mixed
     */
    public function worksAppTemplateLists($where=[],$order=[],$page=1,$per_page=20,$raw=false)
    {
        return $this->tableLists($this->app_template_table,$where,$order,$page,$per_page,$raw);
    }

    /**
     * @name app模版数量
     * @param array $where
     * @param bool $raw
     * @return mixed
     */
    public function worksAppTemplateCount($where=[],$raw=false)
    {
        return $this->tableCount($this->app_template_table,$where,$raw);
    }

    /**
     * @name app模版信息
     * @param array $where
     * @return array
     */
    public function worksAppTemplateInfo($where=[])
    {
        $res = $this->db->table($this->app_template_table)->where($where)->first();
        if ($res) {
            $res = (array) $res;
            if($res['config']) {
                $res['config'] = ng_mysql_json_safe_decode($res['config']);
            }
            if($res['preview']) {
                $res['preview'] = ng_mysql_json_safe_decode($res['preview']);
            }
        }
        return $res;
    }

    /**
     * @name 添加app模版
     * @param $map
     * @return mixed
     */
    public function addWorksAppTemplate($map)
    {
        if (!$map['template_sid']) {
            $map['template_sid'] = substr(md5(getRandomStr().microtime(true)),8,16);
        }
        if (!$map['status']) $map['status'] = 0;
        if (!$map['mtime']) $map['mtime'] = time();
        if (!$map['ctime']) $map['ctime'] = time();

        $flag = $this->db->table($this->app_template_table)->insertGetId($map);
        return $flag;
    }

    /**
     * @name 保存app模版
     * @param array $where
     * @param $map
     * @return mixed
     * @throws \Exception
     */
    public function saveWorksAppTemplate($where=[],$map)
    {
        if (!$where['template_sid']) {
            throw new \Exception('保存用户必须有template_sid');
        }
        if (!$map['mtime']) $map['mtime'] = time();
        $flag = $this->db->table($this->app_template_table)->where($where)->update($map);

        return $flag;
    }

    /**
     * @name 修改状态
     * @param $template_sid
     * @param $status
     * @return mixed
     */
    public function changeStatusWorksAppTemplate($template_sid,$status)
    {
        $map = [
            'status'=>$status,
            'mtime'=>time(),
        ];
        $where = ['template_sid'=>$template_sid];
        $flag = $this->db->table($this->app_template_table)->where($where)->update($map);
        return $flag;
    }


    /**
     * @name 物理删除
     * @param $where
     * @param bool $raw
     * @return mixed
     * @throws \Exception
     */
    public function deleteWorksAppTemplate($where,$raw=false)
    {

        $obj = $this->db->table($this->app_template_table);
        if (!$raw) {
            if (!$where['template_sid']) {
                throw new \Exception('保存用户必须有template_sid');
            }
            $obj=$obj->where($where);
        } else {
            if (!preg_match('/template_sid/is',$where[0])) {
                throw new \Exception('保存用户必须有template_sid');
            }
            $obj=$obj->whereRaw($where[0],$where[1]);
        }
        return $obj->delete();
    }
    
}