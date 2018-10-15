<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/28
 * Time: 上午1:20
 */

namespace api;

use libs\asyncme\Plugins as Plugins;
use libs\asyncme\RequestHelper as RequestHelper;
use libs\asyncme\ResponeHelper as ResponeHelper;
use manager\model\ManageMenuModel;
use \Slim\Http\UploadedFile;

class PermissionBase extends ApiBase
{
    public $sessions;

    public function auth()
    {
        $req = $this->service->getRequestHelper();
        $bid = $req->company_id;

        $path = [
            'mark' => 'manager',
            'bid'  => $bid,
            'pl_name'=>'manager',
        ];
        $query = [
            'mod'=>'pub',
            'act'=>'login'
        ];

        $url = urlGen($req,$path,$query);
        $time = 0;
        $mess = '';
        $error_code = -1;

        $session = $this->service->getSession();

        $sessions['manager_uid'] = $session->get('manager_uid');
        $sessions['manager_user'] = $session->get('manager_user');
        $sessions['manager_name'] = $session->get('manager_name');
        $sessions['manager_avatar'] = $session->get('manager_avatar');
        $sessions['manager_login_time'] = $session->get('manager_login_time');
        $sessions['manager_site_title_prefix'] = $session->get('manager_site_title_prefix');
        $sessions['manager_site_version'] = $session->get('manager_site_version');
        $sessions['token'] = $session->get('token');

        $this->sessions = $sessions;

        $status = $session->get('manager_user') ? true : false;

        $headers = $req->getHeader();

        if (!$headers['HTTP_NGTOKEN'][0] || $headers['HTTP_NGTOKEN'][0]!=$session->get('token'))  {
            $status = false;
            $mess = '没有权限';
            $error_code = "-2";
        }

        return [
            'status'=>$status,
            'mess'=>$mess,
            'error_code'=>$error_code,
            'url'=>$url,
            'time'=>$time,
        ];

    }
}