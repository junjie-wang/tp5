<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/4 0004
 * Time: 下午 4:30
 * 企业付款测试
 */
namespace app\test\controller;
use app\common\controller\HttpUtil;
use app\wxmsg\controller\Wxtool;
use app\common\controller\EnterpriseTools;
use think\Controller;
class Demo extends Controller  {
    //企业付款测试接口:http://t-yigo.hsmsoft.cn/test/demo/pay
    //企业付款测试
   public   function  pay(){

    //   请求接口链接
       $pay_url="https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
    //   $appid="wx807b288e295cbec2";//公众号appid-测试
       $appid="wx945ca1ac1305664e";//翼Go
       //$mch_id="1499892152";//商户号-测试
       $mch_id="1499375792";//商户号
       $nonce_str=Demo::createNonceStr(30);//随机字符串
       $order_number=Demo::getOrderNo();//订单编号
       $openid="o5-jy1VxvMuVuietvvI2YUshYwQ8";//贵州博荣自己的openid
      $amount=100;//转账的金额，单位为分
      $desc="企业付款测试";
       $ip=get_ip();
       $data['mch_appid']=$appid;
       $data['mchid']=$mch_id;
       $data['nonce_str']=$nonce_str;
       $data['partner_trade_no']=$order_number;
       $data['openid']=$openid;
       $data['check_name']="NO_CHECK";
       $data['amount']=$amount;
       $data['desc']=$desc;
       $data['spbill_create_ip']=$ip;
       $sign= Demo::getSign($data);
       //组装请求参数
       $xml="<xml>";
       $xml=$xml."<mch_appid>".$appid."</mch_appid>";
       $xml=$xml."<mchid>".$mch_id."</mchid>";
       $xml=$xml."<nonce_str>".$nonce_str."</nonce_str>";
       $xml=$xml."<partner_trade_no>".$order_number."</partner_trade_no>";
       $xml=$xml."<openid>".$openid."</openid>";
       $xml=$xml."<check_name>NO_CHECK</check_name>";
   /*    $xml=$xml."<re_user_name>张三</re_user_name>";*/
       $xml=$xml."<amount>".$amount."</amount>";
       $xml=$xml."<desc>".$desc."</desc>";
       $xml=$xml."<spbill_create_ip>".$ip."</spbill_create_ip>";
       $xml=$xml."<sign>".$sign."</sign>";
       $xml=$xml."</xml>";
     //  show($xml);
      $res=Demo::postXmlSSLCurl($pay_url,$xml);
      $res=Demo::xmlToArray($res);
     show($res);
     //  $res=EnterpriseTools::pay($openid,$amount,$desc,'');


   }
//    public function  test(){
//        echo "测试开启中.....";
//        $openid="oziEL05PJ2dZGuc0ib83_CcDnlzQ";//贵州博荣自己的openid
//        $amount=100;//转账的金额，单位为分
//        $desc="企业付款测试";
//        $res=Demo::pay($openid,$amount,$desc,'');
//        show($res);
//
//    }

    public function  dd(){
        $openid="o5-jy1VxvMuVuietvvI2YUshYwQ8";
        $amount=100;
        $desc="企业付款测试ddd";
        $res=Wxtool::pay($openid,$amount,$desc,'');
        show($res);

    }

    /**
     * 生成订单编号
     * @return string
     */
    protected  static  function getOrderNo() {
        $num1 = mt_rand(10000000, 99999999);
        $ordernumber = '21' . date('Ymd', time()) . $num1;
        return $ordernumber;
    }

    /**
     * 生成随机字符串
     * @param int $length
     * @return string
     */
    protected static function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    /**
     * 	作用：使用证书，以post方式提交xml到对应的接口url
     */
    static  function postXmlSSLCurl($url,$xml,$second=30)
    {
        $packet=config("yigo_packet");
       // show($packet);
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,1);//证书检查
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'pem');
        curl_setopt($ch,CURLOPT_SSLCERT,$packet['CURLOPT_SSLCERT']);
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'pem');
        curl_setopt($ch,CURLOPT_SSLKEY,$packet['CURLOPT_SSLKEY']);
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'pem');
        curl_setopt($ch,CURLOPT_CAINFO,$packet['CURLOPT_CAINFO']);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
        $data=curl_exec($ch);
        if($data){ //返回来的是xml格式需要转换成数组再提取值，用来做更新
            curl_close($ch);
            return $data;
        }else{
            $error=curl_errno($ch);
            echo "curl出错，错误代码：$error"."<br/>";
            // echo "<a href='http://curl.haxx.se/libcurl/c/libcurs.html'>;错误原因查询</a><br/>";
            curl_close($ch);
            return false;
        }
    }

   static function getSign($Obj)
    {
          // var_dump($Obj);//die;
            foreach ($Obj as $k => $v)
            {
                $Parameters[$k] = $v;
            }
        //签名步骤一：按字典序排序参数
            ksort($Parameters);
            $String = Demo::formatBizQueryParaMap($Parameters, false);//方法如下
        //echo '【string1】'.$String.'</br>';
          //签名步骤二：在string后加入KEY
            $String = $String."&key=r4t5632fd1daf25s55l5vnmnv4m5554t";
        //echo "【string2】".$String."</br>";
          //签名步骤三：MD5加密
            $String = md5($String);
          //echo "【string3】 ".$String."</br>";
         //签名步骤四：所有字符转为大写
            $result_ = strtoupper($String);
           //echo "【result】 ".$result_."</br>";
            return $result_;
    }
    /**
     * 作用：格式化参数，签名过程需要使用
     */
   static function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }

            $buff .= $k . "=" . $v . "&";
        }
        $reqPar=null;
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        //var_dump($reqPar);//die;
        return $reqPar;
    }

    //将XML转为array
    static function  xmlToArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }
}