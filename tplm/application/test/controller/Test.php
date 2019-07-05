<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/28 0028
 * Time: 下午 1:58
 */
namespace app\test\controller;
use app\common\controller\PacketUtil;
use test\Test1;
use  app\wxmsg\controller\Wxtool;
use think\Controller;
use think\Db;

class  Test  extends  Controller{
     public function test(){
         $m=[['a'=>'name','b'=>'zhagnsan'],['a'=>'age','b'=>20],['a'=>'address','b'=>'guiyang']];

      //   var_dump($m);

         $n=[];
         //code 补充代码
        foreach($m as $key=>$value){
           // show($value[$key]);
            $n[$value['a']]=$value['b'];

        }
         show($n);
       //  var_dump($n);

         die;
     }
    public function  t(){
        $test=new Test1();
        show($test);
    }

    public function sendPack(){

        $openid = "o5-jy1fwuvmaC0k-N9dKuIjed_go";
        $amount = 0;
        $act_name = "翼go信用卡活动";
        $wishing = "搜索“翼go”关注公众号,领取更多红包!";
        $phone="13655186043";
        $remark="信用卡红包发放";
        $res = PacketUtil::send($openid,2,$amount);
        $status='FAIL';
        $nickname="百视通";
        if($res['status']==1){
            $status='SUCCESS';
        }
        $obj=null;
        $obj->openid=$openid;
        $obj->money=$amount;
        $obj->note=date("Y-m-d",time());
        $obj->create_time=time();
        $obj->phone=$phone;
        $obj->remark=$remark;
        $obj->status=$status;
        $obj->nickname=$nickname;
        $obj->json=json_encode($res,JSON_UNESCAPED_UNICODE);
        Db::name("admin_send_packet")->saveObj($obj);
        show($res);
    }
}