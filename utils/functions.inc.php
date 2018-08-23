<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/7/10
 * Time: 上午10:26
 */
if(!defined('NG_ME')) die();


/**
 * json数据封装
 * @param $status
 * @param $desc
 * @param int $tag
 * @param array $data
 * @return array
 */
function build_json_data( $status , $desc,$tag=0,$data=[] )
{
    $json_data = ['status'=>$status,'desc'=>$desc,'data'=>$data,'tag'=>$tag,'t'=>time()];
    return $json_data;
}

/**
 * 返回性别称谓
 * @param $sex_val
 * @param array $sex_args
 * @return mixed
 */
function make_sex_title( $sex_val,$sex_args=['保密','先生','女士'])
{
    $sex_title = $sex_args[$sex_val];
    return $sex_title;
}

/**
 * GET 请求
 * @param string $url
 */
function http_get($url)
{
    $oCurl = curl_init();
    if (stripos($url, "https://") !== FALSE) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if (intval($aStatus["http_code"]) == 200) {
        return $sContent;
    } else {
        return false;
    }
}
/**
 * POST 请求
 * @param string $url
 * @param array $param
 * @param boolean $post_file 是否文件上传
 * @return string content
 */
function http_post($url, $param, $post_file = false)
{
    $oCurl = curl_init();
    if (stripos($url, "https://") !== FALSE) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    if (is_string($param) || $post_file) {
        $strPOST = $param;
    } else {
        $aPOST = array();
        foreach ($param as $key => $val) {
            $aPOST[] = $key . "=" . urlencode($val);
        }
        $strPOST = join("&", $aPOST);
    }
    //curl_setopt($oCurl, CURLINFO_CONTENT_TYPE, "")
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POST, true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);

    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if (intval($aStatus["http_code"]) == 200) {
        return $sContent;
    } else {
        return false;
    }
}

/**
 * 参数毫秒数
 * @return float
 */
function getMillisecond()
{
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
}

/**
 * 随机生成16位字符串
 * @return string 生成的字符串
 */
function getRandomStr()
{
    $str = "";
    $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($str_pol) - 1;
    for ($i = 0; $i < 16; $i++) {
        $str .= $str_pol[mt_rand(0, $max)];
    }
    return $str;
}

/**
 * @return string 获得ip地址
 */
function getIP()
{
    $realip = '0.0.0.0';
    if (isset($_SERVER)){
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $realip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $realip = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")){
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("HTTP_CLIENT_IP")) {
            $realip = getenv("HTTP_CLIENT_IP");
        } else {
            $realip = getenv("REMOTE_ADDR");
        }
    }
    return $realip;
}
/**
 * 获取 IP  地理位置
 * 淘宝IP接口
 * @Return: array
 */
function getCity($ip = '')
{
    if($ip == ''){
        //$url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json";
        //$ip=json_decode(file_get_contents($url),true);
        //$data = $ip;
        $data='';
    }else{
        //$url="http://int.dpool.sina.com.cn/iplookup/iplookup.php?ip=".$ip;//新浪
        $url="http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
        $ip=json_decode(file_get_contents($url));
        if((string)$ip->code=='1'){
            return false;
        }
        $data = (array)$ip->data;
    }

    return $data;
}

/**
 * @return 返回浏览器的信息
 */
function getUserAgent()
{
    $agent = $_SERVER['HTTP_USER_AGENT'];
    return $agent;
}

function ng_plugins(\libs\asyncme\RequestHelper $asyRequest,\libs\asyncme\Service $pl_service,$custom=[])
{
    //权限检查

    return callPlugin($asyRequest,$pl_service,$custom);
}

/**
 * 插件点用方法
 * @param $asyRequest
 * @param $pl_service
 * @param array $custom
 * @return mixed
 */
function callPlugin(\libs\asyncme\RequestHelper $asyRequest,\libs\asyncme\Service $pl_service,$custom=[]){
    $plugin_name = strtolower($asyRequest->request_plugin);
    $plugin_name_lists = explode("_",$plugin_name);
    $plugin_class_data = [];
    foreach ($plugin_name_lists as $plugin_name_item) {
        $plugin_class_data[] = ucfirst($plugin_name_item);
    }
    $plugin_class = implode('',$plugin_class_data);


    try {

        if (!file_exists(NG_ROOT.'/plugins/'.$plugin_name.'/'.$plugin_class.'.php')) {
            throw new Exception("plugins calling :".$plugin_class.' and not invaild');
        }

        $pl_class = 'plugins\\'.$plugin_name.'\\'.$plugin_class;
        if (!class_exists($pl_class)) {
            throw new Exception("plugins class  :".$pl_class.'  not exist');
        }
        $pl = new $pl_class(NG_ROOT.'/plugins/'.$plugin_name.'/');
        $pl->setService($pl_service);
        $pl_respone = $pl->run($asyRequest);

    } catch (Exception $e){
        echo $e->getMessage();
        die();
    }
    return $pl_respone;
}

/**
 * URL生成
 * @param $req
 * @param $path
 * @param array $query
 * @param bool $no_host
 * @return string
 */
function urlGen($req,$path,$query=[],$no_host=false)
{
    if($req) {
        //REQUEST_SCHEME
        $serverParams = $req->getServerParams();
        $host = $serverParams['HTTP_HOST'];
        $port = $serverParams['SERVER_PORT']=='80'? '' : ':'.$serverParams['SERVER_PORT'];
        $request_scheme = $serverParams['REQUEST_SCHEME'];
        $script_name = $serverParams['SCRIPT_NAME'];

        if (is_array($path)) {
            $path = implode('/',array_values($path));
        }
        if ($path && is_string($path)  && $path[0]!='/') {
            $path = '/'.$path;
        }
        if (is_array($query)) {
            $querys = [];
            foreach ($query as $k=>$v) {
                $querys[] = $k.'='.urlencode($v);
            }
            $query = implode('&',$querys);
        }
        if ($query && is_string($query) && $query[0]!='?') {
            $query = '?'.$query;
        }
        $res_host = $no_host ? $request_scheme.'://'.$host.$port : '';
        return $res_host.''.$script_name.''.$path.''.$query;

    }

}

/**
 * 入库安全
 * @param $data
 * @return string
 */
function ng_mysql_json_safe_encode($data)
{
    return addslashes(json_encode($data,JSON_UNESCAPED_UNICODE));
}

function ng_mysql_json_safe_decode($data)
{
    return json_decode(stripslashes($data),true);
}

/**
 * 调试输出
 * @param $data
 */
function dump($data)
{
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
}

/**
 * 使用转换
 * @param int $use
 * @return string
 */
function use2Str($use=1,$option=['不显示','显示'])
{
    if (isset($option[$use]))
    {
        return $option[$use];
    }

    return $option[0];
}


