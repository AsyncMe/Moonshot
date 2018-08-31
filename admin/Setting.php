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
use libs\asyncme\ResponeHelper as ResponeHelper;
use PHPSQLParser\PHPSQLParser;
use PHPSQLParser\utils\PHPSQLParserConstants;
use libs\asyncme\Page as Page;
use \Slim\Http\UploadedFile;

class Setting extends PermissionBase
{
    public function indexAction(RequestHelper $req,array $preData)
    {
        $status = true;
        $mess = '成功';

        $nav_data = $this->nav_default($req,$preData);

        //ng_func_privilege_check($req->company_id,$this->sessions['admin_uid'],'index');

        $data = [
            'default_frame_name'=>'设置',
            'content'=>'',
        ];
        $data = array_merge($nav_data,$data);

        return $this->render($status,$mess,$data,'template','setting/index');
    }

    public function settingAction(RequestHelper $req,array $preData)
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

        $operater_url = array_merge($query,['act'=>'setting_export']);
        $operaters_export_action =  urlGen($req,$path,$operater_url,true);

        $operater_url = array_merge($query,['act'=>'setting_import']);
        $operaters_import_action =  urlGen($req,$path,$operater_url,true);

        $pagination = $page->show('Admin');

        $data = [
            'total'=>$total,
            'lists' => $lists,
            'add_action_url'=>$operaters_add_action,
            'delete_action_url'=>$operaters_delete_action,
            'export_action_url'=>$operaters_export_action,
            'import_action_url'=>$operaters_import_action,
            'pagination' => $pagination,
            'formget'=>$formget,

        ];
        $data = array_merge($nav_data,$data);

        return $this->render($status,$mess,$data,'template','setting/setting');
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
                $where = ['id'=>$remove_id,'lock'=>0];
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
            $rel_model = new model\ConfigModel($this->service);
            if ($request_uid) {
                $rel_info = $rel_model->getConfigInfo(['id'=>$request_uid]);
                if (!$rel_info) {
                    throw new \Exception('配置不存在');
                }

                if ($rel_info['config']) {
                    $rel_info['config'] = ng_mysql_json_safe_decode($rel_info['config']);
                }
                $data = [
                    'info'=>$rel_info,
                ];
                $status = true;
                $mess = '成功';
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

        return $this->render($status,$mess,$data,'template','setting/setting_info');
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
                'act'=>'setting'
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
                    'act'=>'setting'
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

    public function setting_exportAction(RequestHelper $req,array $preData)
    {
        $rel_model = new model\ConfigModel($this->service);
        $total = $rel_model->configCount();
        if ($total) {
            $data = [];
            $where =[];
            $per_page = 100;
            $page = $this->page('',$total,$per_page);
            $total_page = $page->getTotalPages();
            $current_page = $page->Current_page;
            while($current_page <= $total_page) {

                $lists = $rel_model->configLists($where,['ctime','desc'],$current_page,$per_page);
                if ($lists) {
                    foreach ($lists as $key=>$val) {
                        if (is_array($val)) {
                            foreach ($val as $k=>$v) {
                                if($k=='id') continue;
                                $value = stripslashes($v);
                                $value = iconv('utf-8','gb2312',$value);
                                $value = str_replace(',','#;#',$value);
                                $data[$key][$k] = $value;
                            }
                        }
                    }
                }
                $current_page++;
            }

        }


        $status = true;
        $mess = '成功';

        $string = '';
        foreach ($data as $key => $value)
        {
            $string .= implode(",",$value)."\n"; //用英文逗号分开
        }
        unset($data);
        $respone = new ResponeHelper($status,$mess,$string,'file','','');
        $respone->export_file_name = 'setting_config_'.date('YmdHis').'.csv';
        $respone->export_file_type = 'text/csv';
        return $respone;
    }

    public function setting_importAction(RequestHelper $req,array $preData)
    {
        $rel_model = new model\ConfigModel($this->service);
        foreach ( $req->upload_files as $file) {
            $error = $file->getError();
            if ($error === UPLOAD_ERR_OK) {

                $filetype = $file->getClientMediaType();
                if (strtolower($filetype) == 'text/csv') {
                    $excelData = file($file->file);
                    $chunkData = array_chunk($excelData, 5000);

                    $count = count($chunkData);
                    for ($i = 0; $i < $count; $i++) {
                        foreach ($chunkData[$i] as $value) {
                            $string = mb_convert_encoding(trim(strip_tags($value)), 'utf-8', 'gb2312');
                            $v = explode(',', trim($string));
                            $map = [];
                            $map['name'] = $v[0];
                            $map['config'] = addslashes(str_replace('#;#',',',$v[1]));
                            $map['lock'] = $v[2];
                            $map['ctime'] = $v[3];
                            $map['mtime'] = $v[4];
                            $exit = $rel_model->getConfigInfo(['name'=>$map['name']]);
                            if (!$exit) {
                                $flag = $rel_model->addConfigInfo($map);
                            }

                        }
                    }
                }

            } else {
                $flag = false;
                break;
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
    /**
     * 管理菜单
     */

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

        $operater_url = array_merge($query,['act'=>'menu_export']);
        $operaters_export_action =  urlGen($req,$path,$operater_url,true);

        $operater_url = array_merge($query,['act'=>'menu_import']);
        $operaters_import_action =  urlGen($req,$path,$operater_url,true);

        $data =[
            'lists'=>$tree,
            'count'=>$count,
            'add_action_url'=>$add_action_url,
            'delete_action_url'=>$delete_action_url,
            'listorder_action_url'=>$listorder_action_url,
            'export_action_url'=>$operaters_export_action,
            'import_action_url'=>$operaters_import_action,
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
                    'cate_name'=>'管理菜单',
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
                'cate_name'=>'管理菜单',
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

    public function menu_exportAction(RequestHelper $req,array $preData)
    {
        $rel_model = new model\MenuModel($this->service);
        $total = $rel_model->menuCount();
        if ($total) {
            $data = [];
            $where =[];
            $per_page = 100;
            $page = $this->page('',$total,$per_page);
            $total_page = $page->getTotalPages();
            $current_page = $page->Current_page;
            while($current_page <= $total_page) {

                $lists = $rel_model->menuLists($where,['ctime','desc'],$current_page,$per_page);
                if ($lists) {
                    foreach ($lists as $key=>$val) {
                        if (is_array($val)) {
                            foreach ($val as $k=>$v) {
                                $value = stripslashes($v);
                                $value = iconv('utf-8','gb2312',$value);
                                $value = str_replace(',','#;#',$value);
                                $data[$key][$k] = $value;
                            }
                        }
                    }
                }
                $current_page++;
            }

        }


        $status = true;
        $mess = '成功';

        $string = '';
        foreach ($data as $key => $value)
        {
            $string .= implode(",",$value)."\n"; //用英文逗号分开
        }
        unset($data);
        $respone = new ResponeHelper($status,$mess,$string,'file','','');
        $respone->export_file_name = 'menu_'.date('YmdHis').'.csv';
        $respone->export_file_type = 'text/csv';
        return $respone;
    }

    public function menu_importAction(RequestHelper $req,array $preData)
    {
        $rel_model = new model\MenuModel($this->service);
        foreach ( $req->upload_files as $file) {
            $error = $file->getError();
            if ($error === UPLOAD_ERR_OK) {

                $filetype = $file->getClientMediaType();
                if (strtolower($filetype) == 'text/csv') {
                    $excelData = file($file->file);
                    $chunkData = array_chunk($excelData, 5000);

                    $count = count($chunkData);
                    for ($i = 0; $i < $count; $i++) {
                        foreach ($chunkData[$i] as $value) {
                            $string = mb_convert_encoding(trim(strip_tags($value)), 'utf-8', 'gb2312');
                            $v = explode(',', trim($string));
                            $map = [];
                            $map['id'] = $v[0];
                            $map['parentid'] = $v[1];
                            $map['app'] = $v[2];
                            $map['model'] = $v[3];
                            $map['action'] = $v[4];
                            $map['data'] = $v[5];

                            $map['category'] = $v[6];
                            $map['placehold'] = $v[7];
                            $map['use_priv'] = $v[8];
                            $map['type'] = $v[9];
                            $map['link'] = $v[10];

                            $map['status'] = $v[11];
                            $map['name'] = addslashes(str_replace('#;#',',',$v[12]));
                            $map['icon'] = $v[13];
                            $map['remark'] = $v[14];
                            $map['listorder'] = $v[15];

                            $map['ctime'] = $v[16];
                            $map['mtime'] = $v[17];
                            $exit = $rel_model->menuInfo(['id'=>$map['id']]);
                            if (!$exit) {
                                $flag = $rel_model->addMenu($map);
                            }

                        }
                    }
                }

            } else {
                $flag = false;
                break;
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
    /**
     * 运营管理菜单
     */
    public function manage_menuAction(RequestHelper $req,array $preData)
    {
        $status = true;
        $mess = '成功';

        $model = new model\ManageMenuModel($this->service);
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

                $url_options = ['act'=>'manage_menu_edit','id'=>$val['id']];
                $url_options = array_merge($url_options,$query);
                $val['edit_url'] = urlGen($req,$path,$url_options,true);

                $url_options = ['act'=>'manage_menu_add','parentid'=>$val['id']];
                $url_options = array_merge($url_options,$query);
                $val['addsub_url'] = urlGen($req,$path,$url_options,true);

                $url_options = ['act'=>'manage_menu_delete','id'=>$val['id']];
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

        $url_options = ['act'=>'manage_menu_add','parentid'=>0];
        $url_options = array_merge($url_options,$query);
        $add_action_url = urlGen($req,$path,$url_options,true);

        $url_options = ['act'=>'manage_menu_delete'];
        $url_options = array_merge($url_options,$query);
        $delete_action_url = urlGen($req,$path,$url_options,true);

        $url_options = ['act'=>'manage_menu_listorder'];
        $url_options = array_merge($url_options,$query);
        $listorder_action_url = urlGen($req,$path,$url_options,true);

        $operater_url = array_merge($query,['act'=>'manage_menu_export']);
        $operaters_export_action =  urlGen($req,$path,$operater_url,true);

        $operater_url = array_merge($query,['act'=>'manage_menu_import']);
        $operaters_import_action =  urlGen($req,$path,$operater_url,true);

        $data =[
            'lists'=>$tree,
            'count'=>$count,
            'add_action_url'=>$add_action_url,
            'delete_action_url'=>$delete_action_url,
            'listorder_action_url'=>$listorder_action_url,
            'export_action_url'=>$operaters_export_action,
            'import_action_url'=>$operaters_import_action,
        ];

        return $this->render($status,$mess,$data,'template','setting/manage_menu');
    }

    public function manage_menu_editAction(RequestHelper $req,array $preData)
    {
        $request_id = $req->query_datas['id'];
        try {
            $handle_model = new model\ManageMenuModel($this->service);
            if ($request_id) {
                //返回地址
                $path = [
                    'mark' => 'sys',
                    'bid'  => $req->company_id,
                    'pl_name'=>'admin',
                ];
                $query = [
                    'mod'=>'setting',
                    'act'=>'manage_menu'
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
                    'cate_name'=>'运营菜单',
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
            return $this->render($status,$mess,$data,'template','setting/manage_menu_edit');
        }
    }

    public function manage_menu_addAction(RequestHelper $req,array $preData)
    {
        $request_parentid = $req->query_datas['parentid'];
        try {
            $handle_model = new model\ManageMenuModel($this->service);
            $path = [
                'mark' => 'sys',
                'bid'  => $req->company_id,
                'pl_name'=>'admin',
            ];
            $query = [
                'mod'=>'setting',
                'act'=>'manage_menu'
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
                'cate_name'=>'运营菜单',
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
            return $this->render($status,$mess,$data,'template','setting/manage_menu_edit');
        }
    }

    public function manage_menu_deleteAction(RequestHelper $req,array $preData)
    {
        if ($req->request_method=='POST') {
            $remove_uids = $req->post_datas['ids'];
        } else {
            $request_uid = $req->query_datas['id'];
            $remove_uids = [$request_uid];
        }

        $flag = true;
        if (!empty($remove_uids)) {
            $rel_model = new model\ManageMenuModel($this->service);
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

    public function manage_menu_listorderAction(RequestHelper $req,array $preData)
    {

        $listorders = $req->post_datas['listorders'];

        $flag = true;
        if ($listorders) {
            $handle_model = new model\ManageMenuModel($this->service);
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

    public function manage_menu_exportAction(RequestHelper $req,array $preData)
    {
        $rel_model = new model\ManageMenuModel($this->service);
        $total = $rel_model->menuCount();
        if ($total) {
            $data = [];
            $where =[];
            $per_page = 100;
            $page = $this->page('',$total,$per_page);
            $total_page = $page->getTotalPages();
            $current_page = $page->Current_page;
            while($current_page <= $total_page) {

                $lists = $rel_model->menuLists($where,['ctime','desc'],$current_page,$per_page);
                if ($lists) {
                    foreach ($lists as $key=>$val) {
                        if (is_array($val)) {
                            foreach ($val as $k=>$v) {
                                $value = stripslashes($v);
                                $value = iconv('utf-8','gb2312',$value);
                                $value = str_replace(',','#;#',$value);
                                $data[$key][$k] = $value;
                            }
                        }
                    }
                }
                $current_page++;
            }

        }


        $status = true;
        $mess = '成功';

        $string = '';
        foreach ($data as $key => $value)
        {
            $string .= implode(",",$value)."\n"; //用英文逗号分开
        }
        unset($data);
        $respone = new ResponeHelper($status,$mess,$string,'file','','');
        $respone->export_file_name = 'manage_menu_'.date('YmdHis').'.csv';
        $respone->export_file_type = 'text/csv';
        return $respone;
    }

    public function manage_menu_importAction(RequestHelper $req,array $preData)
    {
        $rel_model = new model\ManageMenuModel($this->service);
        foreach ( $req->upload_files as $file) {
            $error = $file->getError();
            if ($error === UPLOAD_ERR_OK) {

                $filetype = $file->getClientMediaType();
                if (strtolower($filetype) == 'text/csv') {
                    $excelData = file($file->file);
                    $chunkData = array_chunk($excelData, 5000);

                    $count = count($chunkData);
                    for ($i = 0; $i < $count; $i++) {
                        foreach ($chunkData[$i] as $value) {
                            $string = mb_convert_encoding(trim(strip_tags($value)), 'utf-8', 'gb2312');
                            $v = explode(',', trim($string));
                            $map = [];
                            $map['id'] = $v[0];
                            $map['parentid'] = $v[1];
                            $map['app'] = $v[2];
                            $map['model'] = $v[3];
                            $map['action'] = $v[4];
                            $map['data'] = $v[5];

                            $map['category'] = $v[6];
                            $map['placehold'] = $v[7];
                            $map['use_priv'] = $v[8];
                            $map['type'] = $v[9];
                            $map['link'] = $v[10];

                            $map['status'] = $v[11];
                            $map['name'] = addslashes(str_replace('#;#',',',$v[12]));
                            $map['icon'] = $v[13];
                            $map['remark'] = $v[14];
                            $map['listorder'] = $v[15];

                            $map['ctime'] = $v[16];
                            $map['mtime'] = $v[17];
                            $exit = $rel_model->menuInfo(['id'=>$map['id']]);
                            if (!$exit) {
                                $flag = $rel_model->addMenu($map);
                            }

                        }
                    }
                }

            } else {
                $flag = false;
                break;
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

    /**
     * 缓存
     */
    public function clean_cacheAction(RequestHelper $req,array $preData)
    {
        $cache_key = 'global_view_val';
        $cache_rel = $this->service->getCache();
        $cache_rel->delete($cache_key);

        $status = true;
        $mess = '成功';
        $data =[
            'mess_title'=>'消息提示',
            'success'=>'缓存清除成功'
        ];
        return $this->render($status,$mess,$data,'template','success_box');
    }
}