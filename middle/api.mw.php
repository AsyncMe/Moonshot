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
$container['api_view'] = function ($container) {
    $view = new \Slim\Views\Twig('../api/templates', [
        //'cache' => 'data/cache/template'
    ]);
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
    $view->addExtension(new \libs\asyncme\NgTwigExtension($container->get('router'), $basePath));
    //$view->addExtension(new Slim\Views\TwigExtension($container->get('router'), $basePath));

    return $view;
};
