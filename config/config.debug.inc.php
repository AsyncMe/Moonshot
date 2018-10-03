<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/10
 * Time: 上午1:47
 */
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = '127.0.0.1';
$config['db']['user']   = 'moon_shot_user';
$config['db']['pass']   = '7xK2da7e';
$config['db']['dbname'] = 'moon_shot';
$config['db']['charset'] = 'utf8';
$config['db']['collation'] = 'utf8_unicode_ci';
$config['db']['prefix'] = 'ng_';


$config['logger']['name'] = 'moon_shot';
$config['logger']['level'] = 100;
$config['logger']['path'] = __DIR__ . '/../logs/app_'.date('Ymd').'.log';

$config['redis']['host']   = 'localhost';
$config['redis']['pass']   = 'B7dHr2';
$config['redis']['port']   = '6379';
$config['redis']['dbnum'] = '10';
$config['redis']['db_mark'] = 'yazai_app_db';

$config['filecache']['path'] = 'data/cache/file';

if (file_exists("../config/version.inc.php")) {
    $version_config = include "version.inc.php";
    $config = array_merge($config,$version_config);
}




return $config;