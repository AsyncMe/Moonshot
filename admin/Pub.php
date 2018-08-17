<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/24
 * Time: 下午7:09
 */

namespace admin;

use libs\asyncme\Plugins;
use admin\model;
use libs\asyncme\RequestHelper;
use Delight\Cookie\Cookie;

class Pub extends AdminBase
{
    public function IndexAction(RequestHelper $req,array $preData)
    {
        $status = true;
        $mess = '成功';
        $data = [
            'test'=>'hell admin!',
            'req'=>$req,
        ];

        return $this->render($status,$mess,$data);
    }

    public function loginAction(RequestHelper $req,array $preData)
    {

        $path = [
            'mark' => 'plugin',
            'bid'  => $req->company_id,
            'pl_name'=>'verification_code',
        ];
        $query = [
            'act'=>'gen',
            'w'=>'248',
            'h'=>50,
        ];
        $vcode_url = urlGen($req,$path,$query,true);

        $error_code = 0;
        $error = '';
        $admin_user_fail = $this->service->getSession()->get('admin_user_fail');

        if($admin_user_fail>=5) {
            $error_code = 1022;
            $error = '登陆次数过多';
        } else {

            if ($req->request_method=='POST') {

                $post_datas = $req->post_datas;
                $session_code = $this->service->getSession()->get($req->company_id.'_vcode');


                if ($post_datas['verify']!=$session_code) {
                    $error_code = 1005;
                    $error = '验证码错误';
                } else if (!$post_datas['username']) {
                    $error_code = 1001;
                    $error = '用户名不为空';
                } else if (strlen($post_datas['username'])>16) {
                    $error_code = 1002;
                    $error = '用户名格式错误';
                } else if (!$post_datas['password']) {
                    $error_code = 1003;
                    $error = '密码不为空';
                }
                $admin_account = new model\Account($this->service);
                $admin_res = $admin_account->getAdminWithName($req->company_id,$post_datas['username']);
                $flag = true;
                if ($admin_res && $admin_res['id']!=1) {
                    if ($admin_res['expire_time']) {
                        if (time()>$admin_res['expire_time']) {
                            $flag = false;
//                            $error_code = 1010;
//                            $error = '账户已过期';
                        }
                    }
                }
                if($flag && $admin_res && $admin_res['status']==1) {
                    $flag = $admin_account->checkPass($post_datas['password'],$admin_res['password'],$admin_res['slat']);
                    if (!$flag) {
                        $error_code = 1011;
                        $error = '密码错误';
                    } else {
                        //设定session
                        $cookie = new Cookie('admin_user');
                        $cookie->setValue($admin_res['account']);
                        $cookie->setMaxAge(60 * 60 * 24);
                        $cookie->save();

                        $session = $this->service->getSession();
                        $session->set('admin_uid',$admin_res['id']);
                        $session->set('admin_user',$admin_res['account']);
                        $session->set('admin_name',$admin_res['nickname']);
                        $session->set('admin_avatar',$admin_res['avatar']);
                        $session->set('admin_login_time',time());

                        $logInfo = [
                            'ip'=>getIP(),
                            'user_id'=>$admin_res['id'],
                            'account'=>$admin_res['account'],
                            'nickname'=>$admin_res['nickname'],
                            'mess'=>'登陆成功',
                            'flag'=>true,
                        ];

                        $this->service->getSession()->set('admin_user_fail',0);
                        $admin_account->sysLog($req->company_id,$logInfo,'pub/login');

                        $bid = $req->company_id;
                        $path = [
                            'mark' => 'sys',
                            'bid'  => $bid,
                            'pl_name'=>'admin',
                        ];
                        $query = [
                            'mod'=>'index',
                            'act'=>'index'
                        ];
                        $sys_url = urlGen($req,$path,$query);

                        $this->redirect($sys_url);

                    }
                } else {
                    $error_code = 1010;
                    $error = '管理用户不存在';
                }

            }

            //
            if($error_code>0 && $error_code!=1005) {
                //验证码错误不登记
                if ($post_datas['username']) {
                    $logInfo = [
                        'ip'=>getIP(),
                        'account'=>$post_datas['username'],
                        'password'=>$post_datas['password'],
                        'mess'=>'登陆失败',
                        'flag'=>false,
                    ];
                    $admin_account->sysLog($req->company_id,$logInfo,'pub/login');
                }

                $this->service->getSession()->set('admin_user_fail',$admin_user_fail+1);
            }
        }



        $status = true;
        $mess = '成功';
        $cookie_exist = Cookie::exists('admin_user');
        if ($cookie_exist){
            $cookie_val = Cookie::get('admin_user');
        }

        $data = [
            'default_user'=>$cookie_val,
            'form_url' =>'#',
            'vcode_url'=>$vcode_url,
            'error_code'=>$error_code,
            'admin_user_fail'=>$admin_user_fail,
            'allow_try'=>5,
            'error'=>$error,
        ];

        return $this->render($status,$mess,$data,'template','login');
    }

    public function logoutAction(RequestHelper $req,array $preData)
    {

        $session = $this->service->getSession();
        $session->delete('admin_uid');
        $session->delete('admin_user');
        $session->delete('admin_name');
        $session->delete('admin_avatar');
        $session->delete('admin_login_time');

        $bid = $req->company_id;
        $path = [
            'mark' => 'sys',
            'bid'  => $bid,
            'pl_name'=>'admin',
        ];
        $query = [
            'mod'=>'pub',
            'act'=>'login'
        ];
        $sys_url = urlGen($req,$path,$query);

        $this->redirect($sys_url);

    }


}