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

class Index extends PermissionBase
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


}