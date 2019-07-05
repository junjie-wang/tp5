<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

function getClientUrl(){
    //组装客户端的当前完整请求url。
    $clientUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
    return $clientUrl;
}

function is_weixin() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    } return false;
}
/**
 * 模拟tab产生空格
 * @param int $step
 * @param string $string
 * @param int $size
 * @return string
 */
function tab($step = 1, $string = ' ', $size = 4)
{
    return str_repeat($string, $size * $step);
}

function guid(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        //         $uuid = chr(123)// "{"
        //         .substr($charid, 0, 8).$hyphen
        //         .substr($charid, 8, 4).$hyphen
        //         .substr($charid,12, 4).$hyphen
        //         .substr($charid,16, 4).$hyphen
        //         .substr($charid,20,12)
        //         .chr(125);// "}"
        
        $uuid = substr($charid, 0, 8).$hyphen
        .substr($charid, 8, 4).$hyphen
        .substr($charid,12, 4).$hyphen
        .substr($charid,16, 4).$hyphen
        .substr($charid,20,12);// "}"
        return $uuid;
    }
}

function show($expression){
    echo "<br/>";
    var_dump($expression);
}
function trimzq($str)//删除空格
{
    $qian=array(" ","　","\t","\n","\r");
    $hou=array("","","","","");
    return str_replace($qian,$hou,$str);
}

function apiData($status=0,$msg = '', $data =null)
{
    $resposne = array('status' => $status,'msg' => $msg, 'data' => $data);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin:*');
    return str_replace("\\/", "/",json_encode($resposne,JSON_UNESCAPED_UNICODE));
}

/**
 * 获取客户端IP地址
 *
 * @return string IP地址
 */
function get_ip() {
    $realip = '';

    if ( isset($_SERVER) ) {
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
    $onlineip = array();
    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
    return $realip;
}




/**
 * 获取服务器的ip
 *
 * @access public
 *
 * @return string
 **/
function real_server_ip() {
    static $serverip = NULL;
    if ($serverip !== NULL) {
        return $serverip;
    }
    if (isset($_SERVER)) {
        if (isset($_SERVER['SERVER_ADDR'])) {
            $serverip = $_SERVER['SERVER_ADDR'];
        } else {
            $serverip = '0.0.0.0';
        }
    } else {
        $serverip = getenv('SERVER_ADDR');
    }
    return $serverip;
}

/**
 * 互联网允许使用IP地址
 *
 * @access public
 *
 * @return string
 */
function get_ip_type($ip) {
    $iplist = explode(".", $ip);
    if ($iplist[0] >= 224 && $iplist[0] <= 239)               return '多播';
    if ($iplist[0] >= 240 && $iplist[0] <= 255)               return '保留';
    if (preg_match('/^198\.51\.100/', $ip))                   return 'TEST-NET-2，文档和示例';
    if (preg_match('/^203\.0\.113/', $ip))                    return 'TEST-NET-3，文档和示例';
    if (preg_match('/^192\.(18|19)\./', $ip))                 return '网络基准测试';
    if (preg_match('/^192\.168/', $ip))                       return '专用网络[内部网]';
    if (preg_match('/^192\.88\.99/', $ip))                    return 'ipv6to4中继';
    if (preg_match('/^192\.0\.2\./', $ip))                    return 'TEST-NET-1，文档和示例';
    if (preg_match('/^192\.0\.0\./', $ip))                    return '保留（IANA）';
    if (preg_match('/^192\.0\.0\./', $ip))                    return '保留（IANA）';
    if ($iplist[0]==172 && $iplist[1]<=31 && $iplist[1]>=16)  return '专用网络[内部网]';
    if ($iplist[0] == 169 && $iplist[1] == 254)               return '链路本地';
    if ($iplist[0] == 127)                                    return '环回地址';
    if ($iplist[0] == 10)                                     return '专用网络[内部网]';
    if ($iplist[0] == 0)                                      return '本网络（仅作为源地址时合法）';
    return 'InterNet网地址';
}


/**
 * 根据IP地址获所有地
 *
 * @prarm String $ip
 *
 * @return 地区
 */
function convert_ip($ip) {
    $return = '';
    if( preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip) ) {

        /* ip 地址验证 */
        $iparray = explode('.', $ip);
        if($iparray[0] == 10 || $iparray[0] == 127 || ($iparray[0] == 192 && $iparray[1] == 168) || ($iparray[0] == 172 && ($iparray[1] >= 16 && $iparray[1] <= 31))) {
            $return = '- LAN';
        } elseif($iparray[0] > 255 || $iparray[1] > 255 || $iparray[2] > 255 || $iparray[3] > 255) {
            $return = '- Invalid IP Address';

        } else {
            $string = '';

            /* 天逸IP数据库 */
            $tinyipfile = SYSDAT_PATH. SEP .'tinyipdata.dat';
            if(@file_exists($tinyipfile)) {
                $string = convert_ip_tiny($ip, $tinyipfile);
            }

            /* ip纯真数据库 */
            $fullipfile = SYSDAT_PATH. SEP .'wry.dat';
            if( empty($string) && @file_exists($fullipfile)) {
                $string = convert_ip_full($ip, $fullipfile);
            }
            return empty($string) ? '' :  $string;
        }
    }
    //$return = iconv('GBK', 'UTF-8', $return);
    return $return;
}


/**
 * 天逸IP数据库
 *
 * @param srting $ip          IP地址
 * @param srting $ipdatafile  IP数据库文件
 *
 * @return 地址
 */
function convert_ip_tiny($ip, $ipdatafile) {
    static $fp = NULL, $offset = array(), $index = NULL;
    $ipdot = explode('.', $ip);
    $ip    = pack('N', ip2long($ip));

    $ipdot[0] = (int)$ipdot[0];
    $ipdot[1] = (int)$ipdot[1];

    if($fp === NULL && $fp = @fopen($ipdatafile, 'rb')) {
        $offset = unpack('Nlen', fread($fp, 4));
        $index  = fread($fp, $offset['len'] - 4);
    } elseif($fp == FALSE) {
        return  '- Invalid IP data file';
    }

    $length = $offset['len'] - 1028;
    $start  = unpack('Vlen', $index[$ipdot[0] * 4] . $index[$ipdot[0] * 4 + 1] . $index[$ipdot[0] * 4 + 2] . $index[$ipdot[0] * 4 + 3]);
    for ($start = $start['len'] * 8 + 1024; $start < $length; $start += 8) {
        if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $ip) {
            $index_offset = unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
            $index_length = unpack('Clen', $index{$start + 7});
            break;
        }
    }

    fseek($fp, $offset['len'] + $index_offset['len'] - 1024);
    if($index_length['len']) {
        return '- '.fread($fp, $index_length['len']);
    } else {
        return '- Unknown';
    }
}


/**
 * ip纯真数据库
 *
 * @param srting $ip          IP地址
 * @param srting $ipdatafile  IP数据库文件
 *
 * @return 地址
 */
function convert_ip_full($ip, $ipdatafile) {

    /* 数据库文件没有读权限 */
    if(!$fd = @fopen($ipdatafile, 'rb')) return '- Invalid IP data file';

    $ip = explode('.', $ip);
    $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

    if(!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4)) ) return;
    @$ipbegin = implode('', unpack('L', $DataBegin));
    if($ipbegin < 0) $ipbegin += pow(2, 32);
    @$ipend = implode('', unpack('L', $DataEnd));
    if($ipend < 0) $ipend += pow(2, 32);
    $ipAllNum = ($ipend - $ipbegin) / 7 + 1;


    $BeginNum = $ip2num = $ip1num = 0;
    $ipAddr1 = $ipAddr2 = '';
    $EndNum = $ipAllNum;

    while($ip1num > $ipNum || $ip2num < $ipNum) {
        $Middle= intval(($EndNum + $BeginNum) / 2);

        fseek($fd, $ipbegin + 7 * $Middle);
        $ipData1 = fread($fd, 4);
        if(strlen($ipData1) < 4) {
            fclose($fd);
            return '- System Error';
        }
        $ip1num = implode('', unpack('L', $ipData1));
        if($ip1num < 0) $ip1num += pow(2, 32);

        if($ip1num > $ipNum) {
            $EndNum = $Middle;
            continue;
        }

        $DataSeek = fread($fd, 3);
        if(strlen($DataSeek) < 3) {
            fclose($fd);
            return '- System Error';
        }
        $DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
        fseek($fd, $DataSeek);
        $ipData2 = fread($fd, 4);
        if(strlen($ipData2) < 4) {
            fclose($fd);
            return '- System Error';
        }
        $ip2num = implode('', unpack('L', $ipData2));
        if($ip2num < 0) $ip2num += pow(2, 32);

        if($ip2num < $ipNum) {
            if($Middle == $BeginNum) {
                fclose($fd);
                return '- Unknown';
            }
            $BeginNum = $Middle;
        }
    }

    $ipFlag = fread($fd, 1);
    if($ipFlag == chr(1)) {
        $ipSeek = fread($fd, 3);
        if(strlen($ipSeek) < 3) {
            fclose($fd);
            return '- System Error';
        }
        $ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
        fseek($fd, $ipSeek);
        $ipFlag = fread($fd, 1);
    }

    if($ipFlag == chr(2)) {
        $AddrSeek = fread($fd, 3);
        if(strlen($AddrSeek) < 3) {
            fclose($fd);
            return '- System Error';
        }
        $ipFlag = fread($fd, 1);
        if($ipFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if(strlen($AddrSeek2) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }

        while(($char = fread($fd, 1)) != chr(0))
            $ipAddr2 .= $char;

        $AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
        fseek($fd, $AddrSeek);

        while(($char = fread($fd, 1)) != chr(0))
            $ipAddr1 .= $char;
    } else {
        fseek($fd, -1, SEEK_CUR);
        while(($char = fread($fd, 1)) != chr(0))
            $ipAddr1 .= $char;

        $ipFlag = fread($fd, 1);
        if($ipFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if(strlen($AddrSeek2) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }
        while(($char = fread($fd, 1)) != chr(0))
            $ipAddr2 .= $char;
    }
    fclose($fd);

    if(preg_match('/http/i', $ipAddr2)) {
        $ipAddr2 = '';
    }
    $ipaddr = "$ipAddr1 $ipAddr2";
    $ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
    $ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
    $ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
    if(preg_match('/http/i', $ipaddr) || $ipaddr == '') {
        $ipaddr = '- Unknown';
    }
    return '- '.$ipaddr;
}




/**
 * 获取IP地理位置 (淘宝IP接口)
 *
 * @Return: array
 */
function getCity($ip) {
    $url="http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
    $ip=json_decode(file_get_contents($url));
    if((string)$ip->code=='1') return false;
    $data = (array) $ip->data;
    return $data;
}



/**
 * 获得当前时间戳
 */
function getMicrotime()
{
	$list = explode(' ', microtime());
	$time = $list[1] . "." . substr($list[0], 2);
	return $time;
}

/**
 × get请求，参数通过get形式提交
 */
function httpGet($url)
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 500);
	// 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
	// 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
	//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
	//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
	curl_setopt($curl, CURLOPT_URL, $url);
	
	$res = curl_exec($curl);
	curl_close($curl);
	
	return $res;
}

/**
 × post请求，参数通过post表单形式提交
 */
function httpPost($url,$data)
{
	// 模拟提交数据函数
	$curl = curl_init(); // 启动一个CURL会话
	curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
	//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
	
	//php5的safe_model是关闭的
	if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')){
	    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
	}

	curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
	curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
	curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
	curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
	$tmpInfo = curl_exec($curl); // 执行操作
	if (curl_errno($curl))
	{
		echo 'Errno'.curl_error($curl);//捕抓异常
	}
	curl_close($curl); // 关闭CURL会话
	return $tmpInfo; // 返回数据
}

/**
 * 验证手机号是否正确
 * @参数 string $mobile
 * @返回 bool true|false
 */
function isPhone($phone)
{
	if (!is_numeric($phone))
	{
		return false;
	}
	return preg_match('/^1[\d]{10}$/', $phone) ? true : false;
}

/**
 * 验证电话号码是否正确
 * @参数 string $mobile
 * @返回 bool true|false
 */
function isHomePhone($phone)
{
	return (preg_match("/^(((d{3}))|(d{3}-))?((0d{2,3})|0d{2,3}-)?[1-9]d{6,8}$/",$phone))?true:false;
}

function passwordMd5($str)
{
	try
	{
		$key1=config('password_key_first');
		$key2=config('password_key_last');
	}
	catch (\Exception $e)
	{
		$key1='12121sdfsdfd';
		$key2='uyshrdfs';
	}
	$str=$key1.$str.$key2;
	return md5($str);
}

function phoneNoDisplay($phone)
{
	$str = substr_replace($phone,'****',3,4);
	return $str;
}

function getClientIP()
{
	global $ip;
	if (getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
		else if(getenv("HTTP_X_FORWARDED_FOR"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
			else if(getenv("REMOTE_ADDR"))
				$ip = getenv("REMOTE_ADDR");
				else $ip = "Unknow";
				return $ip;
}

function logUrl(){
    $clientUrl = getClientUrl();
    logInfo($clientUrl);
}

function logInfo($object,$isClear=false){
	
	$filePath = __DIR__ . "/../upload/log.txt";
	if($isClear){
		//清空历史数据
		file_put_contents($filePath, "");
	}
	
	$log_info = $object;
	if (gettype($log_info)=="array"){
		$log_info=implode(',',$log_info);
	}
	$time_str = date('Y-m-d H:i:s',time());;
	file_put_contents($filePath, "log($time_str):$log_info\n", FILE_APPEND);
}