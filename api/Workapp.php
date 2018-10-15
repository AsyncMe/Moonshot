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

class Workapp extends PermissionBase
{

    /**
     * @param RequestHelper $req
     * @param array $preData
     * @return \libs\asyncme\ResponeHelper
     * @priv allow
     */
    public function indexAction(RequestHelper $req,array $preData)
    {
        $status = true;
        $mess = '成功';

        $nav_data = [];

        $data = [
            'title'=>'hello api!',
            'content'=>'version 1.0',
            'bid'=>$req->company_id,
        ];
        $data = array_merge($nav_data,$data);
        return $this->render($status,$mess,$data);
    }

    /**
     * @name 获取页面配置
     * @param RequestHelper $req
     * @param array $preData
     * @return \libs\asyncme\ResponeHelper
     * @priv allow
     */
    public function workInfo(RequestHelper $req,array $preData)
    {
        $request_work_id = $req->post_datas['work_id'];
        $request_app_sid = $req->post_datas['app_sid'];
        $request_user_id = $req->post_datas['user_id'];
        if (!$request_work_id || !$request_app_sid) {
            throw new \Exception('参数不正确');
        }
        $where = [
            'work_id'=>$request_work_id,
            'app_sid'=>$request_app_sid,
            'company_id'=>$req->company_id,
            'account_id'=>$request_user_id,
        ];

        $work_app_model = new model\WorksAppModel($this->service);
        $work_info = $work_app_model->worksAppInfo($where);
        if ($work_info) {

            $work_detail = $work_app_model->worksDatasAppInfo($where);
            if ($work_detail) {
                $work_info['detail'] = $work_detail['datas'];
            }
        }
        return $work_info;
    }

    /**
     * @name 获取页面API
     * @param RequestHelper $req
     * @param array $preData
     * @return \libs\asyncme\ResponeHelper
     * @priv ask
     */
    public function pagesAction(RequestHelper $req,array $preData)
    {
        try{
            $work_info = $this->workInfo( $req, $preData);
            $info = [];
            $info['config'] = $work_info['config'];
            $info['detail'] = $work_info['datas'];


            if ($info['config'] && $info['config']['pages']) {
                $pages = [];
                foreach ($info['config']['pages'] as $key=>$page) {
                    $page_titles = explode('/',$page);
                    $page_title = count($page_titles) ? array_pop($page_titles) : '未命名';
                    $pages[$key] = [
                        'page_id'=>substr(md5($page),8,16),
                        'title'=>$page_title,
                        'path'=>$page,
                        'snapshot'=>'',//需要从data里面获得
                    ];
                }
                $info['config']['pages'] = $pages;

            }
            $info = $info['config']['pages'];

            $status = true;
            $mess = '成功';

            $data = [
                'status'=>$status,
                'info'=>$info,
            ];
        } catch(\Exception $e) {
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
     * @name 页面布局
     * @param RequestHelper $req
     * @param array $preData
     * @return \libs\asyncme\ResponeHelper
     * @priv ask
     */
    public function pageInfoAction(RequestHelper $req,array $preData)
    {
        try{
            $request_page_id = $req->post_datas['page_id'];
            $work_info = $this->workInfo( $req, $preData);
            $info = [];
            $info['config'] = $work_info['config'];
            $info['detail'] = $work_info['datas'];


            if ($info['config'] && $info['config']['pages']) {
                $page_index = -1;
                foreach ($info['config']['pages'] as $key=>$page) {
                    $page_id = substr(md5($page),8,16);
                    if ($request_page_id == $page_id) {
                        $page_index = $key;
                        break;
                    }
                }

                $window = $info['config']['window'];

                if ($page_index>=0) {
                    $laytout = $info['config']['lists'][$page_index];
                }

            }
            $info = [];
            $info['window'] = $window;
            $info['page'] = $laytout;

            $status = true;
            $mess = '成功';

            $data = [
                'status'=>$status,
                'info'=>$info,
            ];
        } catch(\Exception $e) {
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
}