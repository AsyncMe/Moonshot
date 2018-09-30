<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/8/4
 * Time: 下午2:32
 */

namespace admin;

use admin\model;
use libs\asyncme\RequestHelper;
use PHPSQLParser\PHPSQLParser;
use PHPSQLParser\utils\PHPSQLParserConstants;

class Plugins extends PermissionBase
{
    public function indexAction(RequestHelper $req,array $preData)
    {
        $status = true;
        $mess = '成功';

        $nav_data = $this->nav_default($req,$preData);


        //ng_func_privilege_check($req->company_id,$this->sessions['admin_uid'],'index');

        $path = [
            'mark' => 'sys',
            'bid'  => $req->company_id,
            'pl_name'=>'admin',
        ];
        $query = [
            'mod'=>'plugins',
            'act'=>'info'
        ];
        $default_frame_url = urlGen($req,$path,$query,true);


        $sub_plugin_menus = [
            ['id'=>81003,'parentid'=>81002,'app'=>'admin' ,'model'=>'plugins','action'=>'center',
                'data'=>'','category'=>'应用','placehold'=>'','use_priv'=>1,'type'=>1,
                'link'=>1,'status'=>1,'name'=>'应用市场','icon'=>'th'
            ],
            ['id'=>81004,'parentid'=>81002,'app'=>'admin' ,'model'=>'plugins','action'=>'has',
                'data'=>'','category'=>'应用','placehold'=>'','use_priv'=>1,'type'=>1,
                'link'=>1,'status'=>1,'name'=>'已安装','icon'=>'th'
            ],
        ];
        $plugin_menus = [
            ['id'=>81001,'parentid'=>0,'app'=>'admin' ,'model'=>'plugins','action'=>'info',
                'data'=>'','category'=>'应用','placehold'=>'','use_priv'=>1,'type'=>1,
                'link'=>1,'status'=>1,'name'=>'信息','icon'=>'th'
            ],
            ['id'=>81002,'parentid'=>0,'app'=>'admin' ,'model'=>'plugins','action'=>'center',
                'data'=>'','category'=>'应用','placehold'=>'','use_priv'=>1,'type'=>1,
                'link'=>1,'status'=>1,'name'=>'应用中心','icon'=>'th','items'=>$sub_plugin_menus,
            ],
        ];



        $plugin_dao = new model\PluginModel($this->service);
        $plugin_custom_menus = $plugin_dao->getSubMenu(0);
        if ($plugin_custom_menus) {
            $plugin_menus = array_merge($plugin_menus,$plugin_custom_menus);
        }

        $plugin_menus = $this->recursion_menus($req,$plugin_menus);

        $data = [
            'default_frame_name'=>'仪表盘',
            'content'=>'',
            'default_frame_url'=>$default_frame_url,
            'plugin_menus'=>$plugin_menus,
        ];
        $data = array_merge($nav_data,$data);

        return $this->render($status,$mess,$data,'template','plugin/index');
    }

    /**
     * 本地数据
     * @param RequestHelper $req
     * @param array $preData
     * @return \libs\asyncme\ResponeHelper
     */
    public function hasAction(RequestHelper $req,array $preData)
    {
        $plugin_dao = new model\PluginModel($this->service);
        $where = [];
        $pre_page = 20;
        $plugin_lists = $plugin_dao->getPluginLists($where,'',[['mtime','desc']],$pre_page);
        if ($plugin_lists) {
            $plugin_lists = $this->format_plugin_data($plugin_lists);
        }

        $installed = 1;
        $path = [
            'mark' => 'sys',
            'bid'  => $req->company_id,
            'pl_name'=>'admin',
        ];
        $query = [
            'mod'=>'plugins',
            'installed'=>$installed,

        ];
        foreach ($plugin_lists as $key => $val) {

            $file = $val['name'] ? strtolower($val['name']) : $val['file'];


            $uninstall_url = array_merge($query,['act'=>'uninstall','file'=>$file,'plugin_id'=>$val['id']]);
            $plugin_lists[$key]['uninstall_url'] = urlGen($req,$path,$uninstall_url,true);

            $review_url = array_merge($query,['act'=>'review','file'=>$file,'plugin_id'=>$val['id']]);
            $plugin_lists[$key]['review_url'] = urlGen($req,$path,$review_url,true);

            $info_url = array_merge($query,['act'=>'info','file'=>$file,'plugin_id'=>$val['id']]);
            $plugin_lists[$key]['info_url'] = urlGen($req,$path,$info_url,true);
        }

        $status = true;
        $mess = '成功';

        $data = [
            'plugin_lists'=>$plugin_lists,
        ];
        return $this->render($status,$mess,$data,'template','plugin/has');
    }
    /**
     * 应用中心
     * @param RequestHelper $req
     * @param array $preData
     */
    public function centerAction(RequestHelper $req,array $preData)
    {

        $plugin_lists = $this->load_plugin_datas($req,$preData);
        $status = true;
        $mess = '成功';
        if($plugin_lists) {

            $plugin_dao = new model\PluginModel($this->service);
            $where = [];
            $pre_page = 20;
            $install_plugin_lists = $plugin_dao->getPluginLists($where,['id','title','class_name','version'],[['mtime','desc']],$pre_page);
            $checked_install_data = [];
            if ($install_plugin_lists) {
                foreach($install_plugin_lists as $val) {
                    $key = $val['class_name'].'_'.$val['version'];
                    $key = md5($key);
                    $checked_install_data[$key] = $val['id'];
                }
            }


            $path = [
                'mark' => 'sys',
                'bid'  => $req->company_id,
                'pl_name'=>'admin',
            ];


            foreach ($plugin_lists as $key => $val) {

                $file = $val['base']['name'] ? strtolower($val['base']['name']) : $val['file'];
                $check_key = md5($file."_".$val['base']['version']);

                $plugin_id = 0;
                if($checked_install_data[$check_key]) {
                    $installed = 1;
                    $plugin_id = $checked_install_data[$check_key];
                } else {
                    $installed = 0;
                }

                $plugin_lists[$key]['installed'] = $installed;
                $query = [
                    'mod'=>'plugins',
                    'installed'=>$installed,
                    'plugin_id' => $plugin_id,

                ];

                $install_url = array_merge($query,['act'=>'install','file'=>$file]);
                $plugin_lists[$key]['install_url'] = urlGen($req,$path,$install_url,true);

                $upgrade_url = array_merge($query,['act'=>'upgrade','file'=>$file]);
                $plugin_lists[$key]['upgrade_url'] = urlGen($req,$path,$upgrade_url,true);

                $uninstall_url = array_merge($query,['act'=>'uninstall','file'=>$file]);
                $plugin_lists[$key]['uninstall_url'] = urlGen($req,$path,$uninstall_url,true);

                $review_url = array_merge($query,['act'=>'review','file'=>$file]);
                $plugin_lists[$key]['review_url'] = urlGen($req,$path,$review_url,true);

                $info_url = array_merge($query,['act'=>'info','file'=>$file]);
                $plugin_lists[$key]['info_url'] = urlGen($req,$path,$info_url,true);
            }
        }
        $data = [
            'plugin_lists'=>$plugin_lists,
        ];

        return $this->render($status,$mess,$data,'template','plugin/center');
    }

    public function infoAction(RequestHelper $req,array $preData)
    {

        $plugin_id = $req->query_datas['plugin_id'];
        $status = false;
        $mess = '失败';

        $path = [
            'mark' => 'sys',
            'bid'  => $req->company_id,
            'pl_name'=>'admin',
        ];
        $query = [
            'mod'=>'plugins',
            'act'=>'center',

        ];
        $back_url = urlGen($req,$path,$query,true);

        $data = [
            'back_url'=>$back_url,
        ];


        if($req->query_datas['file']) {
            $plugin_lists = $this->load_plugin_datas($req,$preData,$req->query_datas['file']);

            if ($plugin_id > 0) {
                $plugin_dao = new model\PluginModel($this->service);
                $where = [
                    'id'=>$plugin_id
                ];

                $install_plugin_item = $plugin_dao->getPluginInfo($where,['id','title','class_name','version']);
                if ($install_plugin_item) {
                    $installed = 1;
                }
            }


            if ($plugin_lists) {
                $path = [
                    'mark' => 'sys',
                    'bid'  => $req->company_id,
                    'pl_name'=>'admin',
                ];
                $query = [
                    'mod'=>'plugins',
                    'installed'=>$installed,
                    'plugin_id' => $plugin_id,
                ];

                foreach ($plugin_lists as $key => $val) {

                    $plugin_lists[$key]['installed'] = $installed;

                    $file = $val['base']['name'] ? strtolower($val['base']['name']) : $val['file'];
                    $install_url = array_merge($query,['act'=>'install','file'=>$file]);
                    $plugin_lists[$key]['install_url'] = urlGen($req,$path,$install_url,true);

                    $upgrade_url = array_merge($query,['act'=>'upgrade','file'=>$file]);
                    $plugin_lists[$key]['upgrade_url'] = urlGen($req,$path,$upgrade_url,true);

                    $uninstall_url = array_merge($query,['act'=>'uninstall','file'=>$file]);
                    $plugin_lists[$key]['uninstall_url'] = urlGen($req,$path,$uninstall_url,true);

                    $review_url = array_merge($query,['act'=>'review','file'=>$file]);
                    $plugin_lists[$key]['review_url'] = urlGen($req,$path,$review_url,true);

                    $info_url = array_merge($query,['act'=>'info','file'=>$file]);
                    $plugin_lists[$key]['info_url'] = urlGen($req,$path,$info_url,true);
                }
                reset($plugin_lists);

                $status = true;
                $mess = '成功';
                $data = array_merge($data,[
                    'plugin_lists'=>$plugin_lists[0],
                ]);

            }
        }

        return $this->render($status,$mess,$data,'template','plugin/info');
    }

    public function reviewAction(RequestHelper $req,array $preData)
    {

        $plugin_id = $req->query_datas['plugin_id'];
        $status = false;
        $mess = '失败';

        $path = [
            'mark' => 'sys',
            'bid'  => $req->company_id,
            'pl_name'=>'admin',
        ];
        $query = [
            'mod'=>'plugins',
            'act'=>'center',

        ];
        $back_url = urlGen($req,$path,$query,true);

        $data = [
            'back_url'=>$back_url,
        ];


        if($req->query_datas['file'] && $plugin_id) {
            $plugin_lists = $this->load_plugin_datas($req,$preData,$req->query_datas['file']);

            if ($plugin_id > 0) {
                $plugin_dao = new model\PluginModel($this->service);
                $where = [
                    'id'=>$plugin_id
                ];

                $install_plugin_item = $plugin_dao->getPluginInfo($where,['id','title','class_name','version']);
                if ($install_plugin_item) {
                    $installed = 1;
                }
            }


            if ($plugin_lists) {
                $path = [
                    'mark' => 'sys',
                    'bid'  => $req->company_id,
                    'pl_name'=>'admin',
                ];
                $query = [
                    'mod'=>'plugins',
                    'installed'=>$installed,
                    'plugin_id' => $plugin_id,
                ];

                foreach ($plugin_lists as $key => $val) {

                    $plugin_lists[$key]['installed'] = $installed;

                    $file = $val['base']['name'] ? strtolower($val['base']['name']) : $val['file'];
                    $install_url = array_merge($query,['act'=>'install','file'=>$file]);
                    $plugin_lists[$key]['install_url'] = urlGen($req,$path,$install_url,true);

                    $upgrade_url = array_merge($query,['act'=>'upgrade','file'=>$file]);
                    $plugin_lists[$key]['upgrade_url'] = urlGen($req,$path,$upgrade_url,true);

                    $uninstall_url = array_merge($query,['act'=>'uninstall','file'=>$file]);
                    $plugin_lists[$key]['uninstall_url'] = urlGen($req,$path,$uninstall_url,true);

                    $review_url = array_merge($query,['act'=>'review','file'=>$file]);
                    $plugin_lists[$key]['review_url'] = urlGen($req,$path,$review_url,true);

                    $info_url = array_merge($query,['act'=>'info','file'=>$file]);
                    $plugin_lists[$key]['info_url'] = urlGen($req,$path,$info_url,true);
                }
                reset($plugin_lists);

                $status = true;
                $mess = '成功';
                $data = array_merge($data,[
                    'plugin_lists'=>$plugin_lists[0],
                ]);
                //dump($plugin_lists[0]);

            }
        } else {
            $status = false;
            $mess = '失败';
            $data = [
                'error'=>'参数错误',
            ];
        }

        return $this->render($status,$mess,$data,'template','plugin/review');
    }

    public function uninstallAction(RequestHelper $req,array $preData)
    {
        try {
            $uninstall_detail = [];
            if($req->query_datas['file'] && $req->query_datas['plugin_id']) {
                $plugin_dao = new model\PluginModel($this->service);
                $where = [];
                $where['id'] = $req->query_datas['plugin_id'];
                $where['class_name'] = $req->query_datas['file'];

                // 1.查找插件信息
                $plugin_info = $plugin_dao->getPluginInfo($where);
                if ($plugin_info) {
                    if ($plugin_info['plugin_process']) {
                        $plugin_info['plugin_process'] = ng_mysql_json_safe_decode($plugin_info['plugin_process']);
                    }
                    // 2.对数据表的数据进行导出打包

                    if ($plugin_info['plugin_process']['install_table']) {

                        $backup_root = NG_ROOT."/backup/".$where['class_name'];
                        if (!is_dir($backup_root."/")) {
                            mkdir($backup_root."/",0755,true);
                        }
                        $backup_log =$plugin_dao->backup_plugin_tables($where['class_name'],$plugin_info['plugin_process']['install_table'],$backup_root);
                        $uninstall_detail[] = ['title'=>'备份数据表','mess'=>$backup_log];
                    }

                    // 3.删除菜单
                    if ($plugin_info['plugin_process']['menu']) {
                        foreach($plugin_info['plugin_process']['menu'] as $uninstall_menu) {
                            $uninstall_detail[] = ['title'=>'删除菜单','mess'=>$uninstall_menu['plugin_name'].'/'.$uninstall_menu['action']];
                            $plugin_dao->deletePluginMenu(['id'=>$uninstall_menu['id']]);
                        }
                    }
                    // 4.删除关联关系和权限
                    $plugin_rel_where = ['plugin_id'=>$plugin_info['id']];
                    $plugin_rel_count =$plugin_dao->getPluginRelCount($plugin_rel_where);
                    if ($plugin_rel_count) {
                        $plugin_dao->deletePluginRel($plugin_rel_where);
                        $uninstall_detail[] = ['title'=>'删除关联关系','mess'=>$plugin_rel_count.'条'];
                    }


                    // 5.删除插件数据表
                    if ($plugin_info['plugin_process']['install_table']) {
                        foreach ($plugin_info['plugin_process']['install_table'] as $uninstall_table) {
                            $plugin_dao->drop_table($uninstall_table);
                            $uninstall_detail[] = ['title'=>'删除表结构','mess'=>$uninstall_table];
                        }
                    }

                    // 6.删除插件表的记录
                    $plugin_dao->deletePlugin(['id'=>$plugin_info['id']]);
                    $uninstall_detail[] = ['title'=>'删除插件','mess'=>$plugin_info['title']."[ id:".$plugin_info['id']."]"];

                    // 7.写日志
                    $info = [
                        '删除插件:'.$plugin_info['title'],
                        "插件id:".$plugin_info['id'],
                        "操作人:".$this->sessions['admin_name']."(".$this->sessions['admin_user'].")",
                        "操作人id:".$this->sessions['admin_uid'],

                    ];
                    $info = implode(";",$info);
                    $this->service->getLogger()->addInfo($info);
                    $uninstall_detail[] = ['title'=>'记录日志'];

                }

            }

        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $status = true;
        $mess = '成功';
        $delay_time = 5;

        $path = [
            'mark' => 'sys',
            'bid'  => $req->company_id,
            'pl_name'=>'admin',
        ];
        $query = [
            'mod'=>'plugins',
            'act'=>'has',

        ];
        $back_url = urlGen($req,$path,$query);

        $data = [
            'status'=>$status,
            'title'=>$plugin_info['title'],
            'version'=>$plugin_info['version'],
            'back_url'=>$back_url,
            'delay_time'=>$delay_time,
            'error'=>$error,
            'operate'=>'卸载',

        ];
        if (!empty($uninstall_detail)) {
            $data['detail'] = $uninstall_detail;
        }

        return $this->render($status,$mess,$data,'template','plugin/install');
    }


    /**
     * 安装插件
     * @param RequestHelper $req
     * @param array $preData
     */
    public function installAction(RequestHelper $req,array $preData)
    {
        try{
            if($req->query_datas['file']) {
                $plugin_dao = new model\PluginModel($this->service);
                $plugin_lists = $this->load_plugin_datas($req, $preData, $req->query_datas['file']);

                if($plugin_lists) {
                    $plugin_lists= reset($plugin_lists);

                    $plugin_base_item = $plugin_lists['base'];
                    //校样
                    if ($plugin_lists['file'] == $plugin_base_item['name'] ) {
                        $update_map = [];
                        //1.执行数据表
                        if ($plugin_lists['install']['table']) {
                            if (!isset($plugin_lists['install']['table'][0])) {
                                $plugin_lists['install']['table'] = array($plugin_lists['install']['table']);
                            }
                            $parse_table_result = [];
                            $this->parse_xml_install_table($plugin_dao,$plugin_lists['install']['table'],$parse_table_result);

                            if($parse_table_result) {
                                $update_map['install_table'] = $parse_table_result;
                            }

                        }

                        //2.插入插件表
                        $parse_info_result = [];
                        $plugin_id = $this->parse_xml_install_info($plugin_dao,$plugin_lists,$parse_info_result);
                        if ($parse_info_result) {
                            $update_map['base'] = $parse_info_result;
                        }

                        if ($plugin_id) {
                            //3.插入菜单表
                            $parse_menu_result = [];
                            $this->parse_xml_install_memu($plugin_dao,$plugin_lists['menus'],$plugin_id,$plugin_lists['base']['name'],0,$parse_menu_result);
                            if ($parse_menu_result) {
                                $update_map['menu'] = $parse_menu_result;
                            }

                            if ($update_map) {
                                $save_map = [];
                                $save_map['plugin_process'] = ng_mysql_json_safe_encode($update_map);
                                $save_map['mtime'] = time();
                                //4.更新插件表
                                $save_where = [
                                    'id'=>$plugin_id
                                ];
                                $update_flag = $plugin_dao->updatePlugin($save_where,$save_map);


                            }
                        }



                    } else {
                        throw new \Exception("安装失败,name和目录名称不一致","10023");
                    }


                }

            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        if ($error) {
            $status = false;
            $mess = '失败';
            $delay_time = 10;
        } else {
            $status = true;
            $mess = '成功';
            $delay_time = 5;
        }

        $path = [
            'mark' => 'sys',
            'bid'  => $req->company_id,
            'pl_name'=>'admin',
        ];
        $query = [
            'mod'=>'plugins',
            'act'=>'center',

        ];
        $back_url = urlGen($req,$path,$query);

        $data = [
            'status'=>$status,
            'title'=>$plugin_base_item['title'],
            'version'=>$plugin_base_item['version'],
            'back_url'=>$back_url,
            'delay_time'=>$delay_time,
            'error'=>$error,
            'operate'=>'安装',
        ];

        return $this->render($status,$mess,$data,'template','plugin/install');
    }

    /**
     * 格式化数据
     * @param $plugin_data
     * @return array
     */
    protected function format_plugin_data($plugin_datas)
    {
        $format_data = [];
        foreach ($plugin_datas as $key=>$plugin_data) {
            $format_data[$key]['id'] = $plugin_data['id'];
            if (isset($plugin_data['title'])) {
                $format_data[$key]['base']['title'] = $plugin_data['title'];
            }
            if (isset($plugin_data['category'])) {
                $format_data[$key]['base']['category'] = $plugin_data['category'];
            }
            if (isset($plugin_data['class_name'])) {
                $format_data[$key]['base']['name'] = $plugin_data['class_name'];
                $format_data[$key]['file'] = $plugin_data['class_name'];
            }
            if (isset($plugin_data['desc'])) {
                $format_data[$key]['base']['desc'] = $plugin_data['desc'];
            }
            if (isset($plugin_data['version'])) {
                $format_data[$key]['base']['version'] = $plugin_data['version'];
            }
            if (isset($plugin_data['author'])) {
                $format_data[$key]['base']['author'] = $plugin_data['author'];
            }
            if (isset($plugin_data['icon'])) {
                $format_data[$key]['base']['icon'] = $plugin_data['icon'];
            }
            if (isset($plugin_data['pub_time'])) {
                $format_data[$key]['base']['time'] = $plugin_data['pub_time'];
            }
        }

        return $format_data;
    }
    /**
     * @param RequestHelper $req
     * @param array $preData
     */
    protected function load_plugin_datas(RequestHelper $req,array $preData,$filter='')
    {
        $plugin_dir = '../plugins';
        $plugins_lists = [];
        if (is_dir($plugin_dir)) {
            if ($filter==''){
                $fp = opendir($plugin_dir);
                while($file = readdir($fp)) {
                    //去掉隐藏
                    if(substr($file,0,1)=='.') continue;

                    if (file_exists($plugin_dir.'/'.$file.'/info.xml')) {
                        $mtime = filemtime($plugin_dir.'/'.$file);
                        $ctime = filectime($plugin_dir.'/'.$file);
                        $plugins_lists[] = ['file'=>$file,'root'=>$plugin_dir,'ctime'=>$ctime,'mtime'=>$mtime];
                    }

                }
                closedir($fp);
            } else {
                if (file_exists($plugin_dir.'/'.$filter.'/info.xml')) {
                    $mtime = filemtime($plugin_dir.'/'.$filter);
                    $ctime = filectime($plugin_dir.'/'.$filter);
                    $plugins_lists[] = ['file'=>$filter,'root'=>$plugin_dir,'ctime'=>$ctime,'mtime'=>$mtime];
                }
            }

        }
        $result = [];
        if ($plugins_lists) {
            foreach ($plugins_lists as $key=>$val) {
                $xml = simplexml_load_file($val['root'].'/'.$val['file'].'/info.xml',null,LIBXML_NOCDATA);
                $json_xml=json_encode($xml);
                $dejson_xml=json_decode($json_xml,true);
                $result[] = array_merge($dejson_xml,$val);
            }
        }
        return $result;
    }

    /**
     * 处理插件的基本信息
     * @param model\PluginModel $plugin_dao
     * @param $plugin_lists
     * @param $parse_result
     * @return mixed
     * @throws \Exception
     */
    protected function parse_xml_install_info(model\PluginModel $plugin_dao,$plugin_lists,&$parse_result)
    {
        $plugin_base_item = $plugin_lists['base'];

        $session = $this->service->getSession();

        $pl_map = [];
        $pl_map['ctime'] = time();
        $pl_map['mtime'] = time();
        $pl_map['operation'] = $session['admin_uid'];

        $pl_map['category'] = $plugin_base_item['category'];
        $pl_map['title'] = $plugin_base_item['title'];
        $pl_map['desc'] = $plugin_base_item['desc'];
        $pl_map['version'] = $plugin_base_item['version'];
        $pl_map['author'] = $plugin_base_item['author'];
        $pl_map['icon'] = $plugin_base_item['icon'];
        $pl_map['pub_time'] = $plugin_base_item['time'];

        if($plugin_base_item['properties']){
            $pl_map['properties'] = $plugin_base_item['properties'];
        }
        if($plugin_base_item['type']){
            $pl_map['type'] = $plugin_base_item['type'];
        }
        if($plugin_base_item['category']){
            $pl_map['category'] = $plugin_base_item['category'];
        }
        if($plugin_base_item['sub_cate']){
            $pl_map['sub_cate'] = $plugin_base_item['sub_cate'];
        }
        $pl_map['plugin_root'] = $plugin_lists['root'].'/'.$plugin_lists['file'].'/';
        $pl_map['class_name'] = $plugin_lists['file'];


        $parse_result['file']['ctime'] = $plugin_lists['ctime'];
        $parse_result['file']['mtime'] = $plugin_lists['mtime'];;

        if($plugin_base_item['preview']['item']) {
            if (!isset($plugin_base_item['preview']['item'][0])){
                $plugin_base_item['preview']['item'] = array($plugin_base_item['preview']['item']);
            }
            $parse_result['preview'] =  $plugin_base_item['preview']['item'];
        }
        $where = [
            'title' => $pl_map['title'],
            'version' =>$pl_map['version'],
            'class_name' => $pl_map['class_name'],
        ];
        $pl_info = $plugin_dao->getPluginInfo($where,['id','title']);
        if (!$pl_info) {
            $pl_id =  $plugin_dao->addPlugin($pl_map);
            if (!$pl_id) {
                throw new \Exception("插入插件错误","10020");
            }
            return $pl_id;
        } else {
            return $pl_info['id'];
        }

    }

    /**
     * 处理插件的菜单
     * @param model\PluginModel $plugin_dao
     * @param array $menu_xml
     * @param $plugin_id
     * @param $plugin_name
     * @param $parentid
     * @param $parse_result
     * @throws \Exception
     */
    protected function parse_xml_install_memu(model\PluginModel $plugin_dao,array $menu_xml,$plugin_id,$plugin_name,$parentid,&$parse_result)
    {
        if(is_array($menu_xml['item'])) {
            if (!isset($menu_xml['item'][0])) {
                $menu_xml['item'] = array($menu_xml['item']);
            }
            foreach($menu_xml['item'] as $item) {
                if ($plugin_id && $item['action'] && $item['name'] ) {
                    $map = [];
                    $map['plugin_id'] = $plugin_id;
                    $map['app'] = $plugin_name;
                    $map['action'] = $item['action'];
                    $map['status'] = 1;
                    $map['parentid'] = $parentid;
                    $map['name'] = $item['name'];

                    if($item['icon']) $map['icon'] = $item['icon'];
                    $map['ctime']= time();
                    $map['mtime']= time();

                    $where = [
                        'plugin_id'=>$map['plugin_id'],
                        'app' =>$map['app'],
                        'action'=>$map['action']
                    ];
                    $pl_info = $plugin_dao->getPluginMenuInfo($where,['id']);
                    if ($pl_info) {
                        $new_menu_id = $pl_info['id'];
                    } else {
                        $new_menu_id = $plugin_dao->addPluginMenu($map);
                    }
                    if ($new_menu_id) {
                        $parse_result[] = ['id'=>$new_menu_id,'plugin_name'=>$plugin_name,'action'=>$item['action']];
                        if (count($item['submenu'])) {
                            $this->parse_xml_install_memu($plugin_dao,$item['submenu'],$plugin_id,$plugin_name,$new_menu_id,$parse_result);
                        }

                    } else {
                        throw new \Exception("插入插件菜单错误","10021");
                    }
                }

            }
        }

    }

    /**
     * 处理插件的数据表
     * @param model\PluginModel $plugin_dao
     * @param array $xml
     * @param $parse_result
     * @throws \Exception
     */
    protected function parse_xml_install_table(model\PluginModel $plugin_dao,array $xml,&$parse_result)
    {
        $sql_parser = new PHPSQLParser();
        $table_prefix = $plugin_dao->get_table_prefix();

        foreach($xml as $item) {
            $sql = trim($item);
            $sql_parser->parse($sql);
            $parsed = $sql_parser->parsed;
            if ($parsed && $parsed['CREATE']) {
                //建表语句

                $install_table_name = $parsed['TABLE']['no_quotes']['parts'][0];
                if(!preg_match("/^$table_prefix/is",$install_table_name)) {
                    $real_table_name = $table_prefix.$install_table_name;
                    $parse_result[] = $install_table_name;
                }else {
                    $real_table_name = $install_table_name;
                    $table_prefix_strlen = strlen($table_prefix);
                    $install_table_name = substr($real_table_name,$table_prefix_strlen);
                    $parse_result[] = $install_table_name;
                }

                $table_exist = false ;
                if(!$parsed['CREATE']['not-exists']) {
                    //存在自检查，则不需要代码检查
                    $table_exist = $plugin_dao->has_table($install_table_name);
                }
                //存在的话，就不在处理
                //表必须要有一个自增字段
                if (!preg_match('/primary key/is',$item)) {
                    throw new \Exception('数据表必须有一个主键',10051);
                }

                if (!preg_match('/auto_increment/is',$item)) {
                    throw new \Exception('数据表必须有一个自增字段',10052);
                }

                if (!preg_match('/company_id/is',$item)) {
                    throw new \Exception('数据表必须有一个company_id,作为大Bid',10053);
                }


                if ($table_exist) continue;

                $sql = preg_replace("/`$install_table_name`/is","`$real_table_name`",$sql);

                if($sql) {
                    $flag = $plugin_dao->create_table($sql);
                    if (!$flag) {
                        throw new \Exception("'$install_table_name' 数据表安装错误","10032");
                    }
                }


            }

        }
        unset($sql_parser);


    }


}