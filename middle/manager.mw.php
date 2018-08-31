<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/10
 * Time: 上午2:23
 */

if(!defined('NG_ME')) die();

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;



//视图中间件
$container['manager_view'] = function ($container) {
    $view = new \Slim\Views\Twig('../manager/templates', [
        //'cache' => 'data/cache/template'
    ]);
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
    $view->addExtension(new \libs\asyncme\NgTwigExtension($container->get('router'), $basePath));
    //$view->addExtension(new Slim\Views\TwigExtension($container->get('router'), $basePath));

    return $view;
};

//验证管理用户
$vaild_manager_mw = function( $request, $response, $next ) {
    $route = $request->getAttribute('route');
    $bid = $route->getArgument('bid');
    $bid_len = strlen($bid);
    $is_vaild = false;
    $mess = '管理账号不存在';
    if( $bid_len > 2 && $bid_len<=16 ) {
        $cachePath = "./data/cache/mw";
        if(!is_dir($cachePath.'/')) {
            mkdir($cachePath.'/',0755,true);
        }
        $cache_file = 'manager_'.md5($bid).".cache.php";
        if (file_exists($cachePath.'/'.$cache_file) && time()-filemtime($cachePath.'/'.$cache_file) < 5*60) {
            $cache = include $cachePath.'/'.$cache_file;
            if ($cache['status']==1 && $cache['group_id']==$bid) {
                $is_vaild = true;
                $response = $next($request, $response);
            }
        } else {
            $company_info = $this->db->table("sys_company_account")->select('id','account','group_id','status')->where('group_id','=',$bid)->first();
            if ( $company_info && $company_info->status == 1) {
                ob_start();
                $cache_data = (array)$company_info;
                var_export($cache_data);
                $cache_str = "<?php\n if(!defined('NG_ME')) die();\nreturn ".ob_get_contents().";\n";
                ob_end_clean();
                @file_put_contents($cachePath.'/'.$cache_file,$cache_str);
                $is_vaild = true;
                $response = $next($request, $response);
            } else {
                $mess = '账号不存在或者账号被锁定';
            }
        }



    }
    if(!$is_vaild) {
        $tag = $request->getQueryParam('tag');
        $tag = $tag ? $tag : 0;
        $json_data = ['status'=>false,'mess'=>$mess,'tag'=>$tag];
        return $response->withJson($json_data);
    }
    return $response;
};