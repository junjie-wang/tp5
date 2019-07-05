<?php
namespace app\index\controller;


// use app\common\controller\WeixinTool;
use  app\wxmsg\controller\Wxtool;
use think\Controller;
use app\common\controller\FM;
use think\Cache;
use DM\DM;
use app\donet\controller\DBM;

class Index 
{
   public function index(){
	   echo  "你好陌生人";
   }
}
