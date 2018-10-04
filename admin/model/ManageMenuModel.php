<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/27
 * Time: 下午7:56
 */

namespace admin\model;


class ManageMenuModel extends AdminModel
{
    protected $menu_table = 'manage_menu';

    public function getNav()
    {
        $map = [
            'status'=> 1,
            'parentid' => 0
        ];
        $res = $this->db->table($this->menu_table)->orderBy('listorder','desc')->where($map)->get();
        $res = reset($res);
        foreach ($res as $key => $val ) {
            $res[$key] = (array)$val;
        }
        //权限检查
        return $res;
    }

    public function getSubMenu($parent_id)
    {
        $map = [
            'status'=> 1,
            'parentid' => $parent_id,
        ];
        $res = $this->db->table($this->menu_table)->orderBy('listorder','desc')->where($map)->get();
        $res = reset($res);
        foreach ($res as $key => $val ) {
            $res[$key] = (array)$val;
            $subMenus = $this->getSubMenu($res[$key]['id']);
            $res[$key]['items'] = $subMenus;
        }
        return $res;
    }

    public function menuLists($where=[],$order=[],$page=1,$per_page=20,$raw=false)
    {
        return $this->tableLists($this->menu_table,$where,$order,$page,$per_page,$raw);
    }


    public function menuCount($where=[],$raw=false)
    {
        return $this->tableCount($this->menu_table,$where,$raw);
    }

    public function menuInfo($where=[])
    {
        $res = $this->db->table($this->menu_table)->where($where)->first();
        if ($res) {
            $res = (array) $res;
        }
        return $res;
    }

    public function addMenu($map)
    {
        $flag = $this->db->table($this->menu_table)->insertGetId($map);
        return $flag;
    }

    public function saveMenu($where=[],$map)
    {
        $flag = $this->db->table($this->menu_table)->where($where)->update($map);
        return $flag;
    }

    public function deleteMenu($where,$raw=false)
    {
        $obj = $this->db->table($this->menu_table);
        if (!$raw) {
            $obj=$obj->where($where);
        } else {
            $obj=$obj->whereRaw($where[0],$where[1]);
        }
        return $obj->delete();
    }

}