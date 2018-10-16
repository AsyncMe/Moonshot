<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/27
 * Time: 下午7:56
 */

namespace admin\model;


class PluginModel extends AdminModel
{

    protected $plugin_menu_table = 'sys_plugins_menu';
    protected $plugin_table = 'sys_plugins';
    protected $plugins_rel_table = 'sys_plugins_rel';


    public function has_table($table_name)
    {
        return $this->db->schema()->hasTable($table_name);
    }

    public function create_table($create_sql)
    {
        return $this->execute($create_sql);
    }

    public function drop_table($table_name)
    {
        return $this->db->schema()->dropIfExists($table_name);
    }

    public function execute($sql)
    {
        return $this->db->getConnection()->statement($sql);
    }
    public function backup_plugin_tables($plugin_name,$tables,$backup_root='')
    {
        if (!$backup_root || !is_dir($backup_root)) {
            throw new \Exception('目录为空');
        }
        $log_name = $plugin_name.'_'.date('YmdHis',time()).".sql";
        $backup_log = $backup_root."/".$log_name;
        $logs = [];
        if ($tables) {
            $logs[] = "-- ----------------------------\r\n";
            $logs[] = "-- 日期：".date("Y-m-d H:i:s",time())."\r\n";
            $logs[] = "-- Power by: Moon_Shot\r\n";
            $logs[] = "-- author: AsyncMe \r\n";
            $logs[] = "-- ----------------------------\r\n\r\n";

        }
        file_put_contents($backup_log,implode('',$logs),FILE_APPEND);
        $logs = [];
        foreach($tables as $table_name) {
            if ($this->has_table($table_name)) {
                //处理建表语句
                $real_table_name = $this->get_table_prefix().$table_name;
                $sql = "show create table `".$real_table_name."`";
                $sth = $this->db->getConnection()->getPdo()->query($sql);

                while($row = $sth->fetch()){
                    $logs[] = "-- ----------------------------\r\n";
                    $logs[] = "-- Table structure for `".$real_table_name."`\r\n";
                    $logs[] = "-- ----------------------------\r\n";
                    $logs[] = "DROP TABLE IF EXISTS `".$real_table_name."`;\r\n";
                    $logs[] = $row['Create Table'].";\r\n";
                    //获取数据条数
                    $count =$this->db->table($table_name)->count();
                    if ($count) {
                        $max_id = $this->db->table($table_name)->max('id');
                        $min_id = $this->db->table($table_name)->min('id');
                        $step = 1000;
                        $current_start_id = $min_id;
                        $current_end_id = ($min_id+$step)<=$max_id ? ($min_id+$step) : $max_id;

                        while($current_start_id<$max_id) {
                            $insert_data = $this->db->table($table_name)->whereRaw('id between ? and ? ',[$current_start_id,$current_end_id])->orderBy('id','asc')->get();
                            if ($insert_data) {
                                $insert_data = reset($insert_data);
                                $insert_data = json_decode(json_encode($insert_data),true);

                                if (count($insert_data)) {
                                    $logs[] = "INSERT INTO `".$real_table_name."` VALUES "."\r\n";
                                    $data_item = [];
                                    foreach($insert_data as $insert_item) {
                                        $val_lists = [];
                                        foreach ($insert_item as $key=>$val) {
                                            $val_lists[] = "'$val'";
                                        }
                                        $data_item[] = '('.implode(',',$val_lists).")";
                                        unset($val_lists);
                                    }
                                    $logs[] = implode(",\r\n",$data_item).";\r\n";
                                }

                            }

                            $current_start_id = $current_end_id+1;
                            $current_end_id = ($current_end_id+$step)<=$max_id ? ($current_end_id+$step) : $max_id;

                        }

                    }

                }
                file_put_contents($backup_log,implode('',$logs),FILE_APPEND);
                $logs = [];
            }
        }
        file_put_contents($backup_log,implode('',$logs),FILE_APPEND);
        unset($logs);
        return $log_name;
    }

    public function getSubMenu($parent_id)
    {
        $map = [
            'status'=> 1,
            'parentid' => $parent_id,
        ];
        $res = $this->db->table('sys_plugins_menu')->orderBy('listorder','desc')->where($map)->get();
        $res = reset($res);
        foreach ($res as $key => $val ) {
            $res[$key] = (array)$val;
            $subMenus = $this->getSubMenu($res[$key]['id']);
            $res[$key]['items'] = $subMenus;
        }
        return $res;
    }

    /**
     * 关联关系表
     * @param $where
     */
    public function getPluginRelCount($where)
    {
        return $this->db->table($this->plugins_rel_table)->where($where)->count();
    }

    public function deletePluginRel($where)
    {
        return $this->db->table($this->plugins_rel_table)->where($where)->delete();
    }

    public function addPluginRel($map)
    {
        return $this->db->table($this->plugins_rel_table)->insertGetId($map);
    }

    public function getPluginRelLists($where,$filed=[],$orderby=[],$limit='')
    {
        $process = $this->db->table($this->plugins_rel_table)->where($where);
        if($filed) {
            $process = $process->select($filed);
        }
        if($orderby && is_array($orderby)) {
            foreach ($orderby as $order_item) {
                $process = $process->orderBy($order_item[0],$order_item[1]);
            }
        }

        if($limit) {
            $process = $process->limit($limit);
        }
        $res = $process->get();
        $res = reset($res);
        if($res) {
            $res = json_decode(json_encode($res),true);
        }
        return $res;
    }

    public function getPluginRelInfo($where,$filed=[])
    {
        $process = $this->db->table($this->plugins_rel_table)->where($where);
        if($filed) {
            $process = $process->select($filed);
        }
        $res = $process->first();
        $res = (array)$res;
        return $res;
    }

    /**
     * 插件菜单表
     * @param $map
     * @return mixed
     */
    public function addPluginMenu($map)
    {
        return $this->db->table($this->plugin_menu_table)->insertGetId($map);
    }

    public function getPluginMenuCount($where)
    {
        return $this->db->table($this->plugin_menu_table)->where($where)->count();
    }

    public function getPluginMenuInfo($where,$filed=[])
    {
        $process = $this->db->table($this->plugin_menu_table)->where($where);
        if($filed) {
            $process = $process->select($filed);
        }
        $res = $process->first();
        $res = (array)$res;
        return $res;
    }

    public function updatePluginMenu($where,$map)
    {
        return $this->db->table($this->plugin_menu_table)->where($where)->update($map);
    }

    public function deletePluginMenu($where)
    {
        return $this->db->table($this->plugin_menu_table)->where($where)->delete();
    }

    /**
     * 插件表
     * @param $map
     * @return mixed
     */
    public function addPlugin($map)
    {
        return $this->db->table($this->plugin_table)->insertGetId($map);
    }

    public function getPluginCount($where)
    {
        return $this->db->table($this->plugin_table)->where($where)->count();
    }

    public function getPluginLists($where,$filed=[],$orderby=[],$limit='')
    {
        $process = $this->db->table($this->plugin_table)->where($where);
        if($filed) {
            $process = $process->select($filed);
        }
        if($orderby && is_array($orderby)) {
            foreach ($orderby as $order_item) {
                $process = $process->orderBy($order_item[0],$order_item[1]);
            }
        }

        if($limit) {
            $process = $process->limit($limit);
        }
        $res = $process->get();
        $res = reset($res);
        if($res) {
            $res = json_decode(json_encode($res),true);
        }
        return $res;
    }

    public function getPluginInfo($where,$filed='')
    {
        $process = $this->db->table($this->plugin_table)->where($where);
        if($filed) {
            $process = $process->select($filed);
        }
        $res = $process->first();
        $res = (array)$res;
        return $res;
    }

    public function updatePlugin($where,$map)
    {
        return $this->db->table($this->plugin_table)->where($where)->update($map);
    }

    public function deletePlugin($where)
    {
        return $this->db->table($this->plugin_table)->where($where)->delete();
    }

}