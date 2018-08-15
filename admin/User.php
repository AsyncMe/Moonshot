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
            foreach ($lists as $key=>$val) {

            }
        }

        $data = [
            'total'=>$total,
            'lists' => $lists,
        ];
        $data = array_merge($nav_data,$data);

        return $this->render($status,$mess,$data,'template','user/admin');
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