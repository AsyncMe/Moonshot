<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/9/10
 * Time: 下午6:28
 */

namespace libs\asyncme;

/**
 * Class NgPrivGen
 * @package libs\asyncme
 * 默认权限 (default)
 * 首页权限 (index)
 * 列表权限 (lists)
 * 增加权限 (add)
 * 增加子权限 (add_sub)
 * 查看权限 (info)
 * 修改权限 (edit)
 * 删除权限 (delete)
 * 排序权限 (orderlists)
 * 修改状态权限 (change_status)
 * 修改类型权限 (change_type)
 * 审核权限   (review)
 * 发布权限   (publish)
 * 撤回权限   (revoke)
 * 授权权限   (grant)
 * 功能授权   (func_priv_grant)
 * 数据授权   (data_priv_grant)
 * 导入权限   (import)
 * 导出权限   (export)
 */
class NgPrivGen
{
    protected function func_privs()
    {

        $privs = [
            ''=>'默认',
            'default'=>'默认',
            'index'=>'首页',
            'search'=>'搜索',
            'default'=>'默认',
            'lists'=>'列表',
            'add'=>'添加',
            'add_sub'=>'添加子',
            'info'=>'查看',
            'edit'=>'修改',
            'update'=>'更新',
            'delete'=>'删除',
            'orderlists'=>'排序',
            'change_status'=>'修改状态',
            'change_type'=>'修改类型',
            'review'=>'审核',
            'publish'=>'发布',
            'revoke'=>'撤回',
            'func_priv_grant'=>'功能授权',
            'data_priv_grant'=>'数据授权',
            'grant'=>'授权',
            'import'=>'导入',
            'export'=>'导出',
        ];
        return $privs;
    }

    public function gen_privs($mod='index',$action='index',$namespace='')
    {
        $gen_lists = [];
        $inner_privs = $this->func_privs();
        $class = ucfirst($mod);
        $class = $namespace.'\\'.$class;

        $actions = [];
        $actions[strtolower($mod)]=strtolower($mod);
        $actions[$action]=$action;

        if (class_exists($class)) {
            $ref = new \ReflectionClass($class);
            foreach($inner_privs as $priv=>$title) {

                foreach($actions as $action_item) {
                    if($priv) $method = $action_item.'_'.$priv;
                    else $method = $action_item;
                    if($ref->hasMethod($method)) {
                        $gen_lists[$method] = $title;
                    }
                }
            }

            $methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
            $method_lists = ['ask'=>[],'allow'=>[]];
            foreach ($methods as $key=>$method_item) {
                if($method_item->class ==$class) {
                    //判断是否是公用的
                    $doc = $ref->getMethod($method_item->name)->getDocComment();
                    $allow = false;
                    $name = 'unknow';
                    if($doc) {
                        if (preg_match("/@priv allow/is",$doc)) {
                            $allow = true;
                        }
                        $doc_line = explode("\n",$doc);
                        foreach($doc_line as $doc_item) {
                            $rs = [];
                            preg_match("/@name (.+)/is",$doc_item,$rs);
                            if($rs) {
                                $name = trim($rs[1]);
                            }
                        }
                    }
                    if ($allow) {
                        $method_lists['allow'][$method_item->name] = $name;
                    } else {
                        $method_lists['ask'][$method_item->name] = $name;
                    }

                }
            }
            if (!empty($method_lists)) {
                $method_lists['ask'] = array_merge($gen_lists,$method_lists['ask']);
            } else {
                $method_lists['ask'] = $gen_lists;
            }

        }

        return $method_lists;
    }
}