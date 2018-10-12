<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/8/4
 * Time: 下午2:32
 */

namespace admin;

use admin\model;
use libs\asyncme\RequestHelper as RequestHelper;
use libs\asyncme\ResponeHelper as ResponeHelper;
use PHPSQLParser\PHPSQLParser;
use PHPSQLParser\utils\PHPSQLParserConstants;
use \Slim\Http\UploadedFile;

/**
 * @name 资源管理
 * Class Asset
 * @package admin
 */
class Asset extends PermissionBase
{
    /**
     * @name 上传对话框
     * @param RequestHelper $req
     * @param array $preData
     * @return ResponeHelper
     * @priv ask
     */
    public function uploadAction(RequestHelper $req,array $preData)
    {

        try {
            $request_uid = $req->query_datas['admin_uid'];

            $filetype = $req->query_datas['filetype'];
            $filetype = $filetype ? $filetype : 'image';

            $multi = $req->query_datas['multi'];
            $multi = $multi ? intval($multi) : 0;

            $app = $req->query_datas['app'];
            //零时文件夹
            $tmp = $req->query_datas['tmp'];

            $upload_setting = $this->upload_setting($filetype);

            $filetypes = [];
            foreach (['*','image','video','audio','file'] as $type) {
                if(isset($upload_setting[$type])) {
                    $filetypes[$type] = [
                        'title'=>ucfirst($type).' files',
                        'extensions'=>$upload_setting[$type]['extensions'],
                    ];
                }
            }


            $upload_max_filesize=$upload_setting[$filetype]['upload_max_filesize'];


            $mime_type=[];
            if(array_key_exists($filetype, $filetypes)){
                $mime_type=$filetypes[$filetype];
            }else{
                throw new \Exception('上传文件类型配置错误！');
            }

            //图片上传地址
            $path = [
                'mark' => 'sys',
                'bid'  => $req->company_id,
                'pl_name'=>'admin',
            ];
            $query = [
                'mod'=>'asset',
                'act'=>'fileupload',
                'admin_uid'=>$request_uid,
                'multi'=>$multi,
                'filetype'=>$filetype,
                'app'=>$app,
                'tmp'=>$tmp,
            ];
            $asset_upload_url = urlGen($req,$path,$query,true);

            $data = [
                'admin_uid'=>$request_uid,
                'upload_max_filesize'=>$upload_max_filesize,
                'upload_max_filesize_mb'=>intval($upload_max_filesize/1024),
                'multi'=>$multi,
                'app'=>$app,
                'extensions'=>$upload_setting[$filetype]['extensions'],
                'mime_type'=>json_encode($mime_type),
                'asset_upload_url'=>$asset_upload_url,
            ];
            $status = true;
            $mess = '成功';



        } catch (\Exception $e) {
            $status = false;
            $mess = '失败';
            $data = [
                'error'=>$e->getMessage()
            ];
        }
        return $this->render($status,$mess,$data,'template','asset/plupload');

    }

    /**
     * @name 文件上传
     * @param RequestHelper $req
     * @param array $preData
     * @return ResponeHelper
     * @priv ask
     */
    public function fileuploadAction(RequestHelper $req,array $preData)
    {
        $success_upload_data = [];

        $multi = $req->query_datas['multi'];
        $multi = $multi ? intval($multi) : 0;

        $filetype = $req->query_datas['filetype'];
        $filetype = $filetype ? $filetype : 'image';

        $app = $req->query_datas['app'];
        //零时文件夹
        $tmp = $req->query_datas['tmp'];

        $asset_path = $this->service->getAssetPath($app,$tmp);
        if(!is_dir($asset_path.'/')) {
            mkdir($asset_path.'/',0775,true);
        }


        $upload_setting = $this->upload_setting($filetype);

        $allow_extensions=explode(',', $upload_setting[$filetype]['extensions']);

        try{
            foreach ( $req->upload_files as $file) {
                $oldname = $file->getClientFilename();
                $error = $file->getError();
                if ($error === UPLOAD_ERR_OK) {
                    $extension = strtolower(pathinfo($oldname)['extension']);
                    if (!in_array($extension,$allow_extensions)) {
                        throw new \Exception('类型不支持');
                    }
                    $uploadFileName = uniqid(date('Ymd').'-').".".$extension;
                    $file->moveTo($asset_path."/".$uploadFileName);
                    $filesize = $file->getSize();
                    $filetype = $file->getClientMediaType();
                    //如果是图片，获得图片的尺寸
                    $smeta = [];
                    $filetype_names = explode('/',$filetype);
                    if (strtolower($filetype_names[0])=='image') {
                        if(function_exists('getimagesize')) {
                            $img_info = getimagesize($asset_path."/".$uploadFileName);
                            $smeta = [
                                'width'=>$img_info[0],
                                'height'=>$img_info[1],
                            ];
                        }
                    }

                    $preview_url = '/wxapp'.ltrim($asset_path."/".$uploadFileName,'.');
                    if(preg_match("/^\.\/data/is",$asset_path)) {
                        $filepath = preg_replace("/^\.\/data/is",'',$asset_path)."/".$uploadFileName;
                    } else {
                        $filepath = $asset_path."/".$uploadFileName;
                    }
                    $success_upload_data[] = [
                        'name'=>$oldname,
                        'preview_url'=>$preview_url,
                        'url'=>$preview_url,
                        'filepath'=>$filepath,
                        'size'=>$filesize,
                        'type'=>$filetype,
                        'smeta'=>$smeta,
                    ];
                } else {
                    $success_upload_data = [
                        'name'=>$oldname,
                        'error'=>$error,
                    ];
                    break;
                }
            }
        }catch (\Exception $e) {
            $error = $e->getMessage();
        }

        if (count($success_upload_data)) {
            $status = true;
            $mess = '成功';
//            if ($multi) {
//                $data = $success_upload_data;
//            } else {
//                $data = $success_upload_data[0];
//            }
            $data = $success_upload_data[0];

        } else {
            $status = false;
            $mess = '失败';
            $data = $error;
        }
        return new ResponeHelper($status,$mess,$data,'json','','admin');
    }

}