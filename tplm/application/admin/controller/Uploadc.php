<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/19 0019
 * Time: 下午 1:59
 */
namespace app\admin\controller;
//定义第三方插件引引入目录
set_time_limit(0); //设置页面等待时间
use app\common\controller\ExcelUtil;
use app\common\controller\PacketUtil;
use app\wxmsg\controller\Wxtool;
use think\Controller;
use think\Db;
use think\Exception;

require_once EXTEND_PATH.'excel/PHPExcel/IOFactory.php';
require_once EXTEND_PATH.'excel/PHPExcel.php';
//require_once VENDOR_PATH."phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php";
//require_once VENDOR_PATH."phpoffice/phpexcel/Classes/PHPExcel.php";

class Uploadc extends  Controller{
   /**
    * 这是一个处理上传文件的方法
    *
    */
    public function  upload(){
       try{
           $file_name=$_FILES['file']['name'];//客户端文件的原名称
           //step1:保存上传的excel文件
           $boolean=ExcelUtil::uploadExcel($_FILES['file']);
           if($boolean===true){
               //上传文件成功
               //将上传文件的内容读取出来
               $date=date("Ymd",time());
               $path="circle".DS.$date;
               $filepathname=ROOT_PATH."upload".DS.$path.DS.$file_name;
               //$rs=ExcelUtil::readCSV($filepathname);
               $rs=ExcelUtil::ReadexcelToArray($filepathname);
               $rs=json_decode($rs,true);
               $rs=$rs['data'];

               //var_dump($rs);exit;
               if(!empty($rs)){
                   //这个for循环为了去除上传csv文件，姓名中的逗号，保证脱敏后的姓名字段，能够保存到数据库
                   foreach($rs as $key=>$value){
                       $name=$value[4];
                       if(strpos($name,",")){
                           $name=str_replace(",","",$name);
                       }
                       $value[4]=$name;
                       $rs[$key]=$value;
                   }
                   //这个for循环是循环匹配核卡数据
                   foreach($rs as $key=>$value){
                       if(count($value)>=5&&$key>1){
                           //取出渠道来源
                           $ways=$value[0];
                           //取出核卡日期
                           $hekaDate=$value[1];
                           //取出进件日期
                           $jinjianDate=$value[2];
                           //取出手机号码
                           $phone=$value[3];
                           //取出用户的真实姓名
                           $realName=$value[4];
                           if(!empty($realName)){
                                 $realName=str_replace(","," ",$realName);
                           }

                               $rs=$this->matching($ways,$hekaDate,$jinjianDate,$phone,$realName);

                               show($rs);
                       }
                   }
               }
               $html="<br/>";
               $html=$html."<button style='width:60px;height:30px;background: deepskyblue;' onclick='back()'>返回</button>";
               $html=$html."<script type='text/javascript'>";
               $html=$html."function back(){ history.go(-1);}";
               $html=$html."</script>";
               return  $html;
           }else{
               var_dump($boolean);
           }
       }catch(Exceptio $e){
           return "<script>alert('上传失败');history.go(-1);</script>";
       }

    }
    /**
     * 这是一个提供下载模板csv文件的方法
     */
    public  function  write(){
      try{
          $filename = "circle.csv";
          $header = array('渠道来源','核卡日期','进件日期','手机号码',"真实姓名");
          $index = array('ways','matchDate','jinjianDate','phone','realName');
          $list=array(
              0=> array(
                  "ways"=>"123456",
                  "matchDate"=>date("Y/m/d",time()),
                  "jinjianDate"=>date('Y/m/d',time()),
                  "phone"=>"177****0268",
                  "realName"=>"*举"
              ),
              1=> array(
                  "ways"=>"123456",
                  "matchDate"=>date("Y/m/d",time()),
                  "jinjianDate"=>date('Y/m/d',time()),
                  "phone"=>"181****8044",
                  "realName"=>"*妍"
              ),
              2=> array(
                  "ways"=>"123456",
                  "matchDate"=>date("Y/m/d",time()),
                  "jinjianDate"=>date('Y/m/d',time()),
                  "phone"=>"151****7900",
                  "realName"=>"*详"
              ),
          );
         ExcelUtil::createCSV($list,$filename,$header,$index);//导出csv文件
      }catch(Exception $e){
          return "<script>alert('下载模板文件失败');history.go(-1);</script>";
      }
      //ExcelUtil::createTable($list,"1.xls",$header,$index);//导出excel文件
    }


    protected function matching($ways,$hekaDate,$jinjianDate,$phone,$realName){
        if(empty($ways)&&empty($hekaDate)&&empty($jinjianDate)&&empty($phone)&&empty($realName)){
                     return false;
        }
        //step1:先将excel文件中的数据插入一份到数据库
         $insert=array(
             "wayscode"=>$ways,
             "match_card_date"=>$hekaDate,
             "jinjian_date"=>$jinjianDate,
             "realName"=>$realName,
             "phone"=>$phone,
             "creatime"=>time(),
             "note"=>date("Ymd",time())
         );
       // var_dump($insert);exit;
       $submit_id= Db::name("admin_input_submit")->insertGetId($insert);
        //step2:去圈子营销用户表查询该用户是否办理过用户卡
        $rs=$this->IsshouliCard($phone,$realName,$hekaDate,$ways,$jinjianDate);
        //step4:如果查询出来的数据不为空，则说明该用户申请过信用卡
        if(count($rs)==1){//精确判断核卡用户只有一个
            //判断核卡状态
            $status=$rs[0]['apply_credit_result'];
            //取出圈子营销用户表的主键
            $yigo_circle_user_id=$rs[0]['user_id'];
            $yigo_circle_parent_user_id=$rs[0]['parent_user_id'];
            $circle_user_update['user_id']=$yigo_circle_user_id;
            if($status!=1){
                //如果该用户没有核卡，则更新用户的核卡状态
                $circle_user_update['apply_credit_result']=1;
                $circle_user_update['match_card_time']=time();
                Db::name("circle_user")->update($circle_user_update);//更新圈子营销用户表的数据

                //更新用户提交表日志记录的状态
                Db::name("admin_input_submit")
                    ->where("id",$submit_id)
                    ->update(["status"=>1]);

                //修改上级发展的总人数和总收益
                $res = Db::name("circle_user")
                    ->field("count(1) as children_count,sum(parent_packet_amount) as children_packet_amount")
                    ->where("parent_user_id",$yigo_circle_parent_user_id)
                    ->where("apply_credit_phone is not null")//“进行过网申”
                    ->where("apply_credit_result",1)//核卡成功
                    ->find();
                $children_count = $res['children_count'];
                $children_packet_amount = $res['children_packet_amount'];
                Db::name("circle_user")->where("user_id",$yigo_circle_parent_user_id)
                    ->update([
                        "children_count"=>$children_count,
                        "children_packet_amount"=>$children_packet_amount
                    ]);

            }else{
                return apiData(-1,"您已经鉴定过");
            }
        }else if(count($rs)>1){
             //模糊匹配到多条用户数据，则不给用户直接通过核卡
            return apiData(-1,"系统模糊匹配到多条记录，为了资金安全，不给于通过，请联系运营人员!");
        }
        else{
            return apiData(-1,"系统没有查询到您的相关记录");
        }

        return true;
    }

    /**
     * 这是一个判断用户是否办理信用卡的方法
     * @param $phone 核卡的手机号码
     * @param $name  核卡用户的脱敏后的真实姓名
     * @param $match_date 交通银行核卡通过的日期 形如 2017/4/27
     * @param $ways  交通银行提供的渠道编号
     * @param $enter_date 交通银行提供的进件日期
     * @return $rs 返回匹配到的用户信息
     */
    protected   function  IsshouliCard($phone,$name,$match_date,$ways,$enter_date){
       if(empty($phone)){
           return "手机号码为空";
       }
        if(!empty($match_date)){
            $match_date=str_replace("\\","-",$match_date);
            $match_date=$match_date." 59:59:59";
        }

        $middle=substr($phone,3,4);
        if($middle=="****"){
                //如果精确不匹配，则进行模糊匹配
                //截取用户的手机号码
                $end=substr($phone,7,4);
                $first=substr($phone,0,3);
                $str=$first."%".$end;
                $where="apply_credit_phone like '$str' ";
              /* if(!empty($name)){
                   $where=$where." or realName like '%realName' ";
               }*/
               if(!empty($match_date)){
                  // $where=$where." and FROM_UNIXTIME(create_time, '%Y/%m/%d')<='$match_date' ";
                   $where=$where." and create_time<='$match_date' ";
               }
            $sql="select * from yigo_circle_user where  ".$where;
            show($sql);
            $rs=Db::query($sql);
             // show($rs);
        }else{
            //进行模糊匹配手机号码
            $rs=Db::name("circle_user")
                ->where("apply_credit_phone",'like',$phone)
                ->select();
        }

        //如果模糊匹配的数据有多条，说明数据匹配到多条，为了资金安全，将这些手机号码单独记录下来，
        //不给用户直接，通过核卡成功,需要运营手动核实
        if(count($rs)>1){
            foreach($rs as $key=>$value){
                   //组装单独插入的数据
                 $insert=array(
                     "user_id"=>$value['user_id'],
                     "numcode"=>$ways,
                     "match_date"=>$match_date,
                     "enter_date"=>$enter_date,
                     "real_phone"=>$value['apply_credit_phone'],
                     "phone"=>$phone,
                     "real_name"=>$name,
                     "create_time"=>time()
                 );
                $id=Db::name("admin_circle_likemore")->insertGetId($insert);

            }
        }
        return $rs;
    }
public function  dd(){
   // $path="G:/circle.csv";
//    $date=date("Ymd",time());
//    $path="circle".DS.$date;
  // $filepathname=ROOT_PATH."upload".DS.$path.DS."circle.csv";
   // $filepathname="F:/4444/upload/circle/20180514/123.xls";
  $filepathname="G:/test.xls";

   $rs=ExcelUtil::ReadexcelToArray($filepathname);
   //$rs=ExcelUtil::readCSV($filepathname);
 //show($rs);exit;
    if(!is_array($rs)){
        $rs=json_decode($rs,true);
    }
 show($rs);exit;
    $rs=$rs['data'];
    if(!empty($rs)){
        foreach($rs as $key=>$value){

            $name=$value[4];
           if(strpos($name,",")){
             $name=str_replace(",","",$name);
            }
            $value[4]=$name;
            $rs[$key]=$value;
       }
    }
   // $rs=readfile($filepathname);
    var_dump($rs);
}
  public function  sendTest(){
      $openid="o5-jy1VxvMuVuietvvI2YUshYwQ8";
      $orderNo=PacketUtil::getOrderNo();
      $amount=1;
      $act_name="圈子营销_测试";
      $wishing="更多惊醒，请关注翼Go公众号";
      $remark="我们在这里等着你哦";
     // $rs=Wxtool::sendPacket($openid,$orderNo,$amount,$act_name,$wishing,$remark);
      $rs=PacketUtil::send($openid,1,$amount);
      var_dump($rs);
  }
    public  function  tt(){
        $sql="select * from yigo_circle_user";
        $ds=Db::query($sql);
        var_dump($ds);
    }
}
?>