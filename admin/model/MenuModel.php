<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/27
 * Time: 下午7:56
 */

namespace admin\model;


class MenuModel extends AdminModel
{

    public function getNav()
    {
        $map = [
            'status'=> 1,
            'parentid' => 0
        ];
        $res = $this->db->table('sys_menu')->orderBy('listorder','desc')->where($map)->get();
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
        $res = $this->db->table('sys_menu')->orderBy('listorder','desc')->where($map)->get();
        $res = reset($res);
        foreach ($res as $key => $val ) {
            $res[$key] = (array)$val;
            $subMenus = $this->getSubMenu($res[$key]['id']);
            $res[$key]['items'] = $subMenus;
        }
        return $res;
    }

}