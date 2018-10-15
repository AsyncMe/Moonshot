<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/10
 * Time: 上午1:19
 */
if(!defined('NG_ME')) die();

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Http\UploadedFile;
use libs\exceptions\InvaildException;

use libs\asyncme\RequestHelper;
use libs\asyncme\Service;
use libs\asyncme\ResponeHelper;



//公共api
//request : http://work.crab.com/wxapp/debug.php/api/123/index?act=index
$app->any('/api/{bid:[\w]+}/{pl_name:[\w]+}', function (Request $request, Response $response, array $args) {

    $asyRequest = new RequestHelper($request);

    $plugin_name = strtolower($asyRequest->request_plugin);
    $plugin_name_lists = explode("_",$plugin_name);
    $plugin_class_data = [];
    foreach ($plugin_name_lists as $plugin_name_item) {
        $plugin_class_data[] = ucfirst($plugin_name_item);
    }
    $plugin_name = implode('',$plugin_class_data);

    $response_output = 'json';
    $response_data = '';

    try {

        if (!file_exists(NG_ROOT.'/api/'.$plugin_name.'.php')) {
            throw new InvaildException($plugin_name.' not invaild');
        }


        static $static_pl_service;


        if ( !$static_pl_service ) {
            $pl_service = Service::getInstance($asyRequest->company_id,$asyRequest->service_id,$asyRequest);
            $pl_service->setLogger($this->logger);
            $pl_service->setSession($this->session);
            $pl_service->setCache($this->filecache);
            $pl_service->setRedis($this->redis);
            $pl_service->setDb($this->db);

            $static_pl_service = $pl_service;
        }

//        $pl_service = new Service($asyRequest->company_id,$asyRequest->service_id);
//        $pl_service->setCache($this->redis);
//        $pl_service->setDb($this->db);

        $pl_class = 'api\\'.$plugin_name;

        $pl = new $pl_class(NG_ROOT.'/api/');
        $pl->setService($pl_service);
        $pl->setView($this->api_view);
        $pl_respone = $pl->run($asyRequest);
        if ($pl_respone) {
            $response_output = $pl_respone->getType();
            $response_data = $pl_respone->getData();
            $response_template = $pl_respone->getTemplate();
            $response_plugin_name = $pl_respone->getPluginName();
            $json_data = $asyRequest->build_json_data($pl_respone->getStatus(),$pl_respone->getMessage(),$pl_respone->getData());
        } else {

            $json_data = $asyRequest->build_json_data(false,'nothing');
        }


    } catch (InvaildException $e){
        $json_data = $asyRequest->build_json_data(false,$e->getMessage());
    }


    switch ($response_output) {
        case 'json' :
            return $response->withJson($json_data);
        case 'html' :
            return $response->getBody()->write($response_data);
        case 'redirect' :
            return $response->withRedirect($response_data,301);
        case 'captcha' :
            return $response->withHeader('Content-Type','image/jpeg')->write($response_data->output());
        case 'file' : {
            return $response
                ->withHeader('Content-Type',$pl_respone->export_file_type)
                ->withHeader('Content-Disposition','attachment;filename='.$pl_respone->export_file_name)
                ->withHeader('Cache-Control','must-revalidate,post-check=0,pre-check=0')
                ->withHeader('Expires','0')
                ->withHeader('Pragma','public')
                ->write($response_data);
        }

        case 'template' :{
            if('manager'==$response_plugin_name) {
                return $this->manager_view->render($response,$response_template,$response_data);
            } else {
                $name_spaces = explode('\\',$response_plugin_name);
                array_shift($name_spaces);//去掉命名空间
                array_pop($name_spaces);//去掉类名
                $playup_template_name = $response_template;
                if ($name_spaces) {
                    $response_template = implode("/",$name_spaces)."/templates/".$response_template;
                }
                $template_ext_type = '.twig.html';
                if (substr($response_template,0,-strlen($template_ext_type))!=$template_ext_type) {
                    $response_template.= $template_ext_type;
                }
                if(file_exists("../plugins/".$response_template)) {
                    return $this->plugin_view->render($response,$response_template,$response_data);
                } else {
                    $json_data = $asyRequest->build_json_data(false,"template ".$playup_template_name.' no found!');
                    $response->withJson($json_data);
                }

            }
        }
        default :
            return $response->withJson($json_data);
    }


})->add($vaild_manager_mw)->setName('api');