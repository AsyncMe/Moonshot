<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/8/4
 * Time: 下午2:32
 */

namespace admin;

use admin\model;
use libs\asyncme\RequestHelper;
use PHPSQLParser\PHPSQLParser;
use PHPSQLParser\utils\PHPSQLParserConstants;
use libs\asyncme\Page as Page;

class Setting extends PermissionBase
{
    public function indexAction(RequestHelper $req,array $preData)
    {
        $status = true;
        $mess = '成功';

        $nav_data = $this->nav_default($req,$preData);


        //ng_func_privilege_check($req->company_id,$this->sessions['admin_uid'],'index');

        $path = [
            'mark' => 'sys',
            'bid'  => $req->company_id,
            'pl_name'=>'admin',
        ];
        $query = [
            'mod'=>'setting',
            'act'=>'lists'
        ];
        $default_frame_url = urlGen($req,$path,$query,true);



        $plugin_menus = [
            ['id'=>81001,'parentid'=>0,'app'=>'admin' ,'model'=>'setting','action'=>'info',
                'data'=>'','category'=>'设置','placehold'=>'','use_priv'=>1,'type'=>1,
                'link'=>1,'status'=>1,'name'=>'设置','icon'=>'th'
            ],
            ['id'=>81002,'parentid'=>0,'app'=>'admin' ,'model'=>'setting','action'=>'menu',
                'data'=>'','category'=>'设置','placehold'=>'','use_priv'=>1,'type'=>1,
                'link'=>1,'status'=>1,'name'=>'菜单','icon'=>'th'
            ],
        ];



        $plugin_menus = $this->recursion_menus($req,$plugin_menus);

        $data = [
            'default_frame_name'=>'设置',
            'content'=>'',
            'default_frame_url'=>$default_frame_url,
            'plugin_menus'=>$plugin_menus,
        ];
        $data = array_merge($nav_data,$data);

        return $this->render($status,$mess,$data,'template','setting/index');
    }

    public function listsAction(RequestHelper $req,array $preData)
    {
        $status = true;
        $mess = '成功';

        $nav_data = $this->nav_default($req,$preData);

        $where =[];
        $raw = false;

        if ($req->request_method == 'POST') {
            $formget = $req->post_datas['formget'];
        } else {
            $keyword = urldecode($req->query_datas['keyword']);
            $formget['keyword'] = $keyword;
        }
        if ($formget && $formget['keyword']) {
            $where[0] = "( name like ? or name like ? )";
            $where[1] = ['%'.$formget['keyword'].'%','%'.$formget['keyword'].'%'];
            $raw = true;
        }

        $rel_model = new model\ConfigModel($this->service);
        $total = $rel_model->configCount($where,$raw);

        $path = [
            'mark' => 'sys',
            'bid'  => $req->company_id,
            'pl_name'=>'admin',
        ];

        $pageLink = urlGen($req,$path,[],true);
        $per_page = 20;
        $page = $this->page($pageLink,$total,$per_page);
        $lists = $rel_model->configLists($where,['ctime','desc'],$page->Current_page,$per_page,$raw);

        //ng_func_privilege_check($req->company_id,$this->sessions['admin_uid'],'index');

        $path = [
            'mark' => 'sys',
            'bid'  => $req->company_id,
            'pl_name'=>'admin',
        ];
        $query = [
            'mod'=>'setting',
        ];

        if ($lists) {

            foreach ($lists as $key=>$val) {
                $operater_url = array_merge($query,['act'=>'setting_edit','uid'=>$val['id']]);
                $lists[$key]['edit_url'] = urlGen($req,$path,$operater_url,true);

                $operater_url = array_merge($query,['act'=>'setting_delete','uid'=>$val['id']]);
                $lists[$key]['delete_url'] = urlGen($req,$path,$operater_url,true);

                $operater_url = array_merge($query,['act'=>'setting_info','uid'=>$val['id']]);
                $lists[$key]['info_url'] = urlGen($req,$path,$operater_url,true);

                $lists[$key]['config_count'] = 0;
                if ($val['config']) {
                    $val['config'] = ng_mysql_json_safe_decode($val['config']);
                    $lists[$key]['config_count'] = count($val['config']);
                }

            }


            $operater_url = array_merge($query,['act'=>'setting_delete']);
            $operaters_delete_action =  urlGen($req,$path,$operater_url,true);
        }

        $operater_url = array_merge($query,['act'=>'setting_add']);
        $operaters_add_action =  urlGen($req,$path,$operater_url,true);

        $pagination = $page->show('Admin');

        $data = [
            'total'=>$total,
            'lists' => $lists,
            'add_action_url'=>$operaters_add_action,
            'delete_action_url'=>$operaters_delete_action,
            'pagination' => $pagination,
            'formget'=>$formget,

        ];
        $data = array_merge($nav_data,$data);

        return $this->render($status,$mess,$data,'template','setting/lists');
    }

    public function setting_deleteAction(RequestHelper $req,array $preData)
    {
        if ($req->request_method=='POST') {
            $remove_uids = $req->post_datas['ids'];
        } else {
            $request_uid = $req->query_datas['uid'];
            $remove_uids = [$request_uid];
        }

        $flag = true;
        if (!empty($remove_uids)) {
            $rel_model = new model\ConfigModel($this->service);
            foreach ($remove_uids as $remove_id) {
                if ($remove_id==1) continue;
                $where = ['id'=>$remove_id];
                $res = $rel_model->deleteConfigInfo($where);
                $flag = $flag && $res;
            }

        }

        if ($flag) {
            $status = true;
            $mess = '成功';
            $data = [
                'info'=>$mess,
                'status' => true,
            ];
        } else {
            $status = false;
            $mess = '失败，该账号不允许删除';
            $data = [
                'info'=>$mess,
                'status' => false,
            ];
        }

        return $this->render($status,$mess,$data);
    }

    public function setting_infoAction(RequestHelper $req,array $preData)
    {
        $request_uid = $req->query_datas['uid'];
        try {
            $account_model = new model\ConfigModel($this->service);
            if ($request_uid) {
                $admin_account = $account_model->getConfigInfo(['id'=>$request_uid]);
                if (!$admin_account) {
                    throw new \Exception('配置不存在');
                }

                if ($admin_account['config']) {
                    $admin_account['config'] = ng_mysql_json_safe_decode($admin_account['config']);
                }
                $data = [
                    'info'=>$admin_account,
                ];
                $status = true;
                $mess = '成功';
                dump($admin_account['config']);
            }
        }catch (\Exception $e) {
            $error = $e->getMessage();
            $data = [
                'error'=>$error,
                'info'=>$error,
            ];
            $status = false;
            $mess = '失败';
        }

        return $this->render($status,$mess,$data,'template','empty');
    }


    public function setting_addAction(RequestHelper $req,array $preData)
    {
        try {
            //返回地址
            $path = [
                'mark' => 'sys',
                'bid'  => $req->company_id,
                'pl_name'=>'admin',
            ];
            $query = [
                'mod'=>'setting',
                'act'=>'lists'
            ];
            $cate_index_url=  urlGen($req,$path,$query,true);

            //图片上传地址
            $path = [
                'mark' => 'sys',
                'bid'  => $req->company_id,
                'pl_name'=>'admin',
            ];
            $query = [
                'mod'=>'asset',
                'act'=>'upload',
            ];
            $asset_upload_url = urlGen($req,$path,$query,true);

            $status = true;
            $mess = '成功';
            $data = [
                'op'=>'add',
                'cate_index_url'=>$cate_index_url,
                'asset_upload_url'=>$asset_upload_url,
                'cate_name'=>'设置',
            ];

            if($req->request_method == 'POST') {
                $post = $req->post_datas['post'];
                $post_c = $req->post_datas['post_c'];
                if ($post) {
                    $account_model = new model\ConfigModel($this->service);
                    $map = [];
                    if ($post['name']) {
                        $map['name'] = trim($post['name']);
                    } else {
                        throw new \Exception('名称不对。');
                    }
                    $check_account_where = [
                        'name'=>$map['name'],
                    ];
                    $exist = $account_model->getConfigInfo($check_account_where);
                    if ($exist && $post['uid']!=$exist['id']) {
                        throw new \Exception('名称已经存在');
                    }

                    $map['lock'] = $post['lock'];

                    if ($post['status']) {
                        $map['status'] = $post['status'];
                    }


                    if ($post_c) {
                        foreach ($post_c as $c_item) {
                            if ($c_item['key'] && $c_item['val']) {
                                $c_key_data = trim($c_item['key']);
                                $c_val_data = trim($c_item['val']);
                                $config_map[$c_key_data] = $c_val_data;
                            }
                        }
                        $map['config'] = ng_mysql_json_safe_encode($config_map);
                    }
                    $map['ctime'] = time();
                    $map['mtime'] = time();



                    $flag = $account_model->addConfigInfo($map);
                    if (!$flag) {
                        throw new \Exception('保存错误');
                    } else {
                        $data = [
                            'info'=>'保存成功',
                        ];
                        $status = true;
                        $mess = '成功';
                    }

                }

            }
        }catch (\Exception $e) {
            $error = $e->getMessage();
            $data = [
                'error'=>$error,
                'info'=>$error,
            ];
            $status = false;
            $mess = '失败';
        }

        if($req->request_method == 'POST') {
            //json返回
            return $this->render($status,$mess,$data);
        } else {

            return $this->render($status,$mess,$data,'template','setting/setting_edit');
        }
    }
    public function setting_editAction(RequestHelper $req,array $preData)
    {
        $request_uid = $req->query_datas['uid'];
        try {
            $account_model = new model\ConfigModel($this->service);
            if ($request_uid) {
                //返回地址
                $path = [
                    'mark' => 'sys',
                    'bid'  => $req->company_id,
                    'pl_name'=>'admin',
                ];
                $query = [
                    'mod'=>'setting',
                    'act'=>'lists'
                ];
                $cate_index_url=  urlGen($req,$path,$query,true);

                //图片上传地址
                $path = [
                    'mark' => 'sys',
                    'bid'  => $req->company_id,
                    'pl_name'=>'admin',
                ];
                $query = [
                    'mod'=>'asset',
                    'act'=>'upload',
                    'admin_uid'=>$request_uid,
                ];
                $asset_upload_url = urlGen($req,$path,$query,true);


                $admin_account = $account_model->getConfigInfo(['id'=>$request_uid]);
                if (!$admin_account) {
                    throw new \Exception('配置不存在');
                }

                if ($admin_account['config']) {
                    $admin_account['config'] = ng_mysql_json_safe_decode($admin_account['config']);
                }

                $data = [
                    'uid'=>$request_uid,
                    'admin_uid'=>$request_uid,
                    'cate_index_url'=>$cate_index_url,
                    'asset_upload_url'=>$asset_upload_url,
                    'cate_name'=>'设置',
                    'admin_account'=>$admin_account,
                ];
                $status = true;
                $mess = '成功';

                if($req->request_method == 'POST') {
                    $post = $req->post_datas['post'];
                    $post_c = $req->post_datas['post_c'];

                    if ($post) {
                        if($post['uid']!=$request_uid) {
                            throw new \Exception('用户名uid不对应。');
                        }
                        //正常的编辑
                        $map = [];
                        if ($post['name']) {
                            $map['name'] = trim($post['name']);
                        } else {
                            throw new \Exception('名称不对。');
                        }
                        $check_account_where = [
                            'name'=>$map['name'],
                        ];
                        $exist = $account_model->getConfigInfo($check_account_where);
                        if ($exist && $post['uid']!=$exist['id']) {
                            throw new \Exception('名称已经存在');
                        }

                        $map['lock'] = $post['lock'];

                        if ($post['status']) {
                            $map['status'] = $post['status'];
                        }

                        $map['mtime'] = time();

                        $save_where = [
                            'id'=> $post['uid'],
                        ];

                        if ($post_c) {
                            foreach ($post_c as $c_item) {
                                if ($c_item['key'] && $c_item['val']) {
                                    $c_key_data = trim($c_item['key']);
                                    $c_val_data = trim($c_item['val']);
                                    $config_map[$c_key_data] = $c_val_data;
                                }
                            }
                            $map['config'] = ng_mysql_json_safe_encode($config_map);
                        }
                        $flag = $account_model->saveConfigInfo($save_where,$map);
                        if (!$flag) {
                            throw new \Exception('保存错误');
                        } else {
                            $data = [
                                'info'=>'保存成功',
                            ];
                            $status = true;
                            $mess = '成功';
                        }

                    }

                }

            }


        } catch (\Exception $e) {
            $error = $e->getMessage();
            $data = [
                'error'=>$error,
                'info'=>$error,
            ];
            $status = false;
            $mess = '失败';
        }
        if($req->request_method == 'POST') {
            //json返回
            return $this->render($status,$mess,$data);
        } else {
            return $this->render($status,$mess,$data,'template','setting/setting_edit');
        }

    }



    public function menuAction(RequestHelper $req,array $preData)
    {
        $status = true;
        $mess = '成功';

        $model = new model\MenuModel($this->service);
        $where = [];
        $count = $model->menuCount($where);
        $lists = $model->menuLists($where,[['listorder','desc'],['ctime','asc']]);


        $path = [
            'mark' => 'sys',
            'bid'  => $req->company_id,
            'pl_name'=>'admin',
        ];
        $query = [
            'mod'=>'setting',
        ];


        if ($lists) {
            foreach ($lists as $key=>$val) {
                $val['status_str'] = use2Str($val['status'],['不显示','显示']);
                $val['type_str'] = use2Str($val['type'],['分组','菜单','外链']);

                $url_options = ['act'=>'menu_edit','id'=>$val['id']];
                $url_options = array_merge($url_options,$query);
                $val['edit_url'] = urlGen($req,$path,$url_options,true);

                $url_options = ['act'=>'menu_add','parentid'=>$val['id']];
                $url_options = array_merge($url_options,$query);
                $val['addsub_url'] = urlGen($req,$path,$url_options,true);

                $url_options = ['act'=>'menu_delete','id'=>$val['id']];
                $url_options = array_merge($url_options,$query);
                $val['delete_url'] = urlGen($req,$path,$url_options,true);

                $lists[$key] = $val;
            }
        }
        $tree = [];
        $parentid = 0;
        if ($lists) {
            $this->buildTree($lists,$parentid,$tree);
        }

        $url_options = ['act'=>'menu_add','parentid'=>0];
        $url_options = array_merge($url_options,$query);
        $add_action_url = urlGen($req,$path,$url_options,true);

        $url_options = ['act'=>'menu_delete'];
        $url_options = array_merge($url_options,$query);
        $delete_action_url = urlGen($req,$path,$url_options,true);

        $url_options = ['act'=>'menu_listorder'];
        $url_options = array_merge($url_options,$query);
        $listorder_action_url = urlGen($req,$path,$url_options,true);
        $data =[
            'lists'=>$tree,
            'count'=>$count,
            'add_action_url'=>$add_action_url,
            'delete_action_url'=>$delete_action_url,
            'listorder_action_url'=>$listorder_action_url,
        ];

        return $this->render($status,$mess,$data,'template','setting/menu');
    }


    public function menu_editAction(RequestHelper $req,array $preData)
    {
        $request_id = $req->query_datas['id'];
        try {
            $handle_model = new model\MenuModel($this->service);
            if ($request_id) {
                //返回地址
                $path = [
                    'mark' => 'sys',
                    'bid'  => $req->company_id,
                    'pl_name'=>'admin',
                ];
                $query = [
                    'mod'=>'setting',
                    'act'=>'menu'
                ];
                $cate_index_url=  urlGen($req,$path,$query,true);




                $res_info = $handle_model->menuInfo(['id'=>$request_id]);
                if (!$res_info) {
                    throw new \Exception('菜单不存在');
                }


                $where = [];
                $lists = $handle_model->menuLists($where,[['listorder','desc'],['ctime','asc']]);
                $tree = [];
                if ($lists) {
                    $this->buildTree($lists,0,$tree);
                }
                $options = $this->selectTree($tree,$request_id,'',true,'',true);



                $data = [
                    'id'=>$request_id,
                    'cate_index_url'=>$cate_index_url,
                    'cate_name'=>'菜单',
                    'res_info'=>$res_info,
                    'parentid'=>$res_info['parentid'],
                    'options'=>$options,
                ];
                $status = true;
                $mess = '成功';

                if($req->request_method == 'POST') {
                    $post = $req->post_datas['post'];

                    if ($post) {
                        if($post['id']!=$request_id) {
                            throw new \Exception('菜单id不对应。');
                        }
                        //正常的编辑
                        $map = [];
                        if ($post['name']) {
                            $map['name'] = trim($post['name']);
                        } else {
                            throw new \Exception('名称不对。');
                        }
                        if ($post['app']) {
                            $map['app'] = trim($post['app']);
                        } else {
                            throw new \Exception('应用不对。');
                        }
                        if ($post['model']) {
                            $map['model'] = trim($post['model']);
                        } else {
                            throw new \Exception('模型不对。');
                        }
                        if ($post['action']) {
                            $map['action'] = trim($post['action']);
                        } else {
                            throw new \Exception('行为不对。');
                        }
                        $map['parentid'] = $post['parentid'];
                        $check_info_where = [
                            'name'=>$map['name'],
                            'parentid'=>$map['parentid'],
                        ];
                        $exist = $handle_model->menuInfo($check_info_where);
                        if ($exist && $post['id']!=$exist['id']) {
                            throw new \Exception('名称已经存在');
                        }



                        if ($post['status']) {
                            $map['status'] = $post['status'];
                        }

                        if ($post['data']) {
                            $map['data'] = $post['data'];
                        }
                        if ($post['type']) {
                            $map['type'] = $post['type'];
                        }
                        if ($post['link']) {
                            $map['link'] = $post['link'];
                        }
                        if ($post['use_priv']) {
                            $map['use_priv'] = $post['use_priv'];
                        }
                        if ($post['remark']) {
                            $map['remark'] = $post['remark'];
                        }

                        $map['mtime'] = time();

                        $save_where = [
                            'id'=> $post['id'],
                        ];

                        $flag = $handle_model->saveMenu($save_where,$map);
                        if (!$flag) {
                            throw new \Exception('保存错误');
                        } else {
                            $data = [
                                'info'=>'保存成功',
                            ];
                            $status = true;
                            $mess = '成功';
                        }

                    }

                }

            }


        } catch (\Exception $e) {
            $error = $e->getMessage();
            $data = [
                'error'=>$error,
                'info'=>$error,
            ];
            $status = false;
            $mess = '失败';
        }
        if($req->request_method == 'POST') {
            //json返回
            return $this->render($status,$mess,$data);
        } else {
            return $this->render($status,$mess,$data,'template','setting/menu_edit');
        }
    }



    public function menu_addAction(RequestHelper $req,array $preData)
    {
        $request_parentid = $req->query_datas['parentid'];
        try {
            $handle_model = new model\MenuModel($this->service);
            $path = [
                'mark' => 'sys',
                'bid'  => $req->company_id,
                'pl_name'=>'admin',
            ];
            $query = [
                'mod'=>'setting',
                'act'=>'menu'
            ];
            $cate_index_url=  urlGen($req,$path,$query,true);


            $where = [];
            $lists = $handle_model->menuLists($where,[['listorder','desc'],['ctime','asc']]);
            $tree = [];
            if ($lists) {
                $this->buildTree($lists,0,$tree);
            }
            $options = $this->selectTree($tree,$request_parentid,'',true);
            $data = [
                'parentid'=>$request_parentid,
                'cate_index_url'=>$cate_index_url,
                'cate_name'=>'菜单',
                'options'=>$options,
                'op'=>'add',
            ];
            $status = true;
            $mess = '成功';

            if($req->request_method == 'POST') {
                $post = $req->post_datas['post'];

                if ($post) {
                    if($post['parentid']!=$request_parentid) {
                        throw new \Exception('菜单id不对应。');
                    }
                    //正常的编辑
                    $map = [];
                    if ($post['name']) {
                        $map['name'] = trim($post['name']);
                    } else {
                        throw new \Exception('名称不对。');
                    }
                    if ($post['app']) {
                        $map['app'] = trim($post['app']);
                    } else {
                        throw new \Exception('应用不对。');
                    }
                    if ($post['model']) {
                        $map['model'] = trim($post['model']);
                    } else {
                        throw new \Exception('模型不对。');
                    }
                    if ($post['action']) {
                        $map['action'] = trim($post['action']);
                    } else {
                        throw new \Exception('行为不对。');
                    }
                    $map['parentid'] = $post['parentid'];
                    $check_info_where = [
                        'name'=>$map['name'],
                        'parentid'=>$map['parentid'],
                    ];
                    $exist = $handle_model->menuInfo($check_info_where);
                    if ($exist && $post['uid']!=$exist['id']) {
                        throw new \Exception('名称已经存在');
                    }


                    if ($post['status']) {
                        $map['status'] = $post['status'];
                    } else {
                        $map['status'] = 1;
                    }

                    if ($post['data']) {
                        $map['data'] = $post['data'];
                    }
                    if ($post['type']) {
                        $map['type'] = $post['type'];
                    } else {
                        $map['type'] = 1;
                    }
                    if ($post['link']) {
                        $map['link'] = $post['link'];
                    }
                    if ($post['use_priv']) {
                        $map['use_priv'] = $post['use_priv'];
                    } else {
                        $map['use_priv'] = 0;
                    }
                    if ($post['remark']) {
                        $map['remark'] = $post['remark'];
                    }

                    $map['ctime'] = time();
                    $map['mtime'] = time();



                    $flag = $handle_model->addMenu($map);
                    if (!$flag) {
                        throw new \Exception('保存错误');
                    } else {
                        $data = [
                            'info'=>'保存成功',
                            'op'=>'add',
                        ];
                        $status = true;
                        $mess = '成功';
                    }

                }

            }


        } catch (\Exception $e) {
            $error = $e->getMessage();
            $data = [
                'error'=>$error,
                'info'=>$error,
                'op'=>'add',
            ];

            $status = false;
            $mess = '失败';
        }
        if($req->request_method == 'POST') {
            //json返回
            return $this->render($status,$mess,$data);
        } else {
            return $this->render($status,$mess,$data,'template','setting/menu_edit');
        }
    }

    public function menu_deleteAction(RequestHelper $req,array $preData)
    {
        if ($req->request_method=='POST') {
            $remove_uids = $req->post_datas['ids'];
        } else {
            $request_uid = $req->query_datas['id'];
            $remove_uids = [$request_uid];
        }

        $flag = true;
        if (!empty($remove_uids)) {
            $rel_model = new model\MenuModel($this->service);
            foreach ($remove_uids as $remove_id) {
                if ($remove_id==1) continue;
                $where = ['id'=>$remove_id];
                $res = $rel_model->deleteMenu($where);
                $flag = $flag && $res;
            }

        }

        if ($flag) {
            $status = true;
            $mess = '成功';
            $data = [
                'info'=>$mess,
                'status' => true,
            ];
        } else {
            $status = false;
            $mess = '失败，不允许删除';
            $data = [
                'info'=>$mess,
                'status' => false,
            ];
        }

        return $this->render($status,$mess,$data);
    }

    public function menu_listorderAction(RequestHelper $req,array $preData)
    {



        $listorders = $req->post_datas['listorders'];

        $flag = true;
        if ($listorders) {
            $handle_model = new model\MenuModel($this->service);
            foreach ($listorders as $id=>$val) {
                if ($id) {
                    $save_where = ['id'=>$id];
                    $map = ['listorder'=>$val,'mtime'=>time()];
                    $flag1 = $handle_model->saveMenu($save_where,$map);
                    $flag = $flag&&$flag1;
                }

            }
        }

        if ($flag) {
            $status = true;
            $mess = '成功';
            $data = [
                'info'=>$mess,
                'status' => true,
            ];
        } else {
            $status = false;
            $mess = '失败';
            $data = [
                'info'=>$mess,
                'status' => false,
            ];
        }

        return $this->render($status,$mess,$data);
    }

}