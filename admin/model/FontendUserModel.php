<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/27
 * Time: 下午7:56
 */

namespace admin\model;


class FontendUserModel extends AdminModel
{
    protected $user_table = 'frontend_user';
    protected $user_detail_table = 'frontend_user_detail';



    public function userLists($where=[],$order=[],$page=1,$per_page=20,$raw=false)
    {
        return $this->tableLists($this->user_table,$where,$order,$page,$per_page,$raw);
    }

    public function userCount($where=[],$raw=false)
    {
        return $this->tableCount($this->user_table,$where,$raw);
    }

    public function userInfo($where=[])
    {
        $res = $this->db->table($this->user_table)->where($where)->first();
        if ($res) {
            $res = (array) $res;
            if($res['config']) {
                $res['config'] = ng_mysql_json_safe_decode($res['config']);
                $info = $this->userDetail(['sys_uid'=>$res['sys_uid'],'company_id'=>$res['company_id']]);
                $res['info'] = $info;
            }
        }
        return $res;
    }

    public function userDetail($where=[])
    {
        $res = $this->db->table($this->user_detail_table)->where($where)->first();
        if ($res) {
            $res = (array) $res;
            if($res['detail']) {
                $res['detail'] = ng_mysql_json_safe_decode($res['detail']);
            }
        }
        return $res;
    }
    public function addUser($map)
    {
        if (!$map['sys_uid']) {
            $map['sys_uid'] = substr(md5(getRandomStr().microtime(true)),8,16);
        }
        if (!$map['status']) $map['status'] = 0;
        if (!$map['mtime']) $map['mtime'] = time();
        if (!$map['ctime']) $map['ctime'] = time();

        $info = [];
        if($map['detail']) {
            $info['detail'] = ng_mysql_json_safe_encode($map['detail']);
            unset($map['detail']);
        }
        if (!$info['sys_uid']) $info['sys_uid'] = $map['sys_uid'];
        if (!$info['company_id']) $info['company_id'] = $map['company_id'];
        if (!$info['work_id']) $info['work_id'] = $map['work_id'];
        if (!$info['username']) $info['work_id'] = $map['username'];
        if (!$info['mtime']) $info['mtime'] = time();
        if (!$info['ctime']) $info['ctime'] = time();

        $flag = $this->db->table($this->user_table)->insertGetId($map);
        if ($flag) {
            $flag2 = $this->db->table($this->user_detail_table)->insertGetId($info);
            $flag = $flag && $flag2;
        }
        return $flag;
    }


    public function saveUser($where=[],$map)
    {
        if (!$where['sys_uid']) {
            throw new \Exception('保存用户必须有sys_uid');
        }
        if (!$map['mtime']) $map['mtime'] = time();

        if($map['detail']) {
            $info = $map['detail'];
            unset($map['detail']);
        }
        if (!$info['mtime']) $info['mtime'] = time();


        $flag = $this->db->table($this->user_table)->where($where)->update($map);
        if ($flag) {
            $where = ['sys_uid'=>$where['sys_uid']];
            $flag2 = $this->db->table($this->user_detail_table)->where($where)->update($info);
            $flag = $flag && $flag2;
        }
        return $flag;
    }

    public function changeStatusUser($sys_uid,$status)
    {
        $map = [
            'status'=>$status,
            'mtime'=>time(),
        ];
        $where = ['sys_uid'=>$sys_uid];
        $flag = $this->db->table($this->user_table)->where($where)->update($map);
        return $flag;
    }

    public function deleteUser($where,$raw=false)
    {

        $obj = $this->db->table($this->user_table);
        $obj_detail = $this->db->table($this->user_detail_table);
        if (!$raw) {
            if (!$where['sys_uid']) {
                throw new \Exception('保存用户必须有sys_uid');
            }
            $obj=$obj->where($where);
            $obj_detail->where(['sys_uid'=>$where['sys_uid']])->delete();
        } else {
            if (!preg_match('/sys_uid/is',$where[0])) {
                throw new \Exception('保存用户必须有sys_uid');
            }
            $obj=$obj->whereRaw($where[0],$where[1]);
            $obj_detail->whereRaw($where[0],$where[1])->delete();
        }
        return $obj->delete();
    }

}