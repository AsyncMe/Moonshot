<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/24
 * Time: 下午7:09
 */

namespace api;

use api\model;
use libs\asyncme\RequestHelper;
use libs\asyncme\NgFileCache;

class Plugins extends PermissionBase
{

    /**
     * @name 分组请求
     * @param RequestHelper $req
     * @param array $preData
     * @return \libs\asyncme\ResponeHelper
     * @priv allow
     */
    public function groupsAction(RequestHelper $req,array $preData)
    {

        try{
            $request_work_id = $req->post_datas['work_id'];
            $request_app_sid = $req->post_datas['app_sid'];
            $request_user_id = $req->post_datas['user_id'];
            if (!$request_work_id || !$request_app_sid) {
                throw new \Exception('参数不正确');
            }


            $cache_key = 'plugins_'.$req->company_id;
            $cache_key = md5($cache_key);
            $file_cache = NgFileCache::$instance;
            $plugin_cache = $file_cache->get($cache_key);
            if (!$plugin_cache) {
                $where = [
                    'bussine_id'=>$req->company_id,
                ];
                $plugin_model = new model\PluginsModel($this->service);
                $count = $plugin_model->pluginsRelCount($where);
                if (!$count) {
                    throw new \Exception('暂时没有插件');
                }
                $lists = $plugin_model->pluginsRelLists($where);
                $plugin_ids = [];
                foreach($lists as $val) {
                    $plugin_ids[] = $val['plugin_id'];
                }
                $rel_lists = $plugin_model->pluginsListInValue([],['id',$plugin_ids]);
                $info = [];
                if ($rel_lists) {
                    foreach($rel_lists as $key=>$val) {
                        $icon = '/assets/plugin/'.str_replace('../plugins/','',$val['plugin_root']).$val['icon'];
                        $info[$key] = [
                            'plugin_id'=>$val['id'],
                            'name'=>$val['title'],
                            'class_name'=>$val['class_name'],
                            'icon'=>$icon,
                            'plugin_root'=>$val['plugin_root'],
                        ];
                    }
                }
                $file_cache->set($cache_key,$info,300);
                $plugin_cache = $info;
            }

            $status = true;
            $mess = '成功';

            $data = [
                'status'=>$status,
                'info'=>$plugin_cache,
            ];
        }catch(\Exception $e) {
            $error = $e->getMessage();
            $status = false;
            $mess = '失败';

            $data = [
                'status'=>$status,
                'error'=>$error,
            ];
        }

        return $this->render($status,$mess,$data);
    }


    /**
     * @name 插件调用
     * @param RequestHelper $req
     * @param array $preData
     * @return mixed
     * @priv allow
     */
    public function plAction(RequestHelper $req,array $preData)
    {
        $class_name = $req->query_datas['pl_name'];
        $action = $req->query_datas['pl_act'] ;
        $action = $action ? $action : "lists";
        $plugin_req = $req;
        $plugin_req->request_plugin = $class_name;
        $plugin_req->action = $action;

        $plugin_reponse = ng_plugins($plugin_req,$this->service);
        return $plugin_reponse;
    }

}