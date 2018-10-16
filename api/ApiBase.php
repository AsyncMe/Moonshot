<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/26
 * Time: 下午6:03
 */

namespace api;

use libs\asyncme\Plugins as Plugins;
use libs\asyncme\RequestHelper as RequestHelper;
use libs\asyncme\ResponeHelper as ResponeHelper;
use \Slim\Http\UploadedFile;
use libs\asyncme\Page as Page;

include_once NG_ROOT.'/api/utils/common_func.php';

class ApiBase extends Plugins
{

    public  $global_view_var = [];

    //初始化代码 ，自己调用  看在第几层的情况下调用
    // 0 母程序初始化
    // 1 初始化数据库后
    // 2 初始化模版对象后
    public function initialize($level=0)
    {
        if($level==2){
            if(method_exists($this,'auth')) {
                $auth_reponse = $this->auth();
                if ($auth_reponse['status'] == false) {
                    //欠缺时间部分
                    if ($auth_reponse['error_code']==-1) {
                        $this->redirect($auth_reponse['url']);
                    } else if (in_array($auth_reponse['error_code'],[-2,-3])) {
                        unset($auth_reponse['url']);

                        if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
                            echo json_encode($auth_reponse);
                        } else {
                            echo '没有权限';
                        }
                        die();
                    }

                }
            }
        }
    }

    public function redirect($url)
    {
        header('Location:'.$url);
    }

    public function render($status,$mess,$data,$type='json',$template='') {
        $data = array_merge($this->global_view_var,$data);
        if ($template && substr($template,0,-10)!='.twig.html') {
            $template.= '.twig.html';
        }

        return new ResponeHelper($status,$mess,$data,$type,$template,'manager');
    }



    /**
     * 上传设置
     * @return array
     */
    protected function upload_setting($c='')
    {
        $model = new model\ConfigModel($this->service);
        $config_vals = $model->getConfigInfo(['name'=>'manager_upload_setting']);

        if (!$config_vals || !$config_vals['config']) {
            throw new \Exception('请设置 manager_upload_setting ');
        }
        $config_vals['config'] = ng_mysql_json_safe_decode($config_vals['config']);

        $upload_setting = [];
        if ($config_vals['config']) {
            $c_all = [];
            $max_fileSize = 0;
            $upload_setting['all_keys'] = array_keys($config_vals['config']);
            foreach ($config_vals['config'] as $key=>$val) {
                if ($val) {
                    list($fileSize,$fileExt) = explode('|',$val);
                    $upload_setting[$key] = [
                        'upload_max_filesize' => $fileSize,//单位KB
                        'extensions' => $fileExt
                    ];
                    $c_all[] = $fileExt;
                    $max_fileSize = $max_fileSize>$fileSize ? $max_fileSize : $fileSize;
                }
            }
            if ($c=='*') {
                $upload_setting[$c] = [
                    'upload_max_filesize'=>$max_fileSize,
                    'extensions'=>implode(',',$c_all),
                ];
                unset($c_all);
            }
        }

        foreach ($upload_setting as $setting){
            $extensions=explode(',', trim($setting['extensions']));
            if(!empty($extensions)){
                $upload_max_filesize=intval($setting['upload_max_filesize'])*1024;//转化成KB
                foreach ($extensions as $ext){
                    if(!isset($upload_max_filesize_setting[$ext]) || $upload_max_filesize>$upload_max_filesize_setting[$ext]*1024){
                        $upload_max_filesize_setting[$ext]=$upload_max_filesize;
                    }
                }
            }
        }

        $upload_setting['upload_max_filesize']=$upload_max_filesize_setting;
        return $upload_setting;
    }

    /**
     * 获取cdn的地址头
     * @return string
     */
    protected function getCdnHost($host='')
    {
        $cdn_prefix = $host."/wxapp/data";
        return $cdn_prefix;
    }

    protected function page($pageLink = '',$total_size = 1, $page_size = 0, $current_page = 1, $listRows = 6, $pageParam = '',  $static = false) {
        if ($page_size == 0) {
            $page_size = 20;
        }

        if (empty($pageParam)) {
            $pageParam = 'p';
        }

        $page = new Page($total_size, $page_size, $current_page, $listRows, $pageParam, $pageLink, $static);
        $page->SetPager('Api', '{first}{prev}&nbsp;{liststart}{list}&nbsp;{next}{last}<span>共{recordcount}条数据</span>', array("listlong" => "4", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
        return $page;
    }
}