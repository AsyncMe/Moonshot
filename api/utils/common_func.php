<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/8/2
 * Time: 下午11:41
 */
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
/**
 * 检查是否具备功能权限
 * @param $bid
 * @param $admin_uid
 * @param $op
 * @return bool
 */
function man_func_privilege_check($bid,$admin_uid,$op)
{

    return true;

}