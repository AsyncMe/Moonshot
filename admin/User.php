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
        $page = 1;
        $account_model = new model\Account($this->service);
        $total = $account_model->adminCount($where);
        $lists = $account_model->adminLists($where,['ctime','desc'],$page);

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

            }
        }

        $data = [
            'total'=>$total,
            'lists' => $lists,
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


    public function admin_editAction(RequestHelper $req,array $preData)
    {
        $request_uid = $req->query_datas['uid'];
        if ($request_uid) {
            //图片返回地址
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

        }
        $status = true;
        $mess = '成功';
        $data = [
            'uid'=>$request_uid,
            'admin_uid'=>$request_uid,
            'cate_index_url'=>$cate_index_url,
            'asset_upload_url'=>$asset_upload_url,
            'cate_name'=>'管理员',
        ];
        return $this->render($status,$mess,$data,'template','user/admin_edit');
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