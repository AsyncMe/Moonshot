<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/11
 * Time: 上午11:34
 */

namespace libs\asyncme;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Http\UploadedFile;
use libs\exceptions\InvaildException;

class RequestHelper
{
    //企业id（大B）
    public $company_id;
    //业务id
    public $service_id = 0;
    //用户id
    public $client_openid;
    //请求方法
    public $request_method;
    //路由对象
    public $router;
    //post数据
    public $post_datas;
    //get数据
    public $query_datas;
    //请求标志
    public $request_tag;
    //插件名称
    public $request_plugin;
    //模型
    public $module = null;
    //动作
    public $action = null;
    //上传文件对象
    public $upload_files = null;
    //输出格式
    public $output = 'json';

    //
    protected  $originalRequest;


    public function __construct(Request $request)
    {
        $tag = $request->getQueryParam('tag');
        $tag = $tag ? $tag : 0;
        $this->request_tag = $tag;

        $router = $request->getAttribute('route');
        $this->router = $router;

        $bid = $router->getArgument('bid');
        $this->company_id = $bid;

        $pl_name = $router->getArgument('pl_name');
        $this->request_plugin = $pl_name;

        $post_datas = $request->getParsedBody();
        $this->post_datas = $post_datas;

        $query_datas = $request->getQueryParams();
        $this->query_datas = $query_datas;

        if ($request->isGet()) {
            $openid = $query_datas['openid'];
            $this->client_openid = $openid;
            if(isset($query_datas['sid'])) {
                $this->service_id = $query_datas['sid'];
            }
        } else {
            $openid = $post_datas['openid'];
            $this->client_openid = $openid;
            if(isset($post_datas['sid'])) {
                $this->service_id = $post_datas['sid'];
            }
        }

        if (isset($query_datas['mod'])) {
            $this->module = $query_datas['mod'];
        } else {
            $this->module = 'Index';
        }
        if (isset($query_datas['act'])) {
            $this->action = $query_datas['act'];
        } else {
            $this->action = 'index';
        }

        if (isset($query_datas['output'])) {
            $this->output = $query_datas['output'];
        }

        $this->request_method = $request->getMethod();

        $this->upload_files = $request->getUploadedFiles();

        $this->originalRequest = $request;

    }

    public function build_json_data($status,$desc,$data=[])
    {
        $json_data = ['status'=>$status,'desc'=>$desc,'data'=>$data,'tag'=>$this->request_tag,'t'=>time()];
        return $json_data;
    }

    public function info()
    {
        $params =  get_class_vars(get_class($this));
        foreach ($params as $key => $val) {
            echo "<p>".$key." : ".$val."</p>";
        }
    }

    public function getServerParams()
    {
        if($this->originalRequest) {
            return $this->originalRequest->getServerParams();
        } else {
            return [];
        }
    }

    public function getHeader()
    {
        if($this->originalRequest) {
            return $this->originalRequest->getHeaders();
        } else {
            return [];
        }

    }


}