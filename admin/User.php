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

class User extends PermissionBase
{
    public function indexAction(RequestHelper $req,array $preData)
    {
        $status = true;
        $mess = '成功';

        $nav_data = $this->nav_default($req,$preData);


        //ng_func_privilege_check($req->company_id,$this->sessions['admin_uid'],'index');

        $data = [
            'title'=>'hello admin!',
            'content'=>'',
        ];
        $data = array_merge($nav_data,$data);

        return $this->render($status,$mess,$data,'template','user/index');
    }

    public function adminAction(RequestHelper $req,array $preData)
    {
        $status = true;
        $mess = '成功';

        $nav_data = $this->nav_default($req,$preData);


        $where =[];
        $account_model = new model\Account($this->service);
        $total = $account_model->adminCount($where);

        $path = [
            'mark' => 'sys',
            'bid'  => $req->company_id,
            'pl_name'=>'admin',
        ];
        $pageLink = urlGen($req,$path,[],true);
        $page = $this->page($pageLink,$total,20);
        $lists = $account_model->adminLists($where,['ctime','desc'],$page->Current_page);

        //ng_func_privilege_check($req->company_id,$this->sessions['admin_uid'],'index');

        if ($lists) {
            $path = [
                'mark' => 'sys',
                'bid'  => $req->company_id,
                'pl_name'=>'admin',
            ];
            $query = [
                'mod'=>'user',
            ];
            foreach ($lists as $key=>$val) {
                $operater_url = array_merge($query,['act'=>'admin_edit','uid'=>$val['id']]);
                $lists[$key]['edit_url'] = urlGen($req,$path,$operater_url,true);

                $operater_url = array_merge($query,['act'=>'admin_delete','uid'=>$val['id']]);
                $lists[$key]['delete_url'] = urlGen($req,$path,$operater_url,true);


                if ($lists[$key]['avatar'] != 'default') {
                    $cdn_prefix = $this->getCdnHost();
                    $lists[$key]['avatar'] = $cdn_prefix.'/'.$lists[$key]['avatar'];
                }

            }

            $operater_url = array_merge($query,['act'=>'admin_add']);
            $operaters_add_action =  urlGen($req,$path,$operater_url,true);
            $operater_url = array_merge($query,['act'=>'admin_delete']);
            $operaters_delete_action =  urlGen($req,$path,$operater_url,true);
        }

        $pagination = $page->show('Admin');

        $data = [
            'total'=>$total,
            'lists' => $lists,
            'add_action_url'=>$operaters_add_action,
            'delete_action_url'=>$operaters_delete_action,
            'pagination' => $pagination,

        ];
        $data = array_merge($nav_data,$data);

        return $this->render($status,$mess,$data,'template','user/admin');
    }

    public function admin_deleteAction(RequestHelper $req,array $preData)
    {
        $data = [

        ];
        $request_uid = $req->query_datas['uid'];
        if ($request_uid > 1) {
            $status = true;
            $mess = '成功';

        } else {
            $status = false;
            $mess = '失败，该账号不允许删除';
        }

        return $this->render($status,$mess,$data);
    }


    public function admin_addAction(RequestHelper $req,array $preData)
    {
        try {
            //返回地址
            $path = [
                'mark' => 'sys',
                'bid'  => $req->company_id,
                'pl_name'=>'admin',
            ];
            $query = [
                'mod'=>'user',
                'act'=>'admin'
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
                'cate_name'=>'管理员',
                'op'=>'add',
                'cate_index_url'=>$cate_index_url,
                'asset_upload_url'=>$asset_upload_url,
                'cate_name'=>'管理员',
            ];

            if($req->request_method == 'POST') {
                $post = $req->post_datas['post'];

                if ($post) {
                    $account_model = new model\Account($this->service);
                    //正常的编辑
                    $map = [];
                    if ($post['account'] && preg_match('/\w{5,16}/is',$post['account'])) {
                        $map['account'] = $post['account'];
                    } else {
                        throw new \Exception('账号不对。');
                    }
                    $check_account_where = [
                        'account'=>$map['account'],
                    ];
                    $exist = $account_model->getAdminAccount($check_account_where);
                    if ($exist) {
                        throw new \Exception('账号已经存在');
                    }

                    if ($post['company_id'] && preg_match('/[1-9]\d{2,15}/is',$post['company_id'])) {
                        $map['company_id'] = $post['company_id'];
                    } else {
                        throw new \Exception('域id格式不对。');
                    }

                    if ($post['nickname'] && (mb_strlen($post['nickname'],'UTF-8')>=3 && mb_strlen($post['nickname'],'UTF-8')<=10)) {
                        $map['nickname'] = $post['nickname'];
                    } else {
                        throw new \Exception('昵称不对。');
                    }

                    //密码
                    if (!$post['newpassword'] || !$post['comfirm_password']) {
                        throw new \Exception('密码必须填。');
                    } else if($post['newpassword']!=$post['comfirm_password']) {
                        throw new \Exception('错认密码错误。');
                    } else {
                        $slat = substr(getRandomStr(),0,6);
                        $map['password'] = md5($post['newpassword'].$slat);
                        $map['slat'] =  $slat;
                    }

                    $map['status'] = $post['status'];
                    if ($post['expire_time']) {
                        $map['expire_time'] = $post['expire_time'];
                    } else {
                        $map['expire_time'] = 0;
                    }
                    $map['avatar'] = $post['avatar'];
                    $map['ctime'] = time();
                    $map['mtime'] = time();



                    $flag = $account_model->addAdminAccount($map);
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

            return $this->render($status,$mess,$data,'template','user/admin_edit');
        }
    }
    public function admin_editAction(RequestHelper $req,array $preData)
    {
        $request_uid = $req->query_datas['uid'];
        try {
            $account_model = new model\Account($this->service);
            if ($request_uid) {
                //返回地址
                $path = [
                    'mark' => 'sys',
                    'bid'  => $req->company_id,
                    'pl_name'=>'admin',
                ];
                $query = [
                    'mod'=>'user',
                    'act'=>'admin'
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


                $admin_account = $account_model->getAdminAccount(['id'=>$request_uid]);
                if (!$admin_account) {
                    throw new \Exception('账号不存在');
                }

                if (!$admin_account['expire_time']) {
                    $admin_account['expire_time'] = '';
                }

                $data = [
                    'uid'=>$request_uid,
                    'admin_uid'=>$request_uid,
                    'cate_index_url'=>$cate_index_url,
                    'asset_upload_url'=>$asset_upload_url,
                    'cate_name'=>'管理员',
                    'admin_account'=>$admin_account,
                ];
                $status = true;
                $mess = '成功';

                if($req->request_method == 'POST') {
                    $post = $req->post_datas['post'];

                    if ($post) {
                        if($post['uid']!=$request_uid) {
                            throw new \Exception('用户名uid不对应。');
                        }
                        //正常的编辑
                        $map = [];
                        if ($post['account'] && preg_match('/\w{5,16}/is',$post['account'])) {
                            $map['account'] = $post['account'];
                        } else {
                            throw new \Exception('账号不对。');
                        }
                        $check_account_where = [
                            'account'=>$map['account'],
                        ];
                        $exist = $account_model->getAdminAccount($check_account_where);
                        if ($exist && $post['uid']!=$exist['id']) {
                            throw new \Exception('账号已经存在');
                        }

                        if ($post['company_id'] && preg_match('/[1-9]\d{2,15}/is',$post['company_id'])) {
                            $map['company_id'] = $post['company_id'];
                        } else {
                            throw new \Exception('域id格式不对。');
                        }

                        if ($post['nickname'] && (mb_strlen($post['nickname'],'UTF-8')>=3 && mb_strlen($post['nickname'],'UTF-8')<=10)) {
                            $map['nickname'] = $post['nickname'];
                        } else {
                            throw new \Exception('昵称不对。');
                        }

                        //密码
                        if (!$post['password'] && ($post['newpassword'] || $post['comfirm_password'])) {
                            throw new \Exception('原始密码必须填。');
                        } else if($post['password']) {
                            if($account_model->checkPass($post['password'],$admin_account['password'],$admin_account['slat'])) {

                                if($post['newpassword']!=$post['comfirm_password']) {
                                    throw new \Exception('错认密码错误。');
                                } else {
                                    $slat = substr(getRandomStr(),0,6);
                                    $map['password'] = md5($post['newpassword'].$slat);
                                    $map['slat'] =  $slat;
                                }

                            } else {
                                throw new \Exception('原始密码错误。');
                            }
                        }

                        if ($post['status']) {
                            $map['status'] = $post['status'];
                        }

                        if ($post['expire_time']) {
                            $map['expire_time'] = $post['expire_time'];
                        }
                        $map['avatar'] = $post['avatar'];
                        $map['mtime'] = time();

                        $save_where = [
                            'id'=> $post['uid'],
                        ];
                        $flag = $account_model->saveAdminAccount($save_where,$map);
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
            return $this->render($status,$mess,$data,'template','user/admin_edit');
        }

    }

    public function companyAction(RequestHelper $req,array $preData)
    {
        $status = true;
        $mess = '成功';

        $nav_data = $this->nav_default($req,$preData);


        //ng_func_privilege_check($req->company_id,$this->sessions['admin_uid'],'index');

        $data = [
            'title'=>'hello admin!',
            'content'=>'',
        ];
        $data = array_merge($nav_data,$data);

        return $this->render($status,$mess,$data,'template','user/company');
    }



}