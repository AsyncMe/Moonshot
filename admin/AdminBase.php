<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/26
 * Time: 下午6:03
 */

namespace admin;

use libs\asyncme\Plugins as Plugins;
use libs\asyncme\RequestHelper as RequestHelper;
use libs\asyncme\ResponeHelper as ResponeHelper;
use \Slim\Http\UploadedFile;

include_once NG_ROOT.'/admin/utils/common_func.php';

class AdminBase extends Plugins
{

    public  $global_view_var = [];

    //初始化代码 ，自己调用  看在第几层的情况下调用
    // 0 母程序初始化
    // 1 初始化数据库后
    // 2 初始化模版对象后
    public function initialize($level=0)
    {
        if($level==2){
            $cache_key = 'global_view_val';
            $this->global_view_var = $this->service->getCache()->get($cache_key);

            if (!$this->global_view_var) {
                $model = new model\Menu($this->service);
                $config_vals = $model->getConfig('sys_global');
                $this->global_view_var = $config_vals;
                $this->service->getCache()->set($cache_key,$config_vals,3600);
            }
            if(method_exists($this,'auth')) {
                $auth_reponse = $this->auth();
                if ($auth_reponse['status'] == false) {
                    //欠缺时间部分
                    $this->redirect($auth_reponse['url']);
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

        return new ResponeHelper($status,$mess,$data,$type,$template,'admin');
    }

    /**
     * 递归处理菜单
     * @param RequestHelper $req
     * @param $menus
     * @return mixed
     */
    protected function recursion_menus(RequestHelper $req,$menus)
    {
        if($menus) foreach ($menus as $key=>$val) {
            $mark = $val['app']=='admin' ? 'sys' : 'plugin';
            $path = [
                'mark' => $mark,
                'bid'  => $req->company_id,
                'pl_name'=>$val['app'],
            ];
            $query = [
                'mod'=>$val['model'],
                'act'=>$val['action']
            ];
            $menus[$key]['url'] = urlGen($req,$path,$query);
            if ($menus[$key]['items']) {
                $menus[$key]['items'] = $this->recursion_menus($req,$menus[$key]['items']);
            }
        }
        return $menus;
    }

    public function nav_default(RequestHelper $req,array $preData)
    {
        $model = new model\Menu($this->service);
        $navs = $model->getNav();

        $default_menu_id = 0;
        if ($navs) {
            foreach ($navs as $key=>$val) {
                //是否加入数据检查

                //默认取第一个
                $default_menu_id = $default_menu_id ? $default_menu_id : $val['id'];
                $navs[$key]['active'] = '';
                if ($val['app']==$req->request_plugin && $val['model']==$req->module && $val['action']==$req->action) {

                    $navs[$key]['active'] = 'nav_active';
                    //适配到取正式的
                    $default_menu_id=$val['id'];
                }

                $path = [
                    'mark' => 'sys',
                    'bid'  => $req->company_id,
                    'pl_name'=>$val['app'],
                ];
                $query = [
                    'mod'=>$val['model'],
                    'act'=>$val['action']
                ];
                $navs[$key]['url'] = urlGen($req,$path,$query);
            }
        }

        //获得子菜单
        if ($default_menu_id) {
            $subMenus = $model->getSubMenu($default_menu_id);
            $subMenus = $this->recursion_menus($req,$subMenus);
        }


        $path = [
            'mark' => 'sys',
            'bid'  => $req->company_id,
            'pl_name'=>'admin',
        ];
        $query = [
            'mod'=>'index',
            'act'=>'info'
        ];
        $default_frame_url = urlGen($req,$path,$query,true);
        $default_frame_name = '首页';

        $data = [
            'bid'=>$req->company_id,
            'pl_name'=>$req->request_plugin,
            'mod'=> $req->module,
            'act'=>$req->action,
            'navs' => $navs,
            'submenu'=>$subMenus,
            'sessions'=>$this->sessions,
            'default_frame_url'=>$default_frame_url,
            'default_frame_name'=>$default_frame_name,
        ];
        return $data;

    }

    /**
     * 上传设置
     * @return array
     */
    protected function upload_setting()
    {
        $upload_setting = array(
            'image' => array(
                'upload_max_filesize' => '10240',//单位KB
                'extensions' => 'jpg,jpeg,png,gif,bmp4'
            ),
            'video' => array(
                'upload_max_filesize' => '10240',
                'extensions' => 'mp4,avi,wmv,rm,rmvb,mkv'
            ),
            'audio' => array(
                'upload_max_filesize' => '10240',
                'extensions' => 'mp3,wma,wav'
            ),
            'file' => array(
                'upload_max_filesize' => '10240',
                'extensions' => 'txt,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar'
            )
        );
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
}