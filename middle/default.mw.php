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

class NGRedis {
    public static $instance;

    private $config;

    private $fail_time;

    private $redis_obj;

    public function __construct($config)
    {
        $this->config = $config;
        $this->fail_time = 0;
        $redis = new \Redis();
        $this->redis_obj = $redis;
        $this->init();
    }

    private function init()
    {
        $config = $this->config;
        $redis = $this->redis_obj;
        $redis->connect($config['host'],$config['port']);
        $redis->auth($config['pass']);
        $redis->select($config['dbnum']);
        $redis->set('db_name',$config['db_mark']);
    }


    public function setAsGlobal()
    {
        static::$instance = $this;
    }

    public function getRedis()
    {
        if ($this->redis_obj) {
            if ($this->fail_time<10) {
                $response = $this->redis_obj->ping();
                if ($response != '+PONG' ) {
                    $this->fail_time++;
                    $this->init();
                }
            } else {
                throw new \Exception('redis lost connect');
            }
            return $this->redis_obj;
        } else {
           return null;
        }

    }
}
//日志中间件
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};
//数据库的中间件
$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $capsule = new Capsule;
    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => $db['host'],
        'database'  => $db['dbname'],
        'username'  => $db['user'],
        'password'  => $db['pass'],
        'charset'   => $db['charset'],
        'collation' => $db['collation'],
        'prefix'    => $db['prefix']
    ]);

    $capsule->setEventDispatcher(new Dispatcher(new Container));
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    return $capsule;
};
//redis中间件
$container['redis'] = function ($c) {
    $config = $c['settings']['redis'];
    $ngRedis = new NGRedis($config);
    $ngRedis->setAsGlobal();
    return $ngRedis->getRedis();
};
//文件缓存
$container['filecache'] = function($c) {
    $file_cache = new \libs\asyncme\NgFileCache($c['settings']['filecache']['path']);
    $file_cache->setAsGlobal();
    return $file_cache;
};

//session处理
$container['session'] = function ($c) {
    return new \SlimSession\Helper;
};


//cos中间件
$container['cosClient'] = function ($c) {
    $config = $c['settings']['cosClient'];
    $cosClient = new \Qcloud\Cos\Client(array('region' => 'ap-guangzhou',
        'credentials'=> array(
            'appId' => $config['appid'],
            'secretId'    => $config['secret_id'],
            'secretKey' => $config['secret_key'])));
    return $cosClient;
};



//通用头部中间件
$common_header_mw = function ($request, $response, $next ) {
    $response = $next($request, $response);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Content-type', 'application/json');
};
